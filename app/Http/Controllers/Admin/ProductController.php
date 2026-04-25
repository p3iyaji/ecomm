<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\ShopCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('category');

        if ($request->filled('search')) {
            $s = $request->string('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->get('category_id'));
        }
        if ($request->get('is_active') === '1' || $request->get('is_active') === '0') {
            $query->where('is_active', (bool) (int) $request->get('is_active'));
        }

        $products = $query
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->orderBy('sort_order')->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'is_active']),
        ]);
    }

    public function create()
    {
        $categories = Category::query()->orderBy('sort_order')->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Products/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $this->toProductData($request->validated(), $request);
        $product = Product::query()->create($data);

        ShopCache::bumpProductListVersion();
        ShopCache::forgetProductDetail($product->slug);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->load('category');
        $categories = Category::query()->orderBy('sort_order')->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
            'imageUrlsText' => is_array($product->images) ? implode("\n", $product->images) : '',
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $oldSlug = $product->slug;
        $data = $this->toProductData($request->validated(), $request);
        $product->update($data);

        ShopCache::bumpProductListVersion();
        ShopCache::forgetProductDetail($oldSlug);
        if ($oldSlug !== $product->slug) {
            ShopCache::forgetProductDetail($product->slug);
        }
        Cache::forget('categories:all');

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'This product is referenced on orders and cannot be deleted. Deactivate it instead.');
        }

        $slug = $product->slug;
        $product->delete();

        ShopCache::bumpProductListVersion();
        ShopCache::forgetProductDetail($slug);
        Cache::forget('categories:all');

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function toProductData(array $validated, Request $request): array
    {
        $text = $validated['image_urls'] ?? null;
        unset($validated['image_urls'], $validated['product_images']);
        if (! empty($validated['slug'])) {
            $validated['slug'] = (string) $validated['slug'];
        } else {
            unset($validated['slug']);
        }
        $urls = $this->imageUrlsFromText($text) ?? [];
        foreach ($request->file('product_images', []) as $file) {
            if ($file && $file->isValid()) {
                $path = $file->store('products', 'public');
                $urls[] = Storage::disk('public')->url($path);
            }
        }
        $validated['images'] = $urls === [] ? null : array_values(array_unique($urls));

        return $validated;
    }

    private function imageUrlsFromText(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $urls = array_values(array_filter(array_map('trim', $lines), fn (string $u) => $u !== ''));

        return $urls === [] ? null : $urls;
    }
}
