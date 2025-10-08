<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Pizza', 'Burger', 'Minuman', 'Snack', 'Roti', 'Pasta', 'Ayam', 'Ikan', 'Nasi', 'Kue', 'Salad', 'Sup', 'Sushi', 'Taco', 'Kebab', 'Dessert', 'Seafood', 'Vegetarian', 'BBQ', 'Pancake'];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category]);
        }
    }
}
