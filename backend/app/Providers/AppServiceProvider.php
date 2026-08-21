<?php

namespace App\Providers;

use App\Http\Controllers\Team\CurrentTeamController as AppCurrentTeam;
use Iamfarhad\Prometheus\Facades\Prometheus;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Http\Controllers\CurrentTeamController as JetstreamCurrentTeam;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            JetstreamCurrentTeam::class,
            AppCurrentTeam::class
        );

        /**
         * Prometheus
         */

        Prometheus::getOrRegisterCounter(
            'uploads_accepted_total',
            'Total Uploads',
            ['pipeline']
        );
        Prometheus::getOrRegisterCounter(
            'uploads_completed_total',
            'Completed Uploads',
            ['pipeline']
        );
        Prometheus::getOrRegisterCounter(
            'uploads_failed_total',
            'Failed Uploads',
            ['pipeline']
        );

        Prometheus::getOrRegisterHistogram(
            'upload_duration_seconds',
            'Upload Duration (s)',
            ['pipeline'],
            [1, 5, 10, 30, 60, 120, 300, 600]
        );

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
