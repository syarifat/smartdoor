<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Force HTTPS saat di production (di balik reverse proxy seperti Nginx/hosting panel)
        // Ini memperbaiki Mixed Content error karena form action menggunakan http://
       // if (config('app.env') === 'production' || request()->server('HTTPS') === 'on'
            //|| request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            //URL::forceScheme('https');
        //}

        // Daftarkan Custom Mailer Resend
        \Illuminate\Support\Facades\Mail::extend('resend', function (array $config) {
            return new \App\Mail\Transport\ResendTransport(
                $config['api_key'] ?? env('RESEND_API_KEY')
            );
        });
    }
}
