<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalProducts = Product::count();
        $totalTransactions = Transaction::count();
        $totalRevenue = Transaction::sum('total');

        // Statistik transaksi per bulan (6 bulan terakhir)
        $monthlyTransactions = Transaction::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(total) as revenue')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Top 5 produk terlaris
        $topProducts = Product::select('products.*', DB::raw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold'))
            ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->with('category')
            ->groupBy('products.id', 'products.name', 'products.category_id', 'products.price', 'products.stock', 'products.description', 'products.image', 'products.created_at', 'products.updated_at')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Get users
        $users = User::where('role', 'user')
            ->select('id', 'name', 'email', 'created_at')
            ->latest()
            ->get();

        // Get products with category
        $products = Product::with('category')
            ->select('products.*')
            ->get();

        // Get transactions with relationships
        $transactions = Transaction::with(['user', 'items.product'])
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('Admin/DashboardAdmin', [
            'stats' => [
                'total_users' => $totalUsers,
                'total_products' => $totalProducts,
                'total_transactions' => $totalTransactions,
                'total_revenue' => $totalRevenue,
            ],
            'monthlyTransactions' => $monthlyTransactions,
            'topProducts' => $topProducts,
            'users' => $users,
            'products' => $products,
            'transactions' => $transactions,
        ]);
    }
}
