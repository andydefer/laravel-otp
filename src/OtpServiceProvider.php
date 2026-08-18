<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp;

use AndyDefer\LaravelOtp\Contracts\Repositories\OtpRepositoryInterface;
use AndyDefer\LaravelOtp\Contracts\Services\OtpGeneratorInterface;
use AndyDefer\LaravelOtp\Contracts\Services\OtpServiceInterface;
use AndyDefer\LaravelOtp\Repositories\OtpRepository;
use AndyDefer\LaravelOtp\Services\OtpGenerator;
use AndyDefer\LaravelOtp\Services\OtpService;
use Illuminate\Support\ServiceProvider;

final class OtpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ✅ Bind des interfaces vers leurs implémentations concrètes
        $this->app->bind(
            OtpGeneratorInterface::class,
            OtpGenerator::class
        );

        $this->app->bind(
            OtpRepositoryInterface::class,
            OtpRepository::class
        );

        $this->app->bind(
            OtpServiceInterface::class,
            OtpService::class
        );

        // ✅ Singleton pour les services qui doivent être uniques
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
        ], 'otp-migrations');
    }
}
