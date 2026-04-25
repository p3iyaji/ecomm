<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\ShopCache;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = ShopCache::productListCacheKey($request->fullUrl());

        $products = Cache::remember($cacheKey, 300, function () use ($request) {
            $query = Product::query()
                ->with('category')
                ->where('is_active', true)
                ->withAvg(['reviews as average_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
                ->withCount(['reviews as reviews_count' => fn ($q) => $q->where('is_approved', true)]);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category')) {
                $category = Category::query()->where('slug', $request->category)->first();
                if ($category) {
                    $query->where('category_id', $category->id);
                }
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            switch ($request->get('sort')) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->withCount('orderItems')->orderBy('order_items_count', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            return $query->paginate(12)->withQueryString();
        });

        $categories = Cache::remember('categories:all', 3600, function () {
            return Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug']);
        });

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'min_price', 'max_price', 'sort']),
        ]);
    }

    public function show(string $slug)
    {
        $product = Cache::remember("product:{$slug}", 300, function () use ($slug) {
            return Product::query()
                ->with(['category', 'reviews' => function ($query) {
                    $query->where('is_approved', true)
                        ->with('user')
                        ->latest();
                }])
                ->withAvg(['reviews as average_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
                ->where('slug', $slug)
                ->where('is_active', true)
                ->firstOrFail();
        });

        $relatedProducts = Cache::remember("product:{$slug}:related", 300, function () use ($product) {
            return Product::query()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->withAvg(['reviews as average_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
                ->withCount(['reviews as reviews_count' => fn ($q) => $q->where('is_approved', true)])
                ->inRandomOrder()
                ->limit(4)
                ->get();
        });

        return Inertia::render('Products/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
