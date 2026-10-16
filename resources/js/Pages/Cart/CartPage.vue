<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
    cartItems: { type: Array, default: () => [] },
    totalTagihan: { type: String, default: 'Rp 0' },
    totalTagihanRaw: { type: [Number, String], default: 0 },
    cartItemsCount: { type: Number, default: 0 },
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const updateQuantity = (item, delta) => {
    const newQty = item.quantity + delta;
    if (newQty < 1) {
        removeItem(item.id);
    } else {
        router.patch(
            route('cart.items.update', item.id),
            { quantity: newQty },
            { preserveScroll: true }
        );
    }
};

const removeItem = (id) => {
    if (confirm('Hapus produk ini dari keranjang?')) {
        router.delete(route('cart.items.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Payment Modal State
const showPaymentModal = ref(false);
const paymentMethod = ref('cash');
const cashAmount = ref('');
const notes = ref('');
const checkoutProcessing = ref(false);

const rawTotal = computed(() => parseFloat(props.totalTagihanRaw) || 0);
const parsedCash = computed(() => parseFloat(cashAmount.value) || 0);

const changeAmount = computed(() => {
    if (paymentMethod.value !== 'cash') return 0;
    return Math.max(0, parsedCash.value - rawTotal.value);
});

const isCashEnough = computed(() => {
    if (paymentMethod.value !== 'cash') return true;
    return parsedCash.value >= rawTotal.value;
});

const openPayment = () => {
    if (!props.cartItems.length) return;
    cashAmount.value = rawTotal.value.toString();
    paymentMethod.value = 'cash';
    notes.value = '';
    showPaymentModal.value = true;
};

const setQuickCash = (amount) => {
    cashAmount.value = amount.toString();
};

const submitCheckout = () => {
    if (!isCashEnough.value && paymentMethod.value === 'cash') return;

    checkoutProcessing.value = true;
    router.post(
        route('cart.checkout'),
        {
            payment_method: paymentMethod.value,
            pay_amount: paymentMethod.value === 'cash' ? parsedCash.value : rawTotal.value,
            notes: notes.value,
        },
        {
            onSuccess: () => {
                showPaymentModal.value = false;
            },
            onFinish: () => {
                checkoutProcessing.value = false;
            },
        }
    );
};
</script>

<template>
    <Head title="Keranjang Belanja - Mas POS" />

    <div class="min-h-screen bg-slate-100 flex flex-col font-sans">
        <ToastNotification />

        <!-- Header -->
        <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between">
            <Link :href="route('pos.index')" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white font-black flex items-center justify-center shadow-md shadow-blue-600/20">
                    P
                </div>
                <span class="font-extrabold text-base text-slate-900">Mas POS &bull; Keranjang</span>
            </Link>
            <Link
                :href="route('pos.index')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition"
            >
                &larr; Kembali ke Kasir
            </Link>
        </header>

        <!-- Body -->
        <main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:p-8 space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Daftar Item Pesanan</h2>
                        <p class="text-xs text-slate-400 mt-0.5">{{ cartItemsCount }} item dalam keranjang</p>
                    </div>
                    <span class="text-xl font-black text-blue-600">{{ totalTagihan }}</span>
                </div>

                <div class="mt-4 divide-y divide-slate-100">
                    <div
                        v-for="item in cartItems"
                        :key="item.id"
                        class="py-4 flex items-center justify-between gap-4"
                    >
                        <div class="min-w-0 flex-1">
                            <h4 class="font-bold text-sm text-slate-900">{{ item.product?.name }}</h4>
                            <p class="text-xs text-slate-400">{{ formatCurrency(item.price) }} / unit</p>
                        </div>

                        <!-- Stepper -->
                        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl p-1">
                            <button
                                @click="updateQuantity(item, -1)"
                                class="w-7 h-7 rounded-lg bg-white text-slate-700 hover:bg-slate-200 font-bold flex items-center justify-center text-xs shadow-sm"
                            >
                                -
                            </button>
                            <span class="w-8 text-center font-black text-xs text-slate-900">{{ item.quantity }}</span>
                            <button
                                @click="updateQuantity(item, 1)"
                                :disabled="item.quantity >= item.product?.stock"
                                class="w-7 h-7 rounded-lg bg-white text-slate-700 hover:bg-slate-200 font-bold flex items-center justify-center text-xs shadow-sm disabled:opacity-30"
                            >
                                +
                            </button>
                        </div>

                        <div class="w-28 text-right font-black text-sm text-slate-900">
                            {{ formatCurrency(item.subtotal || (item.price * item.quantity)) }}
                        </div>

                        <button
                            @click="removeItem(item.id)"
                            class="p-2 text-slate-400 hover:text-rose-600 transition"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                    <div v-if="!cartItems.length" class="py-16 text-center text-slate-400 text-xs">
                        Keranjang belanja Anda masih kosong.
                    </div>
                </div>

                <!-- Footer Checkout -->
                <div v-if="cartItems.length" class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400">Total Tagihan:</p>
                        <p class="text-2xl font-black text-slate-900">{{ totalTagihan }}</p>
                    </div>
                    <button
                        @click="openPayment"
                        class="px-8 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm shadow-lg shadow-blue-600/30 transition"
                    >
                        Lanjut ke Pembayaran &rarr;
                    </button>
                </div>
            </div>
        </main>

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 my-8">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="font-extrabold text-base text-slate-900">Pembayaran Kasir</h3>
                    <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="my-4 p-4 rounded-2xl bg-blue-50 border border-blue-100 text-center">
                    <p class="text-xs font-bold text-blue-800 uppercase tracking-wider">Total yang Harus Dibayar</p>
                    <h2 class="text-3xl font-black text-blue-700 mt-1">{{ totalTagihan }}</h2>
                </div>

                <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100 rounded-2xl mb-4 text-xs font-bold">
                    <button
                        type="button"
                        @click="paymentMethod = 'cash'"
                        class="py-2.5 rounded-xl transition inline-flex items-center justify-center gap-1.5"
                        :class="paymentMethod === 'cash' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Tunai</span>
                    </button>
                    <button
                        type="button"
                        @click="paymentMethod = 'qris'"
                        class="py-2.5 rounded-xl transition inline-flex items-center justify-center gap-1.5"
                        :class="paymentMethod === 'qris' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'"
                    >
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>QRIS</span>
                    </button>
                    <button
                        type="button"
                        @click="paymentMethod = 'transfer'"
                        class="py-2.5 rounded-xl transition inline-flex items-center justify-center gap-1.5"
                        :class="paymentMethod === 'transfer' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'"
                    >
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span>Transfer</span>
                    </button>
                </div>

                <div v-if="paymentMethod === 'cash'" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nominal Uang Diterima (Rp)</label>
                        <input
                            v-model="cashAmount"
                            type="number"
                            min="0"
                            class="w-full py-2.5 px-3 text-base font-black rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="setQuickCash(rawTotal)" class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700">Uang Pas</button>
                        <button type="button" @click="setQuickCash(50000)" class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700">50.000</button>
                        <button type="button" @click="setQuickCash(100000)" class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700">100.000</button>
                    </div>

                    <div
                        class="p-3.5 rounded-2xl flex items-center justify-between font-bold"
                        :class="isCashEnough ? 'bg-blue-50 text-blue-900 border border-blue-200' : 'bg-rose-50 text-rose-900 border border-rose-200'"
                    >
                        <span>{{ isCashEnough ? 'Kembalian:' : 'Uang Kurang:' }}</span>
                        <span class="text-base font-black">
                            {{ formatCurrency(isCashEnough ? changeAmount : (rawTotal - parsedCash)) }}
                        </span>
                    </div>
                </div>

                <div v-else-if="paymentMethod === 'qris'" class="p-4 bg-slate-50 rounded-2xl text-center space-y-2 text-xs border border-slate-200">
                    <p class="font-bold text-slate-800">Scan QRIS Mas POS</p>
                    <div class="w-32 h-32 bg-white p-2 rounded-2xl mx-auto shadow-sm border border-slate-200 flex items-center justify-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=MASPOS-PAYMENT-MOCK" alt="QRIS" class="w-full h-full object-contain" />
                    </div>
                </div>

                <div v-else class="p-4 bg-slate-50 rounded-2xl text-xs border border-slate-200">
                    <p class="font-bold text-slate-800">BCA: 887-092-1234 (MAS POS RETAIL)</p>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        @click="showPaymentModal = false"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitCheckout"
                        :disabled="checkoutProcessing || (!isCashEnough && paymentMethod === 'cash')"
                        class="px-6 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/25 disabled:opacity-50"
                    >
                        {{ checkoutProcessing ? 'Memproses...' : 'Selesaikan Transaksi &rarr;' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
