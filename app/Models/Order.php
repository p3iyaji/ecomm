<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Jobs\SendOrderConfirmation;
use App\Jobs\SendShippingNotification;
use App\Jobs\SendReviewRequest;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_id',
        'status',
        'subtotal',
        'tax',
        'shipping_cost',
        'discount',
        'total',
        'payment_status',
        'payment_method',
        'payment_id',
        'shipping_address',
        'billing_address',
        'metadata',
        'notes',
        'paid_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'metadata' => 'array',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function markAsPaid($paymentId = null)
    {
        $this->update([
            'payment_status' => self::PAYMENT_PAID,
            'payment_id' => $paymentId,
            'paid_at' => now(),
            'status' => self::STATUS_PROCESSING,
        ]);

        //dispatch order confirmation job
        SendOrderConfirmation::dispatch($this);
    }

    public function markAsShipped()
    {
        $this->update([
            'status' => self::STATUS_SHIPPED,
            'shipped_at' => now(),
        ]);

        //dispatch shipping notification job
        SendShippingNotification::dispatch($this);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);

        //update customer stats
        if ($this->customer) {
            $this->customer->update([
                'total_orders' => $this->customer->total_orders + 1,
                'total_spent' => $this->customer->total_spent + $this->total,
                'last_purchase_at' => now(),
            ]);
        }

        SendReviewRequest::dispatch($this);
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'metadata' => array_merge($this->metadata ?? [], ['cancellation_reason' => $reason]),
        ]);

        // Restore stock
        foreach ($this->items as $item) {
            $product = Product::find($item->product_id);
            if ($product && $product->track_quantity) {
                $product->increment('quantity', $item->quantity);
            }
        }
    }
}
