<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    transaction: Object,
});

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <Head title="Transaksi Berhasil - Mas POS" />

    <div class="min-h-screen bg-slate-100 flex flex-col justify-center items-center p-4 sm:p-6 print:bg-white print:p-0">
        <div class="max-w-md w-full space-y-4">
            <!-- Success Top Banner (Hidden in print) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 text-center print:hidden">
                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-xl font-extrabold text-slate-900 mt-3">Pembayaran Berhasil!</h1>
                <p class="text-xs text-slate-500 mt-1">Transaksi telah tersimpan dan stok produk otomatis dipotong.</p>
                <div class="mt-4 flex items-center justify-center gap-3">
                    <Link
                        :href="route('pos.index')"
                        class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md transition"
                    >
                        &larr; Transaksi Baru
                    </Link>
                    <button
                        @click="printReceipt"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Struk (Print)</span>
                    </button>
                </div>
            </div>

            <!-- Thermal Struk Receipt Card -->
            <div id="thermal-receipt-card" class="bg-white rounded-2xl p-6 shadow-md border border-slate-200/80 font-mono text-xs text-slate-800 print:shadow-none print:border-none print:p-0 print:m-0">
                <!-- Store Info -->
                <div class="text-center pb-4 border-b border-dashed border-slate-300">
                    <h2 class="font-black text-base tracking-widest text-slate-900 uppercase">MAS POS RETAIL</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Jl. Raya Pusat Bisnis No. 88</p>
                    <p class="text-[11px] text-slate-500">Telp: (021) 8899-7766</p>
                </div>

                <!-- Meta Info -->
                <div class="py-3 border-b border-dashed border-slate-300 space-y-1 text-[11px]">
                    <div class="flex justify-between">
                        <span class="text-slate-500">No. Nota:</span>
                        <span class="font-bold text-slate-900">{{ transaction.invoice_code }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tanggal:</span>
                        <span>{{ transaction.date }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Kasir:</span>
                        <span>{{ transaction.cashier_name }}</span>
                    </div>
                    <div v-if="transaction.notes" class="flex justify-between text-indigo-600 font-medium">
                        <span>Catatan:</span>
                        <span>{{ transaction.notes }}</span>
                    </div>
                </div>

                <!-- Itemized List -->
                <div class="py-3 border-b border-dashed border-slate-300 space-y-2.5">
                    <div
                        v-for="item in transaction.items"
                        :key="item.id"
                        class="text-[11px]"
                    >
                        <div class="font-bold text-slate-900">{{ item.name }}</div>
                        <div class="flex justify-between text-slate-500 mt-0.5">
                            <span>{{ item.quantity }} x {{ item.price_formatted }}</span>
                            <span class="font-bold text-slate-900">{{ item.subtotal_formatted }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="py-3 border-b border-dashed border-slate-300 space-y-1.5 text-[11px]">
                    <div class="flex justify-between font-black text-sm text-slate-900 pt-1">
                        <span>TOTAL:</span>
                        <span>{{ transaction.total_formatted }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Metode Bayar:</span>
                        <span class="uppercase font-bold text-slate-800">{{ transaction.payment_method }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Uang Diterima:</span>
                        <span>{{ transaction.pay_amount_formatted }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Kembalian:</span>
                        <span class="font-bold text-slate-900">{{ transaction.change_amount_formatted }}</span>
                    </div>
                </div>

                <!-- Receipt Footer -->
                <div class="pt-4 text-center text-[10px] text-slate-400 space-y-1">
                    <p class="font-bold text-slate-600 uppercase tracking-widest">TERIMA KASIH ATAS KUNJUNGAN ANDA</p>
                    <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body {
        background: white !important;
    }
    body * {
        visibility: hidden;
    }
    #thermal-receipt-card, #thermal-receipt-card * {
        visibility: visible;
    }
    #thermal-receipt-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm;
        margin: 0;
        padding: 5mm;
    }
}
</style>
