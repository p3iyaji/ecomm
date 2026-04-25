<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Jobs\NotifyLowStock;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'compare_price',
        'cost_per_item',
        'sku',
        'barcode',
        'quantity',
        'security_stock',
        'track_quantity',
        'allow_backorder',
        'weight',
        'height',
        'width',
        'length',
        'is_active',
        'is_featured',
        'images',
        'attributes',
        'seo_metadata',
        'category_id',
    ];

    protected $appends = [
        'discount_percentage',
        'is_in_stock',
    ];

    protected $casts = [
        'images' => 'array',
        'attributes' => 'array',
        'seo_metadata' => 'array',
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_per_item' => 'decimal:2',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'length' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_quantity' => 'boolean',
        'allow_backorder' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFinalPriceAttribute()
    {
        return $this->compare_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
        }

        return null;
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->isInstock();
    }

    public function getAverageRatingAttribute(): float
    {
        if (array_key_exists('average_rating', $this->attributes) && $this->attributes['average_rating'] !== null) {
            return round((float) $this->attributes['average_rating'], 1);
        }

        return round((float) ($this->reviews()->where('is_approved', true)->avg('rating') ?? 0), 1);
    }

    public function isInstock()
    {
        if (!$this->track_quantity) {
            return true;
        }
        return $this->quantity > $this->security_stock;
    }

    public function hasStock(int $quantity)
    {
        if (!$this->track_quantity) {
            return true;
        }
        return $this->quantity >= $quantity;
    }

    public function decrementStock(int $quantity)
    {
        if ($this->track_quantity) {
            $this->decrement('quantity', $quantity);

            if ($this->quantity <= $this->security_stock) {
                // Dispatch low stock notification job
                NotifyLowStock::dispatch($this);
            }
        }
    }

    public function incrementStock(int $quantity)
    {
        if ($this->track_quantity) {
            $this->increment('quantity', $quantity);
        }
    }
}
