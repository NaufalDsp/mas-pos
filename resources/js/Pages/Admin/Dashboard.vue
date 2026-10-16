<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    stats: Object,
    chartData: Array,
    currentPeriod: String,
    paymentDistribution: Object,
    topProducts: Array,
    lowStockProducts: Array,
    recentTransactions: Array,
});

const chartCanvas = ref(null);
let chartInstance = null;
const loading = ref(false);
const selectedPeriod = ref(props.currentPeriod || 'day');

const periodOptions = [
    { value: 'day', label: '7 Hari Terakhir' },
    { value: 'week', label: '8 Minggu Terakhir' },
    { value: 'month', label: '12 Bulan Terakhir' },
    { value: 'year', label: '5 Tahun Terakhir' },
];

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const changePeriod = (period) => {
    if (selectedPeriod.value === period) return;
    selectedPeriod.value = period;
    loading.value = true;

    router.get(
        route('admin.dashboard'),
        { period: period },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                loading.value = false;
                nextTick(() => initChart());
            },
            onError: () => {
                loading.value = false;
            },
        }
    );
};

const initChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }

    const ctx = chartCanvas.value?.getContext('2d');
    if (!ctx) return;

    const labels = props.chartData.map((item) => item.label);
    const revenueData = props.chartData.map((item) => parseFloat(item.revenue) || 0);
    const transactionData = props.chartData.map((item) => parseInt(item.transactions) || 0);

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pendapatan (Rp)',
                    data: revenueData,
                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1.5,
                    borderRadius: 8,
                    yAxisID: 'y',
                    order: 2,
                },
                {
                    label: 'Jumlah Transaksi',
                    data: transactionData,
                    type: 'line',
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: 'rgb(99, 102, 241)',
                    pointRadius: 4,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y1',
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        font: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                    },
                },
                tooltip: {
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    callbacks: {
                        label: function (context) {
                            if (context.dataset.yAxisID === 'y') {
                                return ` Omset: ${formatCurrency(context.raw)}`;
                            }
                            return ` Transaksi: ${context.raw} struk`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } },
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: { color: 'rgba(226, 232, 240, 0.6)' },
                    ticks: {
                        callback: (value) => {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                            return value;
                        },
                        font: { family: 'Plus Jakarta Sans', size: 11 },
                    },
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        stepSize: 1,
                        font: { family: 'Plus Jakarta Sans', size: 11 },
                    },
                },
            },
        },
    });
};

onMounted(() => {
    nextTick(() => initChart());
});

watch(
    () => props.chartData,
    () => {
        nextTick(() => initChart());
    }
);
</script>

