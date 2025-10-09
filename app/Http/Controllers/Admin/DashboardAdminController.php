<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardAdminController extends Controller
{
    public function index()
    {
        // Only users role
        $totalUsers = User::where('role', 'user')->count();
        $totalProducts = Product::count();
        $totalTransactions = Transaction::count();
        $totalRevenue = Transaction::sum('total');

        // Monthly transactions (last 6 months)
        $monthlyTransactions = Transaction::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(total) as revenue')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Top 5 products
        $topProducts = Product::select('products.*', DB::raw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold'))
            ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->with('category')
            ->groupBy('products.id')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Get users (kasir only)
        $users = User::where('role', 'user')
            ->select('id', 'name', 'email', 'created_at')
            ->latest()
            ->get();

        // Get products
        $products = Product::with('category')->get();

        // Get transactions
        $transactions = Transaction::with(['user', 'items.product'])
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('DashboardAdmin', [
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
