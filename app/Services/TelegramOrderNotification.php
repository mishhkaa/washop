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
            $lines[] = '• ' . self::escapeHtml($name) . " × {$qty} = " . number_format($sum, 2) . ' zł';
        }
        $lines[] = '';
        $lines[] = '💰 <b>Разом: ' . number_format($total, 2) . ' zł</b>';
        $lines[] = '';

        $lines[] = '<b>👤 Клієнт</b>';
        if ($sale->telegram_username) {
            $lines[] = '  @' . self::escapeHtml(ltrim($sale->telegram_username, '@'));
        }
        if ($sale->telegram_user_id) {
            $lines[] = '  ID: <code>' . self::escapeHtml((string) $sale->telegram_user_id) . '</code>';
        }
        if ($sale->client && $sale->client->name) {
            $lines[] = '  ' . self::escapeHtml($sale->client->name);
        }
        if (empty(array_filter([$sale->telegram_username, $sale->telegram_user_id, $sale->client?->name]))) {
            $lines[] = '  —';
        }
        $lines[] = '';

        if (\Illuminate\Support\Facades\Schema::hasColumn('sales', 'delivery_method') && $sale->delivery_method) {
            $lines[] = '<b>📦 Доставка</b>';
            if ($sale->delivery_method === 'paczkomat') {
                $lines[] = '  Paczkomat InPost';
                if (\Illuminate\Support\Facades\Schema::hasColumn('sales', 'delivery_paczkomat_code') && $sale->delivery_paczkomat_code) {
                    $lines[] = '  Код: <code>' . self::escapeHtml($sale->delivery_paczkomat_code) . '</code>';
                    $lines[] = '  <a href="https://inpost.pl/znajdz-paczkomat">Znajdź paczkomat na mapie</a>';
                }
            } else {
                $lines[] = '  Osobisty odbiór';
                if (\Illuminate\Support\Facades\Schema::hasColumn('sales', 'delivery_pickup_name') && $sale->delivery_pickup_name) {
                    $lines[] = '  👤 Imię: ' . self::escapeHtml($sale->delivery_pickup_name);
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('sales', 'delivery_pickup_phone') && $sale->delivery_pickup_phone) {
                    $lines[] = '  📞 Tel: ' . self::escapeHtml($sale->delivery_pickup_phone);
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('sales', 'delivery_pickup_district') && $sale->delivery_pickup_district) {
                    $lines[] = '  📍 Rejon: ' . self::escapeHtml($sale->delivery_pickup_district);
                }
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private static function escapeHtml(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
