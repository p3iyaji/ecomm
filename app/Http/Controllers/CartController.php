<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getCart();
        $this->syncCartCountSession();

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (! $product->hasStock($request->quantity)) {
            return back()->withErrors(['quantity' => 'Insufficient stock']);
        }

        $cart = $this->getCart();
        $items = $cart->items ?? [];
        $found = false;

        foreach ($items as &$item) {
            if ($item['product_id'] == $request->product_id) {
                $newQuantity = $item['quantity'] + $request->quantity;

                if (! $product->hasStock($newQuantity)) {
                    return back()->withErrors(['quantity' => 'Cannot add more items than available stock']);
                }

                $item['quantity'] = $newQuantity;
                $item['total'] = (float) $product->price * $newQuantity;
                $found = true;
                break;
            }
        }

        if (! $found) {
            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => $request->quantity,
                'total' => (float) $product->price * $request->quantity,
                'image' => $product->images[0] ?? null,
            ];
        }

        $cart->items = $items;
        $cart->save();

        $this->syncCartCountSession();

        return redirect()->back()->with('success', 'Product added to cart');
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = $this->getCart();
        $items = $cart->items ?? [];
        $updated = false;

        foreach ($items as $key => $item) {
            if ($item['product_id'] == $request->product_id) {
                if ($request->quantity > 0) {
                    if (! $product->hasStock($request->quantity)) {
                        return back()->withErrors(['quantity' => 'Insufficient stock']);
                    }

                    $items[$key]['quantity'] = $request->quantity;
                    $items[$key]['total'] = (float) $product->price * $request->quantity;
                } else {
                    unset($items[$key]);
                }
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $cart->items = array_values($items);
            $cart->save();
            $this->syncCartCountSession();
        }

        return redirect()->back()->with('success', 'Cart updated');
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $cart = $this->getCart();
        $items = collect($cart->items ?? [])
            ->reject(fn ($item) => $item['product_id'] == $request->product_id)
            ->values()
            ->toArray();

        $cart->items = $items;
        $cart->save();

        $this->syncCartCountSession();

        return redirect()->back()->with('success', 'Product removed from cart');
    }

    public function clear()
    {
        $cart = $this->getCart();
        $cart->items = [];
        $cart->save();

        $this->syncCartCountSession();

        return redirect()->back()->with('success', 'Cart cleared');
    }

    public function getCart(): Cart
    {
        $sessionId = session()->getId();

        if (Auth::check()) {
            $cart = Cart::query()->where('user_id', Auth::id())->first();

            if (! $cart) {
                $guestCart = Cart::query()
                    ->where('session_id', $sessionId)
                    ->whereNull('user_id')
                    ->first();

                if ($guestCart) {
                    $guestCart->update([
                        'user_id' => Auth::id(),
                        'session_id' => $sessionId,
                        'expires_at' => now()->addDays(7),
                    ]);
                    $cart = $guestCart;
                }
            }

            if (! $cart) {
                $cart = Cart::create([
                    'session_id' => $sessionId,
                    'user_id' => Auth::id(),
                    'items' => [],
                    'expires_at' => now()->addDays(7),
                ]);
            }

            return $cart;
        }

        $cart = Cart::query()
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->first();

        if (! $cart) {
            $cart = Cart::create([
                'session_id' => $sessionId,
                'user_id' => null,
                'items' => [],
                'expires_at' => now()->addDays(7),
            ]);
        }

        return $cart;
    }

    protected function syncCartCountSession(): void
    {
        $cart = $this->getCart();
        $count = collect($cart->items ?? [])->sum('quantity');
        session(['cart_count' => (int) $count]);
    }
}
