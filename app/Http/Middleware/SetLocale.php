<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Get locale from session, URL parameter, or default
        $locale = $request->get('locale') 
            ?? Session::get('locale') 
            ?? $this->detectBrowserLocale($request)
            ?? config('app.locale');

        // Validate locale
        if (in_array($locale, array_keys(config('app.available_locales')))) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            
            // Set direction for RTL languages
            $direction = in_array($locale, config('app.rtl_locales')) ? 'rtl' : 'ltr';
            view()->share('direction', $direction);
            view()->share('currentLocale', $locale);
        }

        return $next($request);
    }

    private function detectBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        if (!$acceptLanguage) {
            return null;
        }

        $availableLocales = array_keys(config('app.available_locales'));
        $browserLocales = explode(',', $acceptLanguage);

        foreach ($browserLocales as $browserLocale) {
            $locale = substr(trim($browserLocale), 0, 2);
            if (in_array($locale, $availableLocales)) {
                return $locale;
            }
        }

        return null;
    }
}