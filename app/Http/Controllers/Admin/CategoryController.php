<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Support\ShopCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Categories/Create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $this->toCategoryData($request->validated());
        Category::query()->create($data);

        Cache::forget('categories:all');
        ShopCache::bumpProductListVersion();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $this->toCategoryData($request->validated());
        $category->update($data);

        Cache::forget('categories:all');
        ShopCache::bumpProductListVersion();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Move or remove products in this category before deleting it.');
        }
        $category->delete();
        Cache::forget('categories:all');
        ShopCache::bumpProductListVersion();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function toCategoryData(array $validated): array
    {
        if (empty($validated['slug'] ?? null)) {
            $validated['slug'] = Str::slug($validated['name']);
        } else {
            $validated['slug'] = (string) $validated['slug'];
        }

        return $validated;
    }
}
