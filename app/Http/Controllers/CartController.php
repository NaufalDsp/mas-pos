<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        if (!$userId) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $cart = Cart::with(['items.product'])->firstOrCreate([
            'user_id' => $userId
        ]);

        $totalTagihan = $cart->total;

        return Inertia::render('Cart/CartPage', [
            'cartItems' => $cart->items,
            'totalTagihan' => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
        ]);
    }

    public function addItem(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Check stock
        if ($product->stock < ($validated['quantity'] ?? 1)) {
            return back()->with('error', 'Stok tidak mencukupi');
        }

        // Get or create cart
        $cart = Cart::firstOrCreate([
            'user_id' => $userId
        ]);

        // Check if product already in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + ($validated['quantity'] ?? 1);

            if ($product->stock < $newQuantity) {
                return back()->with('error', 'Stok tidak mencukupi');
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'] ?? 1,
                'price' => $product->price
            ]);
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    public function updateQuantity(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        // Check stock
        if ($cartItem->product->stock < $validated['quantity']) {
            return back()->with('error', 'Stok tidak mencukupi');
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Jumlah berhasil diperbarui');
    }

    public function removeItem(CartItem $cartItem)
    {
        $cartItem->delete();

        return back()->with('success', 'Produk berhasil dihapus dari keranjang');
    }

    public function checkout()
    {
        $userId = Auth::id();
        if (!$userId) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $cart = Cart::with(['items.product'])->where('user_id', $userId)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Keranjang kosong');
        }

        DB::beginTransaction();
        try {
            // Create transaction
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'total' => $cart->total
            ]);

            // Create transaction items and update stock
            foreach ($cart->items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal
                ]);

                // Update product stock
                $product = Product::find($item->product_id);
                $product->decrement('stock', $item->quantity);
            }

            // Clear cart
            $cart->items()->delete();

            DB::commit();

            return redirect()->route('home')->with('success', 'Pembayaran berhasil');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
