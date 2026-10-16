<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
    products: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    cartItems: { type: Array, default: () => [] },
    cart_total: { type: [Number, String], default: 0 },
    cart_total_formatted: { type: String, default: 'Rp 0' },
    cart_count: { type: Number, default: 0 },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const authUser = page.props.auth?.user;

// Real-time Clock
const currentTime = ref('');
const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleDateString('id-ID', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};

let clockInterval = null;
onMounted(() => {
    updateClock();
    clockInterval = setInterval(updateClock, 1000);
});
onUnmounted(() => {
    if (clockInterval) clearInterval(clockInterval);
});

// Search and Category Filters
const searchInput = ref(null);
const searchTerm = ref('');
const selectedCategoryId = ref(null);

const filteredProducts = computed(() => {
    const term = searchTerm.value.trim().toLowerCase();
    return props.products.filter((p) => {
        const inCategory =
            !selectedCategoryId.value ||
            p.category_id === selectedCategoryId.value ||
            p.category?.id === selectedCategoryId.value;
        const inSearch =
            !term ||
            p.name?.toLowerCase().includes(term) ||
            p.sku?.toLowerCase().includes(term);
        return inCategory && inSearch;
    });
});

// Barcode Scan / Exact SKU Match on Enter
const handleSearchEnter = () => {
    const term = searchTerm.value.trim().toLowerCase();
    if (!term) return;

    // Look for exact SKU match or exact name match
    const match = props.products.find(
        (p) => p.sku?.toLowerCase() === term || p.name?.toLowerCase() === term
    );

    if (match && match.stock > 0) {
        addToCart(match);
        searchTerm.value = '';
    }
};

// Cart Actions
const addingProductId = ref(null);

const addToCart = (product) => {
    if (product.stock <= 0) return;
    addingProductId.value = product.id;

    router.post(
        route('cart.store'),
        { product_id: product.id, qty: 1 },
        {
            preserveScroll: true,
            onFinish: () => {
                addingProductId.value = null;
            },
        }
    );
};

const updateItemQty = (item, delta) => {
    const newQty = item.quantity + delta;
    if (newQty <= 0) {
        removeItem(item);
    } else {
        router.patch(
            route('cart.items.update', item.id),
            { quantity: newQty },
            { preserveScroll: true }
        );
    }
};

const removeItem = (item) => {
    router.delete(route('cart.items.destroy', item.id), {
        preserveScroll: true,
    });
};

const clearCart = () => {
    if (!props.cartItems.length) return;
    if (confirm('Yakin ingin mengosongkan seluruh keranjang belanja?')) {
        router.post(route('cart.clear'), {}, { preserveScroll: true });
    }
};

// Payment Modal State
const showPaymentModal = ref(false);
const paymentMethod = ref('cash'); // 'cash' | 'qris' | 'transfer'
const cashAmount = ref('');
const notes = ref('');
const checkoutProcessing = ref(false);

const rawTotal = computed(() => parseFloat(props.cart_total) || 0);

const parsedCash = computed(() => {
    return parseFloat(cashAmount.value) || 0;
});

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

