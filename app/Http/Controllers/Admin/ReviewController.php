<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReviewRequest;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::query()
            ->with(['user', 'product']);

        if ($request->get('status') === 'pending') {
            $query->where('is_approved', false);
        } elseif ($request->get('status') === 'approved') {
            $query->where('is_approved', true);
        }

        if ($request->filled('search')) {
            $s = $request->string('search');
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('content', 'like', "%{$s}%")
                    ->orWhereHas('product', function ($q2) use ($s) {
                        $q2->where('name', 'like', "%{$s}%");
                    })
                    ->orWhereHas('user', function ($q2) use ($s) {
                        $q2->where('name', 'like', "%{$s}%")
                            ->orWhere('email', 'like', "%{$s}%");
                    });
            });
        }

        $reviews = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $review->load('product');
        $review->update($request->validated());
        if ($review->product) {
            $slug = $review->product->slug;
            Cache::forget("product:{$slug}");
            Cache::forget("product:{$slug}:related");
        }

        return redirect()
            ->back()
            ->with('success', 'Review updated.');
    }

    public function destroy(Review $review)
    {
        $review->load('product');
        if ($review->product) {
            $slug = $review->product->slug;
        }
        $review->delete();
        if (isset($slug)) {
            Cache::forget("product:{$slug}");
            Cache::forget("product:{$slug}:related");
        }

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review removed.');
    }
}
