<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardAdminController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'day'); // day, week, month, year

        $totalUsers = User::where('role', 'user')->count();
        $totalProducts = Product::count();
        $totalTransactions = Transaction::count();
        $totalRevenue = (float) Transaction::sum('total');

        // Today's stats
        $todayRevenue = (float) Transaction::whereDate('created_at', Carbon::today())->sum('total');
        $todayTransactions = Transaction::whereDate('created_at', Carbon::today())->count();

        // Estimated profit calculation (Revenue - HPP)
        $totalCost = (float) DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->select(DB::raw('SUM(transaction_items.quantity * COALESCE(products.cost_price, 0)) as cost'))
            ->value('cost') ?? 0;
        $estimatedProfit = max(0, $totalRevenue - $totalCost);

        // Low stock alerts
        $lowStockProducts = Product::where('stock', '<=', 5)
            ->with('category')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();
        $lowStockCount = Product::where('stock', '<=', 5)->count();

        // Chart data based on selected period
        $chartData = $this->getChartData($period);

        // Payment method distribution
        $paymentDistribution = Transaction::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as sum'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->payment_method => [
                        'count' => (int) $item->count,
                        'total' => (float) $item->sum,
                    ]
                ];
            });

        // Top 5 best selling products
        $topProducts = Product::select('products.*', DB::raw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold'), DB::raw('COALESCE(SUM(transaction_items.subtotal), 0) as total_revenue'))
            ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->with('category')
            ->groupBy('products.id')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Latest transactions
        $recentTransactions = Transaction::with(['user:id,name', 'items.product:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users' => $totalUsers,
                'total_products' => $totalProducts,
                'total_transactions' => $totalTransactions,
                'total_revenue' => $totalRevenue,
                'today_revenue' => $todayRevenue,
                'today_transactions' => $todayTransactions,
                'estimated_profit' => $estimatedProfit,
                'low_stock_count' => $lowStockCount,
            ],
            'chartData' => $chartData,
            'currentPeriod' => $period,
            'paymentDistribution' => $paymentDistribution,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    private function getChartData($period)
    {
        switch ($period) {
            case 'day':
                return $this->getDailyData();
            case 'week':
                return $this->getWeeklyData();
            case 'month':
                return $this->getMonthlyData();
            case 'year':
                return $this->getYearlyData();
            default:
                return $this->getDailyData();
        }
    }

    private function getDailyData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $transactions = Transaction::whereDate('created_at', $date)->get();

            $data[] = [
                'label' => $date->format('d M'),
                'fullLabel' => $date->format('d M Y'),
                'revenue' => (float) $transactions->sum('total'),
                'transactions' => $transactions->count(),
            ];
        }
        return $data;
    }

    private function getWeeklyData()
    {
        $data = [];
        for ($i = 7; $i >= 0; $i--) {
            $startOfWeek = Carbon::today()->subWeeks($i)->startOfWeek();
            $endOfWeek = Carbon::today()->subWeeks($i)->endOfWeek();

            $transactions = Transaction::whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();

            $data[] = [
                'label' => 'W' . $startOfWeek->weekOfYear,
                'fullLabel' => $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M Y'),
                'revenue' => (float) $transactions->sum('total'),
                'transactions' => $transactions->count(),
            ];
        }
        return $data;
    }

    private function getMonthlyData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::today()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $transactions = Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->get();

            $data[] = [
                'label' => $date->format('M Y'),
                'fullLabel' => $date->format('F Y'),
                'revenue' => (float) $transactions->sum('total'),
                'transactions' => $transactions->count(),
            ];
        }
        return $data;
    }

    private function getYearlyData()
    {
        $data = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::today()->subYears($i)->year;
            $startOfYear = Carbon::create($year, 1, 1)->startOfDay();
            $endOfYear = Carbon::create($year, 12, 31)->endOfDay();

            $transactions = Transaction::whereBetween('created_at', [$startOfYear, $endOfYear])->get();

            $data[] = [
                'label' => (string) $year,
                'fullLabel' => 'Tahun ' . $year,
                'revenue' => (float) $transactions->sum('total'),
                'transactions' => $transactions->count(),
            ];
        }
        return $data;
    }
}
