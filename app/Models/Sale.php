<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'product_id',
        'manager_id',
        'client_id',
        'quantity',
        'sale_price',
        'profit',
        'is_combined',
        'profit_to_admin',
        'source',
        'telegram_user_id',
        'telegram_username',
        'delivery_method',
        'delivery_paczkomat_code',
        'delivery_pickup_name',
        'delivery_pickup_phone',
        'delivery_pickup_district',
        'delivery_pickup_day',
    ];

    protected $casts = [
        'is_combined' => 'boolean',
        'profit_to_admin' => 'boolean',
        'sale_price' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
