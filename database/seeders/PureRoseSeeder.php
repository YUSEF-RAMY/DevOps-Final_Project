<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Occasion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PureRoseSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::get()->update([
            'site_name' => 'PURE ROSE',
            'meta_title' => 'PURE ROSE — Luxury Floral Boutique',
            'meta_description' => 'Crafting moments, one pure petal at a time. Premium bouquets, subscriptions, and bespoke gifting.',
            'theme' => 'pure-rose',
        ]);

        $roses = Category::updateOrCreate(
            ['slug' => 'signature-bouquets'],
            ['name' => 'Signature Bouquets', 'is_active' => true, 'description' => 'PURE ROSE signature floral artistry']
        );

        $catalog = [
            [
                'name' => 'The Aurora Bouquet — Mixed Pastels',
                'slug' => 'aurora-bouquet-mixed-pastels',
                'sku' => 'PR-AURORA-001',
                'price' => 450,
                'image' => 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?q=80&w=600',
                'description' => 'A dreamy cascade of blush peonies, soft roses, and whisper-light ranunculus — the Aurora Bouquet captures dawn in bloom.',
            ],
            [
                'name' => 'The Lavender Symphony — Purple Roses',
                'slug' => 'lavender-symphony-purple-roses',
                'sku' => 'PR-LAVENDER-002',
                'price' => 520,
                'image' => 'https://images.unsplash.com/photo-1596436889106-be35e843f974?q=80&w=600',
                'description' => 'Velvet purple roses arranged in a symphony of texture — elegant, regal, unforgettable.',
            ],
            [
                'name' => 'The Royal Crimson — Dark Red Roses',
                'slug' => 'royal-crimson-dark-red-roses',
                'sku' => 'PR-CRIMSON-003',
                'price' => 480,
                'image' => 'https://images.unsplash.com/photo-1526047932273-341f2a7631f9?q=80&w=600',
                'description' => 'Deep crimson roses for grand gestures — passion sculpted petal by petal.',
            ],
        ];

        foreach ($catalog as $item) {
            $product = Product::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'category_id' => $roses->id,
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'short_description' => Str::limit($item['description'], 120),
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'compare_at_price' => $item['price'] + 80,
                    'stock' => 50,
                    'is_active' => true,
                    'is_featured' => true,
                ]
            );

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'position' => 0],
                ['path' => $item['image'], 'is_primary' => true]
            );
        }

        $amira = User::where('email', 'like', '%@%')->first();
        if ($amira) {
            Occasion::updateOrCreate(
                ['user_id' => $amira->id, 'recipient_name' => 'Nour', 'occasion_name' => 'Graduation'],
                [
                    'relation_type' => 'Friend',
                    'occasion_date' => now()->addMonths(2)->format('Y-m-d'),
                    'reminder_status' => true,
                    'reminder_days_before' => 14,
                ]
            );

            Occasion::updateOrCreate(
                ['user_id' => $amira->id, 'recipient_name' => 'Self', 'occasion_name' => 'Birthday'],
                [
                    'relation_type' => 'Self',
                    'occasion_date' => '2000-07-07',
                    'reminder_status' => true,
                    'reminder_days_before' => 7,
                ]
            );
        }
    }
}
