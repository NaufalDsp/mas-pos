<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Get search query
        $search = $request->input('search');

        // Get category filter
        $categoryId = $request->input('category_id');

        // Query products
        $query = Product::with('category');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get();

        // Get all categories for filter
        $categories = Category::all();

        // Get user's cart with items
        $cart = null;
        $totalTagihan = 0;
        $cartItemsCount = 0;

        if (auth()->check()) {
            $cart = Cart::with(['items.product'])->firstOrCreate([
                'user_id' => auth()->id()
            ]);

            $totalTagihan = $cart->total;
            $cartItemsCount = $cart->items->sum('quantity');
        }

        return Inertia::render('HomePage', [
            'products' => $products,
            'categories' => $categories,
            'totalTagihan' => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
            'cartItemsCount' => $cartItemsCount,
        ]);
    }
}
