<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Cart;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function create()
    {
        // Get cart info
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

        return Inertia::render('Category/AddCategory', [
            'totalTagihan' => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
            'cartItemsCount' => $cartItemsCount,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name'
        ], [
            'name.required' => 'Nama kategori wajib diisi',
            'name.unique' => 'Kategori sudah tersedia'
        ]);

        Category::create($validated);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function index()
    {
        $categories = Category::withCount('products')->get();

        return response()->json($categories);
    }
}
