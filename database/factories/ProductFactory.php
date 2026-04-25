<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.substr(uniqid(), -8),
            'description' => '<p>'.implode('</p><p>', fake()->paragraphs(3)).'</p>',
            'short_description' => fake()->sentence(8),
            'price' => fake()->randomFloat(2, 9, 299),
            'compare_price' => fake()->optional(0.3)->randomFloat(2, 300, 400),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'quantity' => fake()->numberBetween(5, 200),
            'security_stock' => 2,
            'track_quantity' => true,
            'allow_backorder' => false,
            'is_active' => true,
            'is_featured' => fake()->boolean(25),
            'images' => [
                'https://placehold.co/800x800/fbbf24/422006?text='.urlencode(Str::limit($name, 12)),
            ],
            'category_id' => Category::factory(),
        ];
    }
}
