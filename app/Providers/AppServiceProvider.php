<?php

namespace App\Providers;

use App\Services\YandexReviewsService;
use App\Services\YandexUrlParser;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(YandexUrlParser::class, fn () => new YandexUrlParser);
        $this->app->singleton(YandexReviewsService::class, function ($app) {
            return new YandexReviewsService($app->make(YandexUrlParser::class));
        });
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
