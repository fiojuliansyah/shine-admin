<?php

namespace App\Providers;

use App\Broadcasting\WhatsappChannel;
use App\Models\Applicant;
use App\Models\Company;
use App\Models\Generate;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        
        Schema::defaultStringLength(191);

        Notification::extend('whatsapp', function ($app) {
            return new WhatsappChannel();
        });

        if (!$this->app->runningInConsole()) {
            
            config(['app.locale' => 'id']);
	        Carbon::setLocale('id');

            // Data layout dihitung hanya saat view yang membutuhkannya dirender
            // (dulu: ~6 query di SETIAP request, termasuk file/API).

            // Sidebar admin: satu query untuk semua status + jumlah pelamar menunggu per status.
            view()->composer('admin.layouts.partials.sidebar', function ($view) {
                $view->with('sidebarStatuses', once(fn () => Status::withCount([
                    'applicants as pending_count' => fn ($q) => $q->whereNull('done'),
                ])->get()));
                $view->with('pendingApplicantsCount', once(fn () => Applicant::whereNull('approve_id')
                    ->where('status_id', '=', 0)
                    ->whereNull('done')
                    ->count()));
            });

            // View lain yang memakai daftar status tanpa dikirim controller.
            view()->composer([
                'admin.dashboards.recruit',
                'admin.statuses.partials.resume',
                'admin.applicants.partials.resume',
            ], function ($view) {
                if (! array_key_exists('statuses', $view->getData())) {
                    $view->with('statuses', once(fn () => Status::all()));
                }
            });

            // Badge "terbit" di sidebar pelamar (Auth sudah tersedia saat view dirender).
            view()->composer('website.layouts.partials.sidebar', function ($view) {
                $view->with('eletterBadge', once(fn () => Auth::check()
                    ? Generate::where('user_id', Auth::id())->latest()->first(['id'])
                    : null));
            });
        }
    }
}
