<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'telegram_username',
        'name',
        'phone',
        'cashback_balance',
        'cashback_percent',
    ];

    protected $casts = [
        'cashback_balance' => 'decimal:2',
        'cashback_percent' => 'integer',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }
        if ($this->telegram_username) {
            return '@' . $this->telegram_username;
        }
        if ($this->telegram_user_id) {
            return 'ID ' . $this->telegram_user_id;
        }
        return 'Клієнт #' . $this->id;
    }

    /** Знайти або створити клієнта за Telegram даними */
    public static function findOrCreateByTelegram(?string $telegramUserId, ?string $telegramUsername): ?self
    {
        if (!$telegramUserId && !$telegramUsername) {
            return null;
        }
        $client = null;
        if ($telegramUserId) {
            $client = self::where('telegram_user_id', $telegramUserId)->first();
        }
        if (!$client && $telegramUsername) {
            $client = self::where('telegram_username', $telegramUsername)->first();
        }
        if ($client) {
            if ($telegramUserId && !$client->telegram_user_id) {
                $client->update(['telegram_user_id' => $telegramUserId]);
            }
            if ($telegramUsername && !$client->telegram_username) {
                $client->update(['telegram_username' => $telegramUsername]);
            }
            return $client->fresh();
        }
        return self::create([
            'telegram_user_id' => $telegramUserId,
            'telegram_username' => $telegramUsername,
        ]);
    }

    /** Нарахувати кешбек з суми замовлення */
    public function accrueCashback(float $orderTotal): void
    {
        $percent = (float) ($this->cashback_percent ?? 5);
        $amount = round($orderTotal * $percent / 100, 2);
        if ($amount > 0) {
            $this->update(['cashback_balance' => (float) $this->cashback_balance + $amount]);
        }
    }
}
