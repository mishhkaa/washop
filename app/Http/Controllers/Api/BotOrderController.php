<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\User;
use App\Services\TelegramOrderNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BotOrderController extends Controller
{
    /**
     * Прийом замовлення з shop_site або Telegram бота.
     * POST /api/orders
     * Body: { "items": [ { "product_id": 1, "quantity": 2, "sale_price": 100 } ], "telegram_user_id": "123456" }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sale_price' => 'required|numeric|min:0',
            'telegram_user_id' => 'nullable|string|max:100',
            'telegram_username' => 'nullable|string|max:100',
        ]);

        $telegramUserId = $validated['telegram_user_id'] ?? null;
        $telegramUsername = $validated['telegram_username'] ?? null;

        try {
            $sale = DB::transaction(function () use ($validated, $telegramUserId, $telegramUsername) {
                $admin = User::where('role', 'admin')->first();
                $managerId = $admin ? $admin->id : auth()->id();

                $totalProfit = 0;
                $orderTotal = 0;
                $saleItemsData = [];

                foreach ($validated['items'] as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                    $qty = (int) $item['quantity'];
                    $salePrice = (float) $item['sale_price'];

                    if (!($product->available_in_bot ?? false)) {
                        throw new \InvalidArgumentException("Товар ID {$product->id} не доступний для замовлення з бота.");
                    }
                    if (($product->quantity ?? 0) < $qty) {
                        throw new \InvalidArgumentException("Недостатньо товару «{$product->name}». Доступно: {$product->quantity}.");
                    }

                    $product->decrement('quantity', $qty);
                    $profit = ($salePrice - ($product->purchase_price ?? 0)) * $qty;
                    $totalProfit += $profit;
                    $orderTotal += $salePrice * $qty;
                    $saleItemsData[] = [
                        'product' => $product,
                        'quantity' => $qty,
                        'sale_price' => $salePrice,
                        'profit' => $profit,
                    ];
                }

                $client = Client::findOrCreateByTelegram($telegramUserId, $telegramUsername);

                $sale = Sale::create([
                    'manager_id' => $managerId,
                    'client_id' => $client?->id,
                    'product_id' => null,
                    'quantity' => 0,
                    'sale_price' => 0,
                    'profit' => $totalProfit,
                    'is_combined' => true,
                    'profit_to_admin' => true,
                    'source' => 'bot',
                    'telegram_user_id' => $telegramUserId,
                    'telegram_username' => $telegramUsername,
                ]);

                foreach ($saleItemsData as $data) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $data['product']->id,
                        'quantity' => $data['quantity'],
                        'sale_price' => $data['sale_price'],
                        'profit' => $data['profit'],
                    ]);
                }

                if ($client && $orderTotal > 0) {
                    $client->accrueCashback($orderTotal);
                }

                return $sale->load('saleItems.product');
            });

            try {
                TelegramOrderNotification::sendOrderNotification($sale);
            } catch (\Throwable $e) {
                // не ламати відповідь API при помилці Telegram
            }

            return response()->json([
                'message' => 'Замовлення створено',
                'order_id' => $sale->id,
                'data' => $sale,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Помилка при створенні замовлення: ' . $e->getMessage()], 500);
        }
    }
}
