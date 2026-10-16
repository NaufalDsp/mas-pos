<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'invoice_code')) {
                $table->string('invoice_code')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('transactions', 'payment_method')) {
                $table->string('payment_method')->default('cash')->after('total');
            }
            if (!Schema::hasColumn('transactions', 'pay_amount')) {
                $table->decimal('pay_amount', 12, 2)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('transactions', 'change_amount')) {
                $table->decimal('change_amount', 12, 2)->default(0)->after('pay_amount');
            }
            if (!Schema::hasColumn('transactions', 'notes')) {
                $table->text('notes')->nullable()->after('change_amount');
            }
        });

        // Backfill existing transactions
        $transactions = DB::table('transactions')->whereNull('invoice_code')->get();
        foreach ($transactions as $tx) {
            $date = $tx->created_at ? date('Ymd', strtotime($tx->created_at)) : date('Ymd');
            DB::table('transactions')
                ->where('id', $tx->id)
                ->update([
                    'invoice_code' => sprintf('INV-%s-%04d', $date, $tx->id),
                    'pay_amount' => $tx->total,
                    'change_amount' => 0,
                    'payment_method' => 'cash',
                ]);
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->default(0)->after('price');
            }
        });

        // Populate initial SKUs for products that don't have one
        $products = DB::table('products')->whereNull('sku')->get();
        foreach ($products as $p) {
            DB::table('products')
                ->where('id', $p->id)
                ->update([
                    'sku' => sprintf('PRD-%04d', $p->id),
                    'cost_price' => round($p->price * 0.7), // estimated 70% cost price for initial profit demo
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['invoice_code', 'payment_method', 'pay_amount', 'change_amount', 'notes']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'cost_price']);
        });
    }
};
