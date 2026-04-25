<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('SHOP_ADMIN_EMAILS', 'admin@example.com');
        $firstAdmin = explode(',', $adminEmail)[0];
        $firstAdmin = trim($firstAdmin) ?: 'admin@example.com';

        User::factory()->create([
            'name' => 'Admin User',
            'email' => $firstAdmin,
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Demo Customer',
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $categoryRows = [
            [
                'name' => 'Grains & flours',
                'slug' => 'grains-flours',
                'description' => 'Garri, elubo, semo, and flours for swallows, baking, and breakfast.',
                'sort_order' => 10,
            ],
            [
                'name' => 'Rice & beans',
                'slug' => 'rice-beans',
                'description' => 'Ofada, parboiled rice, oloyin beans, and more.',
                'sort_order' => 20,
            ],
            [
                'name' => 'Oils & fats',
                'slug' => 'oils-fats',
                'description' => 'Red palm oil, groundnut oil, and vegetable oil.',
                'sort_order' => 30,
            ],
            [
                'name' => 'Soups & spices',
                'slug' => 'soups-spices',
                'description' => 'Egusi, crayfish, pepper, and seasonings for Nigerian soups.',
                'sort_order' => 40,
            ],
            [
                'name' => 'Fresh produce',
                'slug' => 'fresh-produce',
                'description' => 'Plantain, yam, peppers, and vegetables.',
                'sort_order' => 50,
            ],
            [
                'name' => 'Pantry',
                'slug' => 'pantry',
                'description' => 'Packaged goods and quick meals.',
                'sort_order' => 60,
            ],
        ];

        $categories = collect($categoryRows)->mapWithKeys(function (array $row) {
            $cat = Category::query()->create([
                'name' => $row['name'],
                'slug' => $row['slug'],
                'description' => $row['description'],
                'image' => null,
                'is_active' => true,
                'sort_order' => $row['sort_order'],
            ]);

            return [$row['slug'] => $cat];
        });

        $products = [
            [
                'category' => 'grains-flours',
                'name' => 'Ijebu white garri (2kg)',
                'short' => 'Fermented cassava granules, crisp and slightly sour — perfect for eba.',
                'price' => 18.99,
                'compare' => 22.5,
                'sku' => 'BOA-GAR-2K',
                'qty' => 80,
                'featured' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1509440159591-0d0210b8c7e3?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'grains-flours',
                'name' => 'Elubo (amala flour, 1kg)',
                'short' => 'Smooth yam flour for classic amala dudu.',
                'price' => 8.5,
                'compare' => null,
                'sku' => 'BOA-AMALA-1K',
                'qty' => 60,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1623428187969-5da2dcea5ebf?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'grains-flours',
                'name' => 'Pounded yam flour (2kg)',
                'short' => 'Quick pounded yam from flour — just add water.',
                'price' => 14.25,
                'compare' => null,
                'sku' => 'BOA-PY-2K',
                'qty' => 45,
                'featured' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1604908176997-50f18c2e8fda?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'grains-flours',
                'name' => 'Semovita (2kg)',
                'short' => 'Fine semolina for smooth semo to pair with any soup.',
                'price' => 9.99,
                'compare' => null,
                'sku' => 'BOA-SEMO-2K',
                'qty' => 100,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1509440159591-0d0210b8c7e3?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'rice-beans',
                'name' => 'Ofada rice (5kg)',
                'short' => 'Stone-free local brown rice with a nutty flavor.',
                'price' => 34.5,
                'compare' => 39.99,
                'sku' => 'BOA-OFAD-5K',
                'qty' => 40,
                'featured' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1586201370056-0e0c5d0c0a0a?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'rice-beans',
                'name' => 'Parboiled rice (10kg)',
                'short' => 'Long grain parboiled rice for everyday jollof and coconut rice.',
                'price' => 28.0,
                'compare' => null,
                'sku' => 'BOA-PB-10K',
                'qty' => 70,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1516684732162-798a0062be99?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'rice-beans',
                'name' => 'Oloyin beans (honey beans, 2kg)',
                'short' => 'Sweet brown beans for ewa agoyin and moin moin.',
                'price' => 12.75,
                'compare' => null,
                'sku' => 'BOA-OLO-2K',
                'qty' => 55,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'oils-fats',
                'name' => 'Red palm oil (1L)',
                'short' => 'Unrefined palm oil for ofada, banga, and traditional dishes.',
                'price' => 6.8,
                'compare' => null,
                'sku' => 'BOA-RPO-1L',
                'qty' => 120,
                'featured' => true,
                'images' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Palm_oil_in_a_bottle.jpg/500px-Palm_oil_in_a_bottle.jpg',
                ],
            ],
            [
                'category' => 'oils-fats',
                'name' => 'Groundnut oil (750ml)',
                'short' => 'Light, nutty oil for frying and everyday cooking.',
                'price' => 5.4,
                'compare' => null,
                'sku' => 'BOA-GNO-750',
                'qty' => 90,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'oils-fats',
                'name' => 'Vegetable oil (3L)',
                'short' => 'Neutral oil for high-heat frying and baking.',
                'price' => 9.1,
                'compare' => null,
                'sku' => 'BOA-VO-3L',
                'qty' => 75,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'soups-spices',
                'name' => 'Ground egusi (melon seeds, 500g)',
                'short' => 'Toasted and ground for thick, rich egusi soup.',
                'price' => 7.2,
                'compare' => null,
                'sku' => 'BOA-EGU-500',
                'qty' => 50,
                'featured' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1599909533707-4f16f7e8a007?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'soups-spices',
                'name' => 'Dried crayfish (200g)',
                'short' => 'Aromatic crustacean base for stews, soups, and ofada sauce.',
                'price' => 10.5,
                'compare' => null,
                'sku' => 'BOA-CRY-200',
                'qty' => 40,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'soups-spices',
                'name' => 'Stockfish (medium cuts, 500g)',
                'short' => 'Dried cod for obe ata, egusi, and efo riro.',
                'price' => 16.0,
                'compare' => null,
                'sku' => 'BOA-STF-500',
                'qty' => 30,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1534939561126-855b8675d7a7?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'fresh-produce',
                'name' => 'Ripe plantain (bunch)',
                'short' => 'Sweet yellow plantain — fry, boil, or grill.',
                'price' => 4.5,
                'compare' => null,
                'sku' => 'BOA-PLT-BN',
                'qty' => 25,
                'featured' => true,
                'images' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0b/Musa_paradisiaca_11.JPG/500px-Musa_paradisiaca_11.JPG',
                ],
            ],
            [
                'category' => 'fresh-produce',
                'name' => 'Water yam (tuber, ~1.5kg)',
                'short' => 'Firm yam for pottage, pounded yam, or porridge.',
                'price' => 7.8,
                'compare' => null,
                'sku' => 'BOA-YAM-15',
                'qty' => 35,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1592841200221-a7a541b6c2d7?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'fresh-produce',
                'name' => 'Scotch bonnet peppers (ata rodo, 200g)',
                'short' => 'Fiery fresh peppers for pepper soup and yaji.',
                'price' => 3.2,
                'compare' => null,
                'sku' => 'BOA-RODO-200',
                'qty' => 60,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'fresh-produce',
                'name' => 'Fresh okra (okro, 300g)',
                'short' => 'Sliced for ila alasepo, seafood okra, and sides.',
                'price' => 2.8,
                'compare' => null,
                'sku' => 'BOA-OKR-300',
                'qty' => 40,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1592419044705-39a9c2b4a0b0?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'pantry',
                'name' => 'Instant noodles (assorted, carton)',
                'short' => 'Quick noodles for busy days — popular brands mixed.',
                'price' => 19.99,
                'compare' => null,
                'sku' => 'BOA-NOD-CTN',
                'qty' => 20,
                'featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1589302168068-964664d93a0d?auto=format&fit=crop&w=800&q=80',
                ],
            ],
        ];

        foreach ($products as $row) {
            $name = $row['name'];
            $baseSlug = Str::slug($name);
            $slug = $baseSlug.'-'.substr(sha1($name), 0, 6);
            $desc = '<p><strong>'.e($name).'</strong> — '.e($row['short']).'</p><p>Sample listing for Boachat. Adjust stock and prices in the admin.</p>';

            Product::query()->create([
                'name' => $name,
                'slug' => $slug,
                'description' => $desc,
                'short_description' => $row['short'],
                'price' => $row['price'],
                'compare_price' => $row['compare'],
                'cost_per_item' => null,
                'sku' => $row['sku'],
                'barcode' => null,
                'quantity' => $row['qty'],
                'security_stock' => 2,
                'track_quantity' => true,
                'allow_backorder' => false,
                'is_active' => true,
                'is_featured' => $row['featured'],
                'images' => $row['images'],
                'category_id' => $categories[$row['category']]->id,
            ]);
        }
    }
}
