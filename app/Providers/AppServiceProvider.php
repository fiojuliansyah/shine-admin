<?php

namespace App\Providers;

use App\Models\Applicant;
use App\Models\Company;
use App\Models\Generate;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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

        if (!$this->app->runningInConsole()) {
            
            config(['app.locale' => 'id']);
	        Carbon::setLocale('id');

            $statuses = Status::all();
            view()->share('statuses', $statuses);

            $countPendingAll = Applicant::where('approve_id', null)
                ->whereNull('done')
                ->count();
            view()->share('countPendingAll', $countPendingAll);

            $countPending = Applicant::where('approve_id', null)
                ->where('status_id', '=', 0)
                ->whereNull('done')
                ->count();
            view()->share('countPending', $countPending);

            $user = Auth::user();

            $eletterBadge = Generate::where('user_id', $user->id)
                ->orderBy('created_at', 'DESC')
                ->first();
            view()->share('eletterBadge', $eletterBadge);

            $pendingApplicants = Applicant::where('approve_id', null)
                ->where('status_id', '=', 0)
                ->whereNull('done')
                ->get();
            view()->share('pendingApplicants', $pendingApplicants);

            $general = Company::where('is_default', 1)
                ->first();
            view()->share('general', $general);
        }
    }
}
