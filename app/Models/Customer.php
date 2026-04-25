<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'shipping_addresses',
        'billing_addresses',
        'total_spent',
        'total_orders',
        'last_purchase_at',
        'preferences',
    ];

    protected function casts(): array
    {
        return [
            'shipping_addresses' => 'array',
            'billing_addresses' => 'array',
            'preferences' => 'array',
            'total_spent' => 'decimal:2',
            'last_purchase_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