// Keyboard Shortcuts (F2 search, F8 pay, Escape close modal)
const handleKeyDown = (e) => {
    if (e.key === 'F2') {
        e.preventDefault();
        searchInput.value?.focus();
    } else if (e.key === 'F8') {
        e.preventDefault();
        if (!showPaymentModal.value && props.cartItems.length > 0) {
            openPayment();
        }
    } else if (e.key === 'Escape') {
        if (showPaymentModal.value) {
            showPaymentModal.value = false;
        }
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
    nextTick(() => {
        searchInput.value?.focus();
    });
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <Head title="Mesin Kasir POS - Mas POS" />

    <div class="h-screen flex flex-col bg-slate-100 overflow-hidden font-sans">
        <ToastNotification />

        <!-- Top POS Navigation Bar -->
        <header class="h-14 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between shrink-0 z-20">
            <!-- Left: Store Branding -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center font-black text-lg text-white shadow-md shadow-emerald-500/20">
                    P
                </div>
                <div>
                    <h1 class="font-extrabold text-base tracking-tight text-slate-900 leading-none">Mas POS</h1>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Terminal Kasir</span>
                </div>
            </div>

            <!-- Middle: Live Date & Clock -->
            <div class="hidden md:flex items-center gap-2 text-xs font-semibold text-slate-600 bg-slate-100 px-3.5 py-1.5 rounded-full border border-slate-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ currentTime }}</span>
            </div>

            <!-- Right: Role Actions & User Profile -->
            <div class="flex items-center gap-3">
                <!-- If Admin, show link to Backoffice -->
                <Link
                    v-if="authUser?.role === 'admin'"
                    :href="route('admin.dashboard')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition"
                    title="Buka Dashboard Admin"
                >
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Dashboard Admin</span>
                </Link>

                <!-- Cashier Profile Pill -->
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1 rounded-full">
                    <div class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">
                        {{ authUser?.name ? authUser.name.charAt(0).toUpperCase() : 'K' }}
                    </div>
                    <span class="text-xs font-bold text-slate-800">{{ authUser?.name || 'Kasir' }}</span>
                </div>

                <!-- Logout Button -->
                <button
                    @click="logout"
                    class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                    title="Keluar / Ganti Akun"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- POS Workspace (Split Screen) -->
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
            <!-- Left Side: Product Catalog & Search (65% width) -->
            <section class="flex-1 flex flex-col bg-slate-50 border-r border-slate-200 overflow-hidden">
                <!-- Search & Filter Controls -->
                <div class="p-4 bg-white border-b border-slate-200/80 space-y-3 shrink-0">
                    <!-- Search Input with Barcode support -->
                    <div class="relative">
                        <input
                            ref="searchInput"
                            v-model="searchTerm"
                            type="text"
                            placeholder="Cari produk atau scan barcode SKU... (Tekan F2)"
                            @keydown.enter="handleSearchEnter"
                            class="w-full pl-10 pr-24 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition"
                        />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span class="absolute right-3 top-2.5 px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] font-mono font-bold">
                            F2 Search
                        </span>
                    </div>

                    <!-- Category Pills Horizontal Scroll -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar text-xs">
                        <button
                            @click="selectedCategoryId = null"
                            class="px-3.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition text-xs shadow-sm"
                            :class="selectedCategoryId === null
                                ? 'bg-slate-900 text-white shadow-slate-900/20'
                                : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                        >
                            Semua Menu ({{ products.length }})
                        </button>
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            @click="selectedCategoryId = cat.id"
                            class="px-3.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition text-xs shadow-sm"
                            :class="selectedCategoryId === cat.id
                                ? 'bg-emerald-600 text-white shadow-emerald-600/20'
                                : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                        >
                            {{ cat.name }} ({{ cat.products_count || 0 }})
                        </button>
                    </div>
                </div>

                <!-- Product Cards Grid (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                        <div
                            v-for="product in filteredProducts"
                            :key="product.id"
                            @click="addToCart(product)"
                            class="bg-white rounded-2xl p-3 border border-slate-200/80 shadow-sm flex flex-col justify-between transition-all duration-150 select-none group"
                            :class="product.stock > 0
                                ? 'hover:shadow-md hover:border-emerald-500/50 cursor-pointer active:scale-[0.98]'
                                : 'opacity-60 cursor-not-allowed bg-slate-50'"
                        >
                            <!-- Image & Badge Container -->
                            <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-slate-100 mb-2.5">
                                <img
                                    :src="product.image_url"
                                    :alt="product.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                    @error="(e) => e.target.src = 'https://placehold.co/200x200?text=Produk'"
                                />
                                <!-- Stock Badge Overlay -->
                                <div class="absolute top-2 right-2">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm"
                                        :class="{
                                            'bg-rose-600 text-white': product.stock <= 0,
                                            'bg-amber-500 text-white': product.stock > 0 && product.stock <= 5,
                                            'bg-emerald-600 text-white': product.stock > 5
                                        }"
                                    >
                                        {{ product.stock <= 0 ? 'Habis' : 'Stok: ' + product.stock }}
                                    </span>
                                </div>
                                <div v-if="product.sku" class="absolute bottom-2 left-2">
                                    <span class="px-1.5 py-0.5 rounded bg-black/60 text-white font-mono text-[9px] font-bold">
                                        {{ product.sku }}
                                    </span>
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                    {{ product.category?.name || 'Umum' }}
                                </span>
                                <h3 class="font-bold text-slate-900 text-xs line-clamp-2 leading-snug group-hover:text-emerald-700 transition">
                                    {{ product.name }}
                                </h3>
                            </div>

                            <!-- Price and Add Button -->
                            <div class="flex items-center justify-between mt-3 pt-2 border-t border-slate-100">
                                <span class="font-extrabold text-xs text-slate-900">
                                    {{ formatCurrency(product.price) }}
                                </span>
                                <button
                                    type="button"
                                    :disabled="product.stock <= 0"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs transition"
                                    :class="product.stock > 0
                                        ? 'bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white'
                                        : 'bg-slate-100 text-slate-300'"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!filteredProducts.length" class="h-64 flex flex-col items-center justify-center text-center text-slate-400">
                        <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-bold text-sm text-slate-600">Produk Tidak Ditemukan</p>
                        <p class="text-xs text-slate-400 mt-1">Coba cari dengan kata kunci lain atau pilih kategori yang berbeda.</p>
                    </div>
                </div>
            </section>

            <!-- Right Side: Live Active Cart & Checkout (35% width) -->
            <aside class="w-full lg:w-[380px] xl:w-[420px] bg-white flex flex-col shrink-0 border-l border-slate-200 z-10 shadow-xl lg:shadow-none">
                <!-- Cart Header -->
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-black text-xs">
                            🛒
                        </div>
                        <div>
                            <h2 class="font-bold text-sm text-slate-900 leading-tight">Keranjang Belanja</h2>
                            <p class="text-[10px] text-slate-400">{{ cart_count }} item dipilih</p>
                        </div>
                    </div>
                    <button
                        v-if="cartItems.length > 0"
                        @click="clearCart"
                        class="text-[11px] font-bold text-rose-500 hover:text-rose-700 hover:underline transition"
                    >
                        Kosongkan
                    </button>
                </div>

                <!-- Cart Items List (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div
                        v-for="item in cartItems"
                        :key="item.id"
                        class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3 group"
                    >
                        <div class="min-w-0 flex-1">
                            <h4 class="font-bold text-xs text-slate-900 truncate">{{ item.product?.name }}</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ formatCurrency(item.price) }} / unit</p>
                            <span class="text-xs font-black text-emerald-700 block mt-1">
                                {{ formatCurrency(item.subtotal) }}
                            </span>
                        </div>

                        <!-- Quantity Stepper -->
                        <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-xl p-1 shadow-sm shrink-0">
                            <button
                                @click="updateItemQty(item, -1)"
                                class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold flex items-center justify-center text-xs transition"
                            >
                                -
                            </button>
                            <span class="w-8 text-center font-black text-xs text-slate-900">{{ item.quantity }}</span>
                            <button
                                @click="updateItemQty(item, 1)"
                                :disabled="item.quantity >= item.product?.stock"
                                class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold flex items-center justify-center text-xs transition disabled:opacity-30 disabled:cursor-not-allowed"
                            >
                                +
                            </button>
                        </div>

                        <!-- Remove Button -->
                        <button
                            @click="removeItem(item)"
                            class="text-slate-300 hover:text-rose-500 transition p-1"
                            title="Hapus Item"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                    <!-- Empty Cart State -->
                    <div v-if="!cartItems.length" class="h-64 flex flex-col items-center justify-center text-center text-slate-400">
                        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-300 flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <p class="font-bold text-xs text-slate-700">Keranjang Masih Kosong</p>
                        <p class="text-[11px] text-slate-400 mt-1 max-w-[200px]">Klik item produk di sebelah kiri untuk menambahkan ke pesanan.</p>
                    </div>
                </div>

                <!-- Cart Calculation & Checkout Footer -->
                <div class="p-4 border-t border-slate-200 bg-slate-50 space-y-3">
                    <div class="space-y-1.5 text-xs text-slate-500">
                        <div class="flex justify-between">
                            <span>Subtotal Item:</span>
                            <span class="font-bold text-slate-800">{{ cart_total_formatted }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pajak & Layanan:</span>
                            <span class="font-semibold text-emerald-600">Rp 0 (Termasuk)</span>
                        </div>
                        <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                            <span>TOTAL TAGIHAN:</span>
                            <span class="text-emerald-600">{{ cart_total_formatted }}</span>
                        </div>
                    </div>

                    <!-- Big Checkout Button -->
                    <button
                        @click="openPayment"
                        :disabled="!cartItems.length"
                        class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm flex items-center justify-between shadow-lg shadow-emerald-600/30 transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Bayar Sekarang</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-lg bg-emerald-700/60 text-xs font-mono">
                            F8
                        </span>
                    </button>
                </div>
            </aside>
        </div>

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 my-8">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900">Pembayaran Kasir</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Pilih metode bayar & selesaikan nota</p>
                    </div>
                    <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Total Display -->
                <div class="my-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-center">
                    <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Total yang Harus Dibayar</p>
                    <h2 class="text-3xl font-black text-emerald-700 mt-1">{{ cart_total_formatted }}</h2>
                </div>

                <!-- Payment Methods Tabs -->
                <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100 rounded-2xl mb-4 text-xs font-bold">
                    <button
                        type="button"
                        @click="paymentMethod = 'cash'"
                        class="py-2.5 rounded-xl transition flex flex-col items-center gap-1"
                        :class="paymentMethod === 'cash'
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-500 hover:text-slate-900'"
                    >
                        <span>💵 Tunai</span>
                    </button>
                    <button
                        type="button"
                        @click="paymentMethod = 'qris'"
                        class="py-2.5 rounded-xl transition flex flex-col items-center gap-1"
                        :class="paymentMethod === 'qris'
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-500 hover:text-slate-900'"
                    >
                        <span>📱 QRIS</span>
                    </button>
                    <button
                        type="button"
                        @click="paymentMethod = 'transfer'"
                        class="py-2.5 rounded-xl transition flex flex-col items-center gap-1"
                        :class="paymentMethod === 'transfer'
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-500 hover:text-slate-900'"
                    >
                        <span>💳 Transfer</span>
                    </button>
                </div>

                <!-- Cash Section -->
                <div v-if="paymentMethod === 'cash'" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nominal Uang Diterima (Rp)</label>
                        <input
                            v-model="cashAmount"
                            type="number"
                            min="0"
                            placeholder="Contoh: 50000"
                            class="w-full py-2.5 px-3 text-base font-black rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                        />
                    </div>

                    <!-- Quick Cash Presets -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pecahan Cepat</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                type="button"
                                @click="setQuickCash(rawTotal)"
                                class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 transition"
                            >
                                Uang Pas
                            </button>
                            <button
                                type="button"
                                @click="setQuickCash(10000)"
                                class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 transition"
                            >
                                10.000
                            </button>
                            <button
                                type="button"
                                @click="setQuickCash(20000)"
                                class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 transition"
                            >
                                20.000
                            </button>
                            <button
                                type="button"
                                @click="setQuickCash(50000)"
                                class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 transition"
                            >
                                50.000
                            </button>
                            <button
                                type="button"
                                @click="setQuickCash(100000)"
                                class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 transition"
                            >
                                100.000
                            </button>
                            <button
                                type="button"
                                @click="setQuickCash(200000)"
                                class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 transition"
                            >
                                200.000
                            </button>
                        </div>
                    </div>

                    <!-- Change Calculation Display -->
                    <div
                        class="p-3.5 rounded-2xl flex items-center justify-between font-bold"
                        :class="isCashEnough
                            ? 'bg-emerald-50 text-emerald-900 border border-emerald-200'
                            : 'bg-rose-50 text-rose-900 border border-rose-200'"
                    >
                        <span>{{ isCashEnough ? 'Kembalian:' : 'Uang Kurang:' }}</span>
                        <span class="text-base font-black">
                            {{ formatCurrency(isCashEnough ? changeAmount : (rawTotal - parsedCash)) }}
                        </span>
                    </div>
                </div>

                <!-- QRIS Section -->
                <div v-else-if="paymentMethod === 'qris'" class="p-4 bg-slate-50 rounded-2xl text-center space-y-3 text-xs border border-slate-200">
                    <p class="font-bold text-slate-800">Scan QRIS Mas POS</p>
                    <div class="w-36 h-36 bg-white p-2 rounded-2xl mx-auto shadow-sm border border-slate-200 flex items-center justify-center">
                        <img
                            src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=MASPOS-PAYMENT-MOCK"
                            alt="QRIS Code"
                            class="w-full h-full object-contain"
                        />
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Tunjukkan QR kepada pelanggan untuk scan via BCA, GoPay, OVO, Dana, dll.
                    </p>
                </div>

                <!-- Transfer Section -->
                <div v-else class="p-4 bg-slate-50 rounded-2xl space-y-2 text-xs border border-slate-200">
                    <p class="font-bold text-slate-800 mb-1">Rekening Pembayaran Toko:</p>
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200 flex justify-between items-center">
                        <div>
                            <p class="font-black text-slate-900">BCA: 887-092-1234</p>
                            <p class="text-[10px] text-slate-400">a.n. MAS POS RETAIL</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700">Bank Transfer</span>
                    </div>
                </div>

                <!-- Optional Note (Meja / Pelanggan) -->
                <div class="mt-3">
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Catatan Tambahan (Opsional)</label>
                    <input
                        v-model="notes"
                        type="text"
                        placeholder="Contoh: Meja 05 / Pelanggan Budi"
                        class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                    />
                </div>

                <!-- Modal Actions -->
                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        @click="showPaymentModal = false"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitCheckout"
                        :disabled="checkoutProcessing || (!isCashEnough && paymentMethod === 'cash')"
                        class="px-6 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ checkoutProcessing ? 'Memproses...' : 'Selesaikan Transaksi &rarr;' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>