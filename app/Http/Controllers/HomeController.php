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

        $query = Product::with('category');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get(); // image_url otomatis included

        $categories = Category::all();

        $cart = null;
        $totalTagihan = 0;
        $cartItemsCount = 0;

        if (Auth::check()) {
            $cart = Cart::with(['items.product'])->firstOrCreate([
                'user_id' => Auth::id()
            ]);

            $totalTagihan = $cart->total;
            $cartItemsCount = $cart->items->sum('quantity');
        }

        return Inertia::render('HomePage', [
            'products' => $products,
            'categories' => $categories,
            'cart_total_formatted' => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
            'cart_total' => $totalTagihan,
            'cart_count' => $cartItemsCount,
        ]);
    }
}
