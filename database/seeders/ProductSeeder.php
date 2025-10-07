<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Pepperoni Cheese', 'category_id' => 1, 'price' => 45000, 'stock' => 50],
            ['name' => 'Pepperoni Fiesta', 'category_id' => 1, 'price' => 55000, 'stock' => 30],
            ['name' => 'Burger Gilz', 'category_id' => 2, 'price' => 29000, 'stock' => 40],
            ['name' => 'Big Mac Cheese', 'category_id' => 2, 'price' => 31000, 'stock' => 35],
            ['name' => 'Coca Cola', 'category_id' => 3, 'price' => 10000, 'stock' => 100],
            ['name' => 'Lechy Tea', 'category_id' => 3, 'price' => 12000, 'stock' => 80],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
