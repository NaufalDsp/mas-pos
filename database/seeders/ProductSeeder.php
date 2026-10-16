<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['sku' => 'PRD-0001', 'name' => 'Pepperoni Cheese', 'category_id' => 1, 'price' => 45000, 'cost_price' => 30000, 'stock' => 50],
            ['sku' => 'PRD-0002', 'name' => 'Pepperoni Fiesta', 'category_id' => 1, 'price' => 55000, 'cost_price' => 38000, 'stock' => 30],
            ['sku' => 'PRD-0003', 'name' => 'Burger Gilz', 'category_id' => 2, 'price' => 29000, 'cost_price' => 18000, 'stock' => 40],
            ['sku' => 'PRD-0004', 'name' => 'Big Mac Cheese', 'category_id' => 2, 'price' => 31000, 'cost_price' => 20000, 'stock' => 35],
            ['sku' => 'PRD-0005', 'name' => 'Coca Cola', 'category_id' => 3, 'price' => 10000, 'cost_price' => 6000, 'stock' => 100],
            ['sku' => 'PRD-0006', 'name' => 'Lechy Tea', 'category_id' => 3, 'price' => 12000, 'cost_price' => 7000, 'stock' => 80],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
