<?php



namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PayrollGenerator;

class PayrollServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('payroll.generator', function ($app) {
            return new PayrollGenerator();
        });
    }

    public function boot()
    {
        
    }
}

