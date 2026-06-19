<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp;

use AndyDefer\LaravelOtp\Repositories\OtpRepository;
use AndyDefer\LaravelOtp\Services\OtpGenerator;
use AndyDefer\LaravelOtp\Services\OtpService;
use Illuminate\Support\ServiceProvider;

final class OtpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OtpGenerator::class);
        $this->app->singleton(OtpRepository::class);
        $this->app->singleton(OtpService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'Otp-migrations');
    }
}
