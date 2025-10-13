<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function create()
    {
        $categories = Category::all();

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

        return Inertia::render('Product/AddProduct', [
            'categories' => $categories,
            'totalTagihan' => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
            'cartItemsCount' => $cartItemsCount,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        // Set default stock if not provided
        $validated['stock'] = $validated['stock'] ?? 0;

        Product::create($validated);

        return redirect()->route('home')->with('success', 'Produk berhasil ditambahkan');
    }

    public function destroy(Product $product)
    {
        // Delete image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('home')->with('success', 'Produk berhasil dihapus');
    }

    public function index()
    {
        $products = Product::with('category')->get();

        return response()->json($products);
    }
}
