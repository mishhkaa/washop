<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateShopApiToken
{
    /**
     * Перевірка API-токена для запитів з shop_site / Telegram бота.
     * Заголовок: X-API-Key або Authorization: Bearer <token>
     * В .env: SHOP_API_TOKEN=ваш_токен
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-API-Key')
            ?? $request->bearerToken();

        $validToken = config('services.shop_api_token') ?: env('SHOP_API_TOKEN');

        if (empty($validToken) || $token !== $validToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
