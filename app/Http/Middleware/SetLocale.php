<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?: $request->user()?->language_pref
            ?: config('app.locale');

        if (in_array($locale, ['en', 'hi', 'gu'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
