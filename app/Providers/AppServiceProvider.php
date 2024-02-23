<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
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
    );

    /**
     * Register webhook drivers
     */
    Webhook::driver('paystack', PaystackWebhookDriver::class);
    Webhook::driver('zilla', ZillaWebhookDriver::class);

        Http::macro('cryptomus', function ($sign) {
            return Http::baseUrl(
                config('services.cryptomus.url')
            )->withHeaders([
                'merchant' => config('services.cryptomus.merchant'),
                'sign' => $sign
            ]);
        });
    }
}
