<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetShopLocale
{
    /** Польська — основна мова; порядок: pl, uk, en */
    protected array $locales = ['pl', 'uk', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', 'pl');
        if (in_array($locale, $this->locales, true)) {
            App::setLocale($locale);
        }
        return $next($request);
    }
}
