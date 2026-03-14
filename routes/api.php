<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BotOrderController;
use App\Http\Controllers\Api\BotProductController;

/*
|--------------------------------------------------------------------------
| API Routes (для shop_site / Telegram бота)
|--------------------------------------------------------------------------
| Авторизація: Bearer token в заголовку X-API-Key або Authorization.
| В .env задати SHOP_API_TOKEN=ваш_секретний_токен
*/

Route::middleware('api.token')->group(function () {
    // Товари для бота/сайту (тільки з available_in_bot = true)
    Route::get('bot/products', [BotProductController::class, 'index']);

    // Прийом замовлення з сайту/бота
    Route::post('orders', [BotOrderController::class, 'store']);
});
