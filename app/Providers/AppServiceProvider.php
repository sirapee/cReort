<?php

namespace App\Providers;

use App\Services\Implementations\CReportService;
use App\Services\Implementations\ReconciliationService;
use App\Services\Implementations\SessionService;
use App\Services\Implementations\SettlementUploadService;
use App\Services\Implementations\UserManagementService;
use App\Services\Interfaces\ICReportService;
use App\Services\Interfaces\IReconciliationService;
use App\Services\Interfaces\ISessionService;
use App\Services\Interfaces\ISettlementUploadService;
use App\Services\Interfaces\IUserManagementService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
                    ISettlementUploadService::class,
            SettlementUploadService::class,

                );
        $this->app->bind(
            ISessionService::class,
            SessionService::class,
        );

        $this->app->bind(
            IUserManagementService::class,
            UserManagementService::class,
        );

        $this->app->bind(
            IReconciliationService::class,
            ReconciliationService::class,
        );

        $this->app->bind(
            ICReportService::class,
            CReportService::class,
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
