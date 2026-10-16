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
use Carbon\Carbon;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::with(['items.product.category'])->firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $totalTagihan = (float) $cart->total;
        $cartItemsCount = (int) $cart->items->sum('quantity');

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
            return back()->with('error', "Stok {$product->name} tidak mencukupi (sisa {$product->stock})");
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
                return back()->with('error', "Stok {$product->name} tidak mencukupi untuk jumlah tersebut.");
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

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function updateQuantity(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $product = $cartItem->product;
        if ($product->stock < $validated['quantity']) {
            return back()->with('error', "Stok {$product->name} hanya tersisa {$product->stock}.");
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Jumlah barang berhasil diperbarui.');
    }

    public function removeItem(CartItem $cartItem)
    {
        $cartItem->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function clearCart()
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if ($cart) {
            $cart->items()->delete();
        }

        return back()->with('success', 'Keranjang berhasil dikosongkan.');
    }

    public function checkout(Request $request)
    {
        $cart = Cart::with(['items.product'])->where('user_id', Auth::id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Keranjang belanja masih kosong.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,qris,transfer',
            'pay_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $total = (float) $cart->total;
        $paymentMethod = $validated['payment_method'];
        $payAmount = $paymentMethod === 'cash' ? (float) ($validated['pay_amount'] ?? $total) : $total;

        if ($paymentMethod === 'cash' && $payAmount < $total) {
            return back()->with('error', 'Nominal uang yang dibayarkan kurang dari total tagihan.');
        }

        $changeAmount = max(0, $payAmount - $total);

        DB::beginTransaction();
        try {
            // Generate invoice code
            $today = Carbon::today()->format('Ymd');
            $countToday = Transaction::whereDate('created_at', Carbon::today())->count() + 1;
            $invoiceCode = sprintf('INV-%s-%04d', $today, $countToday);

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'invoice_code' => $invoiceCode,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'pay_amount' => $payAmount,
                'change_amount' => $changeAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create transaction items & deduct stock atomically
            foreach ($cart->items as $item) {
                // Lock row for update
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                if (!$product || $product->stock < $item->quantity) {
                    throw new \Exception("Stok {$item->product->name} tidak mencukupi saat proses checkout.");
                }

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal
                ]);

                // Decrement stock
                $product->decrement('stock', $item->quantity);
            }

            // Clear cart
            $cart->items()->delete();

            DB::commit();

            return redirect()->route('payment.success', $transaction->id)
                ->with('success', 'Transaksi berhasil diproses!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    public function paymentSuccess(Transaction $transaction)
    {
        $transaction->load(['user:id,name', 'items.product:id,name,sku']);

        return Inertia::render('PaymentSuccess', [
            'transaction' => [
                'id' => $transaction->id,
                'invoice_code' => $transaction->invoice_code ?? ('INV-' . $transaction->id),
                'total' => (float) $transaction->total,
                'total_formatted' => 'Rp ' . number_format($transaction->total, 0, ',', '.'),
                'payment_method' => $transaction->payment_method ?? 'cash',
                'pay_amount' => (float) ($transaction->pay_amount ?? $transaction->total),
                'pay_amount_formatted' => 'Rp ' . number_format($transaction->pay_amount ?? $transaction->total, 0, ',', '.'),
                'change_amount' => (float) ($transaction->change_amount ?? 0),
                'change_amount_formatted' => 'Rp ' . number_format($transaction->change_amount ?? 0, 0, ',', '.'),
                'notes' => $transaction->notes,
                'cashier_name' => $transaction->user?->name ?? 'Kasir',
                'date' => $transaction->created_at->format('d/m/Y H:i'),
                'items' => $transaction->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product?->name ?? 'Produk',
                        'sku' => $item->product?->sku ?? '-',
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'price_formatted' => 'Rp ' . number_format($item->price, 0, ',', '.'),
                        'subtotal' => (float) $item->subtotal,
                        'subtotal_formatted' => 'Rp ' . number_format($item->subtotal, 0, ',', '.'),
                    ];
                }),
            ],
        ]);
    }
}
