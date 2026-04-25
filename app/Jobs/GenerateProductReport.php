<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class GenerateProductReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 2;

    public function handle(): void
    {
        $report = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'featured_products' => Product::where('is_featured', true)->count(),
            'out_of_stock' => Product::where('track_quantity', true)
                ->where('quantity', '<=', 0)
                ->count(),
            'low_stock' => Product::where('track_quantity', true)
                ->whereRaw('quantity <= security_stock')
                ->count(),
            'top_selling' => $this->getTopSellingProducts(),
            'recently_added' => Product::latest()->take(10)->get(['id', 'name', 'price', 'created_at']),
            'categories_distribution' => $this->getCategoryDistribution(),
        ];

        // Store report in Redis with 1 hour expiration
        Redis::setex('reports:products', 3600, json_encode($report));

        // Also cache for fallback
        Cache::put('product_report', $report, now()->addHours(1));
    }

    protected function getTopSellingProducts(int $limit = 10)
    {
        return Product::select('products.id', 'products.name', 'products.price')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.id', 'products.name', 'products.price')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    protected function getCategoryDistribution()
    {
        return Product::select('category_id')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get()
            ->map(fn($item) => [
                'category' => $item->category->name ?? 'Uncategorized',
                'count' => $item->count
            ]);
    }
}
