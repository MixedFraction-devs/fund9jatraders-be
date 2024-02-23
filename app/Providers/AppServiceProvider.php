<?php

namespace App\Providers;

use App\Services\Webhook\Drivers\CryptomusWebhookDriver;
use App\Services\Webhook\Drivers\PaystackWebhookDriver;
use App\Services\Webhook\Webhook;
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
        /**
         * Register webhook drivers
         */
        Webhook::driver('paystack', PaystackWebhookDriver::class);
        Webhook::driver('cryptomus', CryptomusWebhookDriver::class);

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
