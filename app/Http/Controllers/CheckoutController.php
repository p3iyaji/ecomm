<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Jobs\ProcessOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function index(Request $request, CartController $carts)
    {
        $cart = $carts->getCart();

        if (empty($cart->items)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to checkout');
        }

        $shippingMethod = $request->get('shipping_method', 'standard');
        $subtotal = collect($cart->items)->sum('total');
        $tax = $this->calculateTax($subtotal);
        $shippingCost = $this->getShippingCost($shippingMethod);
        $total = round($subtotal + $tax + $shippingCost, 2);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) round($total * 100),
                'currency' => config('shop.currency', 'usd'),
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'cart_id' => (string) $cart->id,
                    'user_id' => (string) Auth::id(),
                    'user_email' => Auth::user()->email,
                    'shipping_method' => $shippingMethod,
                ],
            ]);

            return Inertia::render('Checkout/Index', [
                'cart' => $cart,
                'clientSecret' => $paymentIntent->client_secret,
                'stripeKey' => config('services.stripe.key'),
                'summary' => [
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'shipping_cost' => $shippingCost,
                    'total' => $total,
                    'shipping_method' => $shippingMethod,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe Payment Intent creation failed: '.$e->getMessage());

            return back()->withErrors(['error' => 'Unable to initialize payment. Please try again.']);
        }
    }

    public function process(Request $request, CartController $carts)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'shipping_address' => 'required|array',
            'shipping_address.address_line1' => 'required|string|max:255',
            'shipping_address.city' => 'required|string|max:120',
            'shipping_address.state' => 'required|string|max:120',
            'shipping_address.postal_code' => 'required|string|max:32',
            'shipping_address.country' => 'required|string|max:120',
            'billing_address' => 'required|array',
            'billing_address.address_line1' => 'required|string|max:255',
            'billing_address.city' => 'required|string|max:120',
            'billing_address.state' => 'required|string|max:120',
            'billing_address.postal_code' => 'required|string|max:32',
            'billing_address.country' => 'required|string|max:120',
            'shipping_method' => 'required|string|in:standard,express,next_day',
        ]);

        try {
            DB::beginTransaction();

            $cart = $carts->getCart();

            if (empty($cart->items)) {
                throw new \Exception('Your cart is empty');
            }

            Stripe::setApiKey(config('services.stripe.secret'));
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                throw new \Exception('Payment not successful. Status: '.$paymentIntent->status);
            }

            $subtotal = collect($cart->items)->sum('total');
            $tax = $this->calculateTax($subtotal);
            $shippingCost = $this->getShippingCost($request->shipping_method);
            $total = round($subtotal + $tax + $shippingCost, 2);

            $expectedCents = (int) round($total * 100);
            if ((int) $paymentIntent->amount !== $expectedCents) {
                throw new \Exception('Payment amount does not match order total. Please refresh checkout.');
            }

            $customer = Customer::query()->firstOrCreate(
                ['user_id' => Auth::id()],
                [
                    'phone' => $request->shipping_address['phone'] ?? null,
                    'address' => $request->shipping_address['address_line1'],
                    'city' => $request->shipping_address['city'],
                    'state' => $request->shipping_address['state'],
                    'postal_code' => $request->shipping_address['postal_code'],
                    'country' => $request->shipping_address['country'],
                ]
            );

            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_id' => $customer->id,
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'discount' => 0,
                'total' => $total,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => 'stripe',
                'payment_id' => $paymentIntent->id,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'metadata' => [
                    'payment_intent' => $paymentIntent->id,
                    'shipping_method' => $request->shipping_method,
                    'customer_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'paid_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $product = \App\Models\Product::query()->find($item['product_id']);

                if (! $product) {
                    throw new \Exception("Product not found: {$item['name']}");
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'total' => (float) $product->price * (int) $item['quantity'],
                    'attributes' => $item['attributes'] ?? [],
                ]);
            }

            $cart->items = [];
            $cart->save();

            DB::commit();

            ProcessOrder::dispatch($order)->onQueue('orders');

            return redirect()->route('checkout.success', ['order' => $order->order_number]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Checkout failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'payment_intent' => $request->payment_intent_id,
            ]);

            return back()->withErrors(['error' => 'Checkout failed: '.$e->getMessage()]);
        }
    }

    public function success(string $orderNumber)
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->with(['items', 'user'])
            ->firstOrFail();

        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('Checkout/Success', [
            'order' => $order,
        ]);
    }

    protected function calculateTax(float $subtotal): float
    {
        return round($subtotal * (float) config('shop.tax_rate', 0.1), 2);
    }

    protected function getShippingCost(string $method): float
    {
        $shippingMethods = [
            'standard' => 5.00,
            'express' => 15.00,
            'next_day' => 25.00,
        ];

        return $shippingMethods[$method] ?? 5.00;
    }
}
