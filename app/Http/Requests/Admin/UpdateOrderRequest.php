<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    Order::STATUS_PENDING,
                    Order::STATUS_PROCESSING,
                    Order::STATUS_CONFIRMED,
                    Order::STATUS_SHIPPED,
                    Order::STATUS_DELIVERED,
                    Order::STATUS_CANCELLED,
                    Order::STATUS_REFUNDED,
                ]),
            ],
            'payment_status' => [
                'required',
                'string',
                Rule::in([
                    Order::PAYMENT_PENDING,
                    Order::PAYMENT_PAID,
                    Order::PAYMENT_FAILED,
                    Order::PAYMENT_REFUNDED,
                ]),
            ],
            'payment_id' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:20000'],
            'cancellation_reason' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
