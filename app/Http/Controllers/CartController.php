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
        $cart = Cart::with(['items.product'])->firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $totalTagihan = $cart->total;
        $cartItemsCount = $cart->items->sum('quantity');

        return Inertia::render('Cart/CartPage', [
            'cartItems' => $cart->items,
            'totalTagihan' => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
            'totalTagihanRaw' => $totalTagihan,
            'cartItemsCount' => $cartItemsCount,
        ]);
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'nullable|integer|min:1'
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $quantity = $validated['qty'] ?? 1;

        if ($product->stock < $quantity) {
            return back()->with('error', 'Stok tidak mencukupi');
        }

        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;

            if ($product->stock < $newQuantity) {
                return back()->with('error', 'Stok tidak mencukupi');
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
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
        $cart = Cart::with(['items.product'])->where('user_id', auth()->id())->first();

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
                // Check stock again
                if ($item->product->stock < $item->quantity) {
                    throw new \Exception("Stok {$item->product->name} tidak mencukupi");
                }

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal
                ]);

                // Update product stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear cart
            $cart->items()->delete();

            DB::commit();

            // Redirect to success page (must be GET for Inertia)
            return redirect()->route('payment.success', $transaction);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Show payment success page (GET)
    public function paymentSuccess(Transaction $transaction)
    {
        return Inertia::render('PaymentSuccess', [
            'transaction' => [
                'id' => $transaction->id,
                'total' => $transaction->total,
                'total_formatted' => 'Rp ' . number_format($transaction->total, 0, ',', '.'),
                'date' => $transaction->created_at->format('d F Y'),
            ],
        ]);
    }
}
