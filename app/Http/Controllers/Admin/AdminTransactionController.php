<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminTransactionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $cashierId = $request->input('cashier_id');
        $paymentMethod = $request->input('payment_method');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Transaction::with(['user:id,name', 'items.product:id,name,sku'])->latest();

        if ($search) {
            $query->where('invoice_code', 'like', "%{$search}%");
        }

        if ($cashierId) {
            $query->where('user_id', $cashierId);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        // Summary calculations on the filtered dataset
        $summaryQuery = clone $query;
        $totalRevenue = (float) $summaryQuery->sum('total');
        $totalTransactions = $summaryQuery->count();
        $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        $transactions = $query->paginate(15)->withQueryString();
        $cashiers = User::orderBy('name')->get(['id', 'name', 'role']);

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'cashiers' => $cashiers,
            'filters' => [
                'search' => $search,
                'cashier_id' => $cashierId,
                'payment_method' => $paymentMethod,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_transactions' => $totalTransactions,
                'avg_transaction' => $avgTransaction,
            ]
        ]);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user:id,name,email', 'items.product:id,name,sku,price']);

        return response()->json($transaction);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $search = $request->input('search');
        $cashierId = $request->input('cashier_id');
        $paymentMethod = $request->input('payment_method');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Transaction::with(['user:id,name', 'items.product'])->latest();

        if ($search) {
            $query->where('invoice_code', 'like', "%{$search}%");
        }
        if ($cashierId) {
            $query->where('user_id', $cashierId);
        }
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $fileName = 'laporan-penjualan-' . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        return response()->stream(function () use ($query) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($file, ['No. Nota', 'Tanggal', 'Waktu', 'Kasir', 'Metode Bayar', 'Total Belanja', 'Uang Bayar', 'Kembalian', 'Catatan']);

            $query->chunk(100, function ($transactions) use ($file) {
                foreach ($transactions as $tx) {
                    fputcsv($file, [
                        $tx->invoice_code ?? ('INV-' . $tx->id),
                        $tx->created_at->format('Y-m-d'),
                        $tx->created_at->format('H:i:s'),
                        $tx->user?->name ?? 'Kasir Umum',
                        strtoupper($tx->payment_method ?? 'cash'),
                        $tx->total,
                        $tx->pay_amount ?? $tx->total,
                        $tx->change_amount ?? 0,
                        $tx->notes ?? '',
                    ]);
                }
            });

            fclose($file);
        }, 200, $headers);
    }
}

