<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $revenue = (float) Order::query()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->sum('total');

        $orderCount = Order::query()->count();
        $productCount = Product::query()->count();
        $pendingReviews = Review::query()->where('is_approved', false)->count();

        $lowStock = Product::query()
            ->where('is_active', true)
            ->where('track_quantity', true)
            ->get()
            ->filter(fn (Product $p) => $p->quantity <= $p->security_stock)
            ->take(8)
            ->values()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'quantity' => $p->quantity,
                'security_stock' => $p->security_stock,
            ]);

        $recentOrders = Order::query()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'total' => $o->total,
                'user' => $o->user ? ['name' => $o->user->name, 'email' => $o->user->email] : null,
                'created_at' => $o->created_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'revenue' => $revenue,
                'orders' => $orderCount,
                'products' => $productCount,
                'pending_reviews' => $pendingReviews,
            ],
            'lowStock' => $lowStock,
            'recentOrders' => $recentOrders,
        ]);
    }
}
