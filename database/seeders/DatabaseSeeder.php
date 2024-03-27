<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;
use App\Models\Vendor;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        ItemCategory::insert([
            ['name' => 'Electronics'],
            ['name' => 'Books'],
            ['name' => 'Clothes'],
        ]);

        Item::insert([
            [
                'name' => 'Laptop',
                'category_id' => 1,
                'available_date' => now()->addMonth(),
                'price' => 1000000.00,
                'weight_kg' => 2.5,
            ],
            [
                'name' => 'Smartphone',
                'category_id' => 1,
                'available_date' => now()->addMonth(),
                'price' => 5000000.00,
                'weight_kg' => 0.5,
            ],
            [
                'name' => 'PHP Book',
                'category_id' => 2,
                'available_date' => now()->addMonth(),
                'price' => 500000.00,
                'weight_kg' => 0.3,
            ],
            [
                'name' => 'Laravel Book',
                'category_id' => 2,
                'available_date' => now()->addMonth(),
                'price' => 600000.00,
                'weight_kg' => 0.4,
            ],
            [
                'name' => 'T-Shirt',
                'category_id' => 3,
                'available_date' => now()->addMonth(),
                'price' => 200000.00,
                'weight_kg' => 0.2,
            ],
            [
                'name' => 'Jeans',
                'category_id' => 3,
                'available_date' => now()->addMonth(),
                'price' => 300000.00,
                'weight_kg' => 0.6,
            ],
        ]);

        Vendor::insert([
            [
                'name' => 'Vendor 1',
                'address' => 'Vendor 1 Address',
                'email' => 'vendor@vendor.com',
                'phone' => '1234567890',
                'bank_name' => 'Vendor 1 Bank',
                'bank_account' => '1234567890',
            ],
            [
                'name' => 'Vendor 2',
                'address' => 'Vendor 2 Address',
                'email' => 'vendor2@vendor.com',
                'phone' => '1234567890',
                'bank_name' => 'Vendor 2 Bank',
                'bank_account' => '1234567890',
            ],
        ]);
    }
}