<template>
    <Head title="Admin Dashboard - Mas POS" />

    <AdminLayout title="Ringkasan Analitik & Performa Penjualan">
        <template #header>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Dashboard Overview</h1>
                <p class="text-xs text-slate-500 mt-0.5">Pantau pendapatan, laba, perputaran stok, dan aktivitas kasir real-time.</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Top KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Omset -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Omset</p>
                            <h3 class="text-2xl font-black text-slate-900 mt-1.5">{{ formatCurrency(stats.total_revenue) }}</h3>
                            <div class="flex items-center gap-1.5 mt-2 text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full w-fit">
                                <span>Hari Ini:</span>
                                <span>{{ formatCurrency(stats.today_revenue) }}</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Estimasi Laba Bersih -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estimasi Laba Kotor</p>
                            <h3 class="text-2xl font-black text-indigo-600 mt-1.5">{{ formatCurrency(stats.estimated_profit) }}</h3>
                            <p class="text-xs text-slate-400 mt-2">Omset dikurangi estimasi HPP</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Transaksi -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Transaksi</p>
                            <h3 class="text-2xl font-black text-slate-900 mt-1.5">{{ stats.total_transactions }} <span class="text-sm font-semibold text-slate-400">Nota</span></h3>
                            <div class="flex items-center gap-1.5 mt-2 text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full w-fit">
                                <span>Hari Ini:</span>
                                <span>{{ stats.today_transactions }} Nota</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Stok Menipis Alert -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peringatan Stok</p>
                            <h3 class="text-2xl font-black text-rose-600 mt-1.5">{{ stats.low_stock_count }} <span class="text-sm font-semibold text-slate-400">Produk</span></h3>
                            <Link :href="route('admin.products.index', { stock_filter: 'low' })" class="text-xs font-bold text-rose-500 hover:text-rose-700 underline mt-2 block">
                                Cek & Restock &rarr;
                            </Link>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart & Payment Distribution Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Revenue Chart (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Tren Pendapatan & Volume Penjualan</h2>
                            <p class="text-xs text-slate-500">Pergerakan transaksi kasir berdasarkan periode waktu</p>
                        </div>
                        <!-- Period Selector -->
                        <div class="flex items-center bg-slate-100 p-1 rounded-xl w-fit">
                            <button
                                v-for="opt in periodOptions"
                                :key="opt.value"
                                @click="changePeriod(opt.value)"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition"
                                :class="selectedPeriod === opt.value
                                    ? 'bg-white text-slate-900 shadow-sm'
                                    : 'text-slate-500 hover:text-slate-800'"
                            >
                                {{ opt.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Canvas Area -->
                    <div class="mt-4 h-72 w-full relative">
                        <canvas ref="chartCanvas"></canvas>
                    </div>
                </div>

                <!-- Payment Method Breakdown (1 col) -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Metode Pembayaran</h2>
                        <p class="text-xs text-slate-500">Distribusi cara pembayaran pelanggan</p>

                        <div class="mt-6 space-y-4">
                            <!-- Cash -->
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-black text-xs flex items-center justify-center">
                                            💵
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">Tunai (Cash)</p>
                                            <p class="text-[11px] text-slate-400">{{ paymentDistribution?.cash?.count || 0 }} Transaksi</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-extrabold text-slate-900">{{ formatCurrency(paymentDistribution?.cash?.total || 0) }}</span>
                                </div>
                            </div>

                            <!-- QRIS -->
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 font-black text-xs flex items-center justify-center">
                                            📱
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">QRIS Instant</p>
                                            <p class="text-[11px] text-slate-400">{{ paymentDistribution?.qris?.count || 0 }} Transaksi</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-extrabold text-slate-900">{{ formatCurrency(paymentDistribution?.qris?.total || 0) }}</span>
                                </div>
                            </div>

                            <!-- Transfer / Debit -->
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 font-black text-xs flex items-center justify-center">
                                            💳
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">Transfer / Debit</p>
                                            <p class="text-[11px] text-slate-400">{{ paymentDistribution?.transfer?.count || 0 }} Transaksi</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-extrabold text-slate-900">{{ formatCurrency(paymentDistribution?.transfer?.total || 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-100">
                        <Link
                            :href="route('admin.transactions.index')"
                            class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition"
                        >
                            <span>Lihat Semua Laporan</span>
                            <span>&rarr;</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Lower Section: Top Products & Recent Transactions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top 5 Best Sellers -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">5 Produk Terlaris</h2>
                            <p class="text-xs text-slate-500">Barang dengan volume penjualan tertinggi</p>
                        </div>
                        <Link :href="route('admin.products.index')" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">
                            Semua Produk &rarr;
                        </Link>
                    </div>

                    <div class="mt-4 divide-y divide-slate-100">
                        <div
                            v-for="(product, idx) in topProducts"
                            :key="product.id"
                            class="py-3 flex items-center justify-between gap-4"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-extrabold text-xs flex items-center justify-center shrink-0">
                                    {{ idx + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ product.name }}</p>
                                    <p class="text-xs text-slate-400">{{ product.category?.name || 'Uncategorized' }} &bull; Sisa Stok: {{ product.stock }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs font-black text-slate-900">{{ product.total_sold }} Terjual</p>
                                <p class="text-[11px] font-medium text-emerald-600">{{ formatCurrency(product.price * product.total_sold) }}</p>
                            </div>
                        </div>
                        <div v-if="!topProducts.length" class="py-8 text-center text-xs text-slate-400">
                            Belum ada riwayat penjualan produk.
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Transaksi Terbaru</h2>
                            <p class="text-xs text-slate-500">Aktivitas pembayaran kasir terkini</p>
                        </div>
                        <Link :href="route('admin.transactions.index')" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">
                            Riwayat Lengkap &rarr;
                        </Link>
                    </div>

                    <div class="mt-4 divide-y divide-slate-100">
                        <div
                            v-for="tx in recentTransactions"
                            :key="tx.id"
                            class="py-3 flex items-center justify-between gap-4"
                        >
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-900">{{ tx.invoice_code || ('INV-' + tx.id) }}</span>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                        :class="{
                                            'bg-emerald-100 text-emerald-800': tx.payment_method === 'cash',
                                            'bg-blue-100 text-blue-800': tx.payment_method === 'qris',
                                            'bg-purple-100 text-purple-800': tx.payment_method === 'transfer'
                                        }"
                                    >
                                        {{ tx.payment_method || 'cash' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Kasir: <strong class="text-slate-600">{{ tx.user?.name || 'Kasir Umum' }}</strong> &bull; {{ formatDate(tx.created_at) }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-sm font-black text-slate-900">{{ formatCurrency(tx.total) }}</span>
                                <span class="block text-[11px] text-slate-400">{{ tx.items?.length || 0 }} jenis item</span>
                            </div>
                        </div>
                        <div v-if="!recentTransactions.length" class="py-8 text-center text-xs text-slate-400">
                            Belum ada transaksi yang tercatat.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

