<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        /** @var Product $product */
        $product = $this->route('product');
        if (! $product instanceof Product) {
            $product = Product::query()->findOrFail($this->route('product'));
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('products', 'slug')->ignore($product->id),
            ],
            'description' => ['required', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'cost_per_item' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'security_stock' => ['required', 'integer', 'min:0'],
            'track_quantity' => ['boolean'],
            'allow_backorder' => ['boolean'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'image_urls' => ['nullable', 'string'],
            'product_images' => ['nullable', 'array'],
            'product_images.*' => ['file', 'image', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('track_quantity')) {
            $this->merge(['track_quantity' => $this->boolean('track_quantity')]);
        } else {
            $this->merge(['track_quantity' => false]);
        }
        if ($this->has('allow_backorder')) {
            $this->merge(['allow_backorder' => $this->boolean('allow_backorder')]);
        } else {
            $this->merge(['allow_backorder' => false]);
        }
        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        } else {
            $this->merge(['is_active' => true]);
        }
        if ($this->has('is_featured')) {
            $this->merge(['is_featured' => $this->boolean('is_featured')]);
        } else {
            $this->merge(['is_featured' => false]);
        }
    }
}
