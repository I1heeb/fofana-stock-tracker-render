<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

class SupabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('supabase', function () {
            return Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
                'Authorization' => 'Bearer ' . config('services.supabase.anon_key'),
                'Content-Type' => 'application/json',
                'Prefer' => 'return=minimal',
            ])->baseUrl(config('services.supabase.url') . '/rest/v1');
        });
    }

    public function boot(): void
    {
        //
    }
}



