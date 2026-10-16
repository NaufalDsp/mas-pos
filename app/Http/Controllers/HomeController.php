<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $query = Product::with('category')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get();
        $categories = Category::withCount('products')->orderBy('name')->get();

        $cart = null;
        $totalTagihan = 0;
        $cartItemsCount = 0;
        $cartItems = [];

        if (Auth::check()) {
            $cart = Cart::with(['items.product.category'])->firstOrCreate([
                'user_id' => Auth::id()
            ]);

            $totalTagihan = (float) $cart->total;
            $cartItemsCount = (int) $cart->items->sum('quantity');
            $cartItems = $cart->items;
        }

        return Inertia::render('HomePage', [
            'products' => $products,
            'categories' => $categories,
            'cartItems' => $cartItems,
            'cart_total' => $totalTagihan,
            'cart_total_formatted' => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
            'cart_count' => $cartItemsCount,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
            ],
        ]);
    }
}
