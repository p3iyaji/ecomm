<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        $product = Product::query()->findOrFail($request->product_id);

        Review::query()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
            ],
            [
                'rating' => $request->rating,
                'title' => $request->title,
                'content' => $request->content,
                'is_approved' => true,
            ]
        );

        Cache::forget("product:{$product->slug}");
        Cache::forget("product:{$product->slug}:related");

        return redirect()->back()->with('success', 'Thank you for your review.');
    }
}
