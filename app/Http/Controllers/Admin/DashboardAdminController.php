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

        // Only users role
        $totalUsers = User::where('role', 'user')->count();
        $totalProducts = Product::count();
        $totalTransactions = Transaction::count();
        $totalRevenue = Transaction::sum('total');

        // Get chart data based on period
        $chartData = $this->getChartData($period);

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
            'chartData' => $chartData,
            'currentPeriod' => $period,
            'topProducts' => $topProducts,
            'users' => $users,
            'products' => $products,
            'transactions' => $transactions,
        ]);
    }

    private function getChartData($period)
    {
        switch ($period) {
            case 'day':
                // Last 7 days
                return $this->getDailyData();
            case 'week':
                // Last 8 weeks
                return $this->getWeeklyData();
            case 'month':
                // Last 12 months
                return $this->getMonthlyData();
            case 'year':
                // Last 5 years
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
                'revenue' => $transactions->sum('total'),
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
                'revenue' => $transactions->sum('total'),
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
            $startOfMonth = $date->startOfMonth()->copy();
            $endOfMonth = $date->endOfMonth()->copy();

            $transactions = Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->get();

            $data[] = [
                'label' => $date->format('M Y'),
                'fullLabel' => $date->format('F Y'),
                'revenue' => $transactions->sum('total'),
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
                'label' => (string)$year,
                'fullLabel' => 'Tahun ' . $year,
                'revenue' => $transactions->sum('total'),
                'transactions' => $transactions->count(),
            ];
        }
        return $data;
    }
}
