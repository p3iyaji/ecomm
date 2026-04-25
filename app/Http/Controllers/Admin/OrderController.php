<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with(['user', 'items']);

        if ($request->filled('search')) {
            $s = $request->string('search');
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                    ->orWhereHas('user', function ($q2) use ($s) {
                        $q2->where('email', 'like', "%{$s}%")
                            ->orWhere('name', 'like', "%{$s}%");
                    });
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        $orders = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'payment_status']),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'customer', 'items.product']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $data = $request->validated();
        $newStatus = $data['status'];
        $newPay = $data['payment_status'];
        $oldPay = $order->payment_status;

        if ($newPay === Order::PAYMENT_PAID && $oldPay !== Order::PAYMENT_PAID) {
            $order->markAsPaid($data['payment_id'] ?? $order->payment_id);
        } elseif (array_key_exists('payment_id', $data)) {
            $order->update(['payment_id' => $data['payment_id']]);
        }

        $order->refresh();
        $oldStatus = $order->status;

        if ($newStatus === Order::STATUS_CANCELLED && $oldStatus !== Order::STATUS_CANCELLED) {
            $order->cancel($data['cancellation_reason'] ?? null);
        } elseif ($newStatus === Order::STATUS_SHIPPED && $oldStatus !== Order::STATUS_SHIPPED) {
            $order->markAsShipped();
        } elseif ($newStatus === Order::STATUS_DELIVERED && $oldStatus !== Order::STATUS_DELIVERED) {
            $order->markAsDelivered();
        } else {
            $order->update([
                'status' => $newStatus,
                'payment_status' => $data['payment_status'],
            ]);
        }

        if (array_key_exists('notes', $data)) {
            $order->update(['notes' => $data['notes']]);
        }

        return redirect()
            ->back()
            ->with('success', 'Order updated.');
    }
}
