<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    transactions: Object,
    cashiers: Array,
    filters: Object,
    summary: Object,
});

const search = ref(props.filters.search || '');
const cashierId = ref(props.filters.cashier_id || '');
const paymentMethod = ref(props.filters.payment_method || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

// Debounced filter triggers
let filterTimer = null;
const applyFilters = () => {
    if (filterTimer) clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        router.get(
            route('admin.transactions.index'),
            {
                search: search.value,
                cashier_id: cashierId.value,
                payment_method: paymentMethod.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300);
};

watch([search, cashierId, paymentMethod, dateFrom, dateTo], () => {
    applyFilters();
});

const resetFilters = () => {
    search.value = '';
    cashierId.value = '';
    paymentMethod.value = '';
    dateFrom.value = '';
    dateTo.value = '';
};

// Receipt / Detail Modal
const showReceiptModal = ref(false);
const activeTransaction = ref(null);

const openReceipt = (tx) => {
    activeTransaction.value = tx;
    showReceiptModal.value = true;
};

const printReceipt = () => {
    window.print();
};

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
</script>

<template>
    <Head title="Laporan & Riwayat Penjualan - Mas POS" />

    <AdminLayout title="Laporan Penjualan & Riwayat Transaksi">
        <template #header>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Laporan & Riwayat Transaksi</h1>
                <p class="text-xs text-slate-500 mt-0.5">Audit seluruh riwayat penjualan kasir, cetak ulang struk, dan ekspor data.</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filtered KPI Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Omset Terfilter</p>
                        <h4 class="text-2xl font-black text-slate-900 mt-1">{{ formatCurrency(summary.total_revenue) }}</h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        💰
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Transaksi</p>
                        <h4 class="text-2xl font-black text-blue-600 mt-1">{{ summary.total_transactions }} <span class="text-xs text-slate-400 font-semibold">Nota</span></h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        🧾
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rata-rata per Nota (AOV)</p>
                        <h4 class="text-2xl font-black text-indigo-600 mt-1">{{ formatCurrency(summary.avg_transaction) }}</h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        📈
                    </div>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Filter Data Transaksi</span>
                    <div class="flex items-center gap-2">
                        <button
                            @click="resetFilters"
                            class="text-xs text-slate-500 hover:text-slate-800 font-semibold underline"
                        >
                            Reset Filter
                        </button>
                        <a
                            :href="route('admin.transactions.export', { search, cashier_id: cashierId, payment_method: paymentMethod, date_from: dateFrom, date_to: dateTo })"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-sm transition"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Ekspor CSV</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- Search Invoice -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Cari No. Nota</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="INV-..."
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono"
                        />
                    </div>

                    <!-- Cashier Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Kasir</label>
                        <select
                            v-model="cashierId"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                        >
                            <option value="">Semua Kasir</option>
                            <option v-for="c in cashiers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <!-- Payment Method Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Metode Bayar</label>
                        <select
                            v-model="paymentMethod"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                        >
                            <option value="">Semua Metode</option>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="qris">QRIS Instant</option>
                            <option value="transfer">Transfer / Debit</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Dari Tanggal</label>
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                        />
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Sampai Tanggal</label>
                        <input
                            v-model="dateTo"
                            type="date"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Transaction Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-6 py-3.5">No. Nota</th>
                                <th class="px-6 py-3.5">Waktu Transaksi</th>
                                <th class="px-6 py-3.5">Kasir</th>
                                <th class="px-6 py-3.5">Metode Bayar</th>
                                <th class="px-6 py-3.5">Total Tagihan</th>
                                <th class="px-6 py-3.5">Dibayar</th>
                                <th class="px-6 py-3.5">Kembalian</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="tx in transactions.data"
                                :key="tx.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    {{ tx.invoice_code || ('INV-' + tx.id) }}
                                </td>
                                <td class="px-6 py-4">{{ formatDate(tx.created_at) }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ tx.user?.name || 'Kasir Umum' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                                        :class="{
                                            'bg-emerald-100 text-emerald-800': tx.payment_method === 'cash',
                                            'bg-blue-100 text-blue-800': tx.payment_method === 'qris',
                                            'bg-purple-100 text-purple-800': tx.payment_method === 'transfer'
                                        }"
                                    >
                                        {{ tx.payment_method || 'cash' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-black text-slate-900">
                                    {{ formatCurrency(tx.total) }}
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{ formatCurrency(tx.pay_amount || tx.total) }}
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ formatCurrency(tx.change_amount || 0) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        @click="openReceipt(tx)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 transition"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>Lihat Struk</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!transactions.data.length">
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Tidak ada data transaksi yang sesuai filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="transactions.links?.length > 3" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <strong class="text-slate-800">{{ transactions.from || 0 }}</strong> - <strong class="text-slate-800">{{ transactions.to || 0 }}</strong> dari <strong class="text-slate-800">{{ transactions.total }}</strong> transaksi
                    </p>
                    <div class="flex items-center gap-1">
                        <component
                            :is="link.url ? 'Link' : 'span'"
                            v-for="(link, i) in transactions.links"
                            :key="i"
                            :href="link.url"
                            v-html="link.label"
                            class="px-3 py-1 text-xs rounded-lg font-medium transition"
                            :class="link.active
                                ? 'bg-emerald-600 text-white font-bold'
                                : link.url
                                    ? 'text-slate-600 hover:bg-slate-100'
                                    : 'text-slate-300 cursor-not-allowed'"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Thermal Receipt Preview Modal -->
        <div v-if="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 my-8">
                <!-- Action Header (Hidden in Print) -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 print:hidden">
                    <h3 class="font-extrabold text-sm text-slate-900">Detail Nota Struk Kasir</h3>
                    <button @click="showReceiptModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Printable Receipt Body -->
                <div id="printable-receipt" class="font-mono text-xs text-slate-800 py-4 leading-relaxed">
                    <!-- Store Brand -->
                    <div class="text-center pb-3 border-b border-dashed border-slate-300">
                        <h4 class="font-black text-base tracking-wider text-slate-900 uppercase">MAS POS STORE</h4>
                        <p class="text-[11px] text-slate-500">Jl. Raya Pusat Bisnis No. 88</p>
                        <p class="text-[11px] text-slate-500">Telp: (021) 8899-7766</p>
                    </div>

                    <!-- Meta Information -->
                    <div class="py-2.5 border-b border-dashed border-slate-300 space-y-1 text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Nota:</span>
                            <span class="font-bold text-slate-900">{{ activeTransaction?.invoice_code || ('INV-' + activeTransaction?.id) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tanggal:</span>
                            <span>{{ formatDate(activeTransaction?.created_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kasir:</span>
                            <span>{{ activeTransaction?.user?.name || 'Kasir Umum' }}</span>
                        </div>
                    </div>

                    <!-- Purchased Items -->
                    <div class="py-3 border-b border-dashed border-slate-300 space-y-2">
                        <div
                            v-for="item in activeTransaction?.items"
                            :key="item.id"
                            class="text-[11px]"
                        >
                            <div class="font-bold text-slate-900">{{ item.product?.name || 'Produk' }}</div>
                            <div class="flex justify-between text-slate-500">
                                <span>{{ item.quantity }} x {{ formatCurrency(item.price) }}</span>
                                <span class="font-bold text-slate-900">{{ formatCurrency(item.subtotal) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Calculations -->
                    <div class="py-2.5 border-b border-dashed border-slate-300 space-y-1.5 text-[11px]">
                        <div class="flex justify-between font-black text-xs text-slate-900 pt-1">
                            <span>TOTAL:</span>
                            <span>{{ formatCurrency(activeTransaction?.total) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Metode Bayar:</span>
                            <span class="uppercase font-bold text-slate-800">{{ activeTransaction?.payment_method || 'CASH' }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Uang Diterima:</span>
                            <span>{{ formatCurrency(activeTransaction?.pay_amount || activeTransaction?.total) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Kembalian:</span>
                            <span>{{ formatCurrency(activeTransaction?.change_amount || 0) }}</span>
                        </div>
                    </div>

                    <!-- Footer Message -->
                    <div class="pt-4 text-center text-[10px] text-slate-400 space-y-1">
                        <p class="font-bold text-slate-600 uppercase tracking-widest">TERIMA KASIH</p>
                        <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
                    </div>
                </div>

                <!-- Print & Close Controls (Hidden in Print) -->
                <div class="pt-4 mt-2 border-t border-slate-100 flex items-center justify-end gap-2 print:hidden">
                    <button
                        type="button"
                        @click="showReceiptModal = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100"
                    >
                        Tutup
                    </button>
                    <button
                        type="button"
                        @click="printReceipt"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Struk</span>
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printable-receipt, #printable-receipt * {
        visibility: visible;
    }
    #printable-receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm;
        margin: 0;
        padding: 5mm;
    }
}
</style>

