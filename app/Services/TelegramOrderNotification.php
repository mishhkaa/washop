<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramOrderNotification
{
    /**
     * Відправити повідомлення про нове замовлення в чат заявок Telegram.
     * Використовує TELEGRAM_BOT_TOKEN та TELEGRAM_ORDERS_CHAT_ID з .env.
     */
    public static function sendOrderNotification(Sale $sale): bool
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.orders_chat_id');

        if (empty($token) || empty($chatId)) {
            Log::debug('Telegram order notification skipped: TELEGRAM_BOT_TOKEN or TELEGRAM_ORDERS_CHAT_ID not set.');
            return false;
        }

        $text = self::formatOrderMessage($sale);
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        try {
            $response = Http::timeout(10)->post($url, [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if (!$response->successful()) {
                Log::warning('Telegram order notification failed', [
                    'sale_id' => $sale->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('Telegram order notification error: ' . $e->getMessage(), [
                'sale_id' => $sale->id,
            ]);
            return false;
        }
    }

    private static function formatOrderMessage(Sale $sale): string
    {
        $sale->loadMissing(['saleItems.product', 'client']);
        $lines = [
            '🛒 <b>Нове замовлення #' . $sale->id . '</b>',
            '',
        ];

        $total = 0;
        foreach ($sale->saleItems as $item) {
            $name = $item->product?->name ?? 'Товар #' . $item->product_id;
            $qty = $item->quantity;
            $price = (float) $item->sale_price;
            $sum = $price * $qty;
            $total += $sum;
            $lines[] = "• {$name} × {$qty} = " . number_format($sum, 2) . ' грн';
        }
        $lines[] = '';
        $lines[] = '💰 <b>Разом: ' . number_format($total, 2) . ' грн</b>';

        if ($sale->telegram_username) {
            $lines[] = '👤 @' . ltrim($sale->telegram_username, '@');
        }
        if ($sale->telegram_user_id) {
            $lines[] = 'ID: ' . $sale->telegram_user_id;
        }
        if ($sale->client) {
            $lines[] = 'Клієнт: ' . ($sale->client->name ?: '—');
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('sales', 'delivery_method') && $sale->delivery_method) {
            $delivery = $sale->delivery_method === 'paczkomat' ? 'Paczkomat' : 'Osobisty odbiór';
            $lines[] = 'Доставка: ' . $delivery;
        }

        return implode("\n", $lines);
    }
}
