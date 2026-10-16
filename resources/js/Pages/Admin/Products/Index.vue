<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
    summary: Object,
});

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category_id || '');
const selectedStockFilter = ref(props.filters.stock_filter || '');

// Search & Filter with debounce
let searchTimer = null;
watch([search, selectedCategory, selectedStockFilter], () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            route('admin.products.index'),
            {
                search: search.value,
                category_id: selectedCategory.value,
                stock_filter: selectedStockFilter.value,
            },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300);
});

// Modal states
const showAddModal = ref(false);
const showEditModal = ref(false);
const showRestockModal = ref(false);
const showDeleteModal = ref(false);

const productToEdit = ref(null);
const productToRestock = ref(null);
const productToDelete = ref(null);

const addForm = useForm({
    name: '',
    sku: '',
    category_id: '',
    price: '',
    cost_price: '',
    stock: 0,
    description: '',
    image: null,
});

const editForm = useForm({
    _method: 'PUT',
    name: '',
    sku: '',
    category_id: '',
    price: '',
    cost_price: '',
    stock: 0,
    description: '',
    image: null,
});

const restockForm = useForm({
    added_stock: 10,
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const generateSku = () => {
    const random = Math.floor(1000 + Math.random() * 9000);
    addForm.sku = `PRD-${random}`;
};

const handleImageUpload = (event, form) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
    }
};

const openAddModal = () => {
    addForm.reset();
    addForm.clearErrors();
    generateSku();
    showAddModal.value = true;
};

const submitAdd = () => {
    addForm.post(route('admin.products.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (product) => {
    productToEdit.value = product;
    editForm.name = product.name;
    editForm.sku = product.sku || '';
    editForm.category_id = product.category_id || '';
    editForm.price = product.price;
    editForm.cost_price = product.cost_price || 0;
    editForm.stock = product.stock;
    editForm.description = product.description || '';
    editForm.image = null;
    editForm.clearErrors();
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!productToEdit.value) return;
    editForm.post(route('admin.products.update', productToEdit.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            productToEdit.value = null;
        },
    });
};

const openRestockModal = (product) => {
    productToRestock.value = product;
    restockForm.added_stock = 10;
    showRestockModal.value = true;
};

const submitRestock = () => {
    if (!productToRestock.value) return;
    restockForm.post(route('admin.products.restock', productToRestock.value.id), {
        onSuccess: () => {
            showRestockModal.value = false;
            productToRestock.value = null;
        },
    });
};

const openDeleteModal = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!productToDelete.value) return;
    router.delete(route('admin.products.destroy', productToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            productToDelete.value = null;
        },
    });
};

const calculateMargin = (price, cost) => {
    const p = parseFloat(price) || 0;
    const c = parseFloat(cost) || 0;
    if (p <= 0 || c <= 0) return 0;
    return Math.round(((p - c) / p) * 100);
};
</script>

<template>
    <Head title="Manajemen Produk & Stok - Mas POS" />

    <AdminLayout title="Katalog Produk & Manajemen Stok">
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Katalog Produk & Inventori</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data barang, harga beli/jual, margin laba, dan restock fisik.</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Jenis Produk</p>
                        <h4 class="text-2xl font-black text-slate-900 mt-1">{{ summary.total_products }}</h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Fisik Unit Stok</p>
                        <h4 class="text-2xl font-black text-blue-600 mt-1">{{ summary.total_stock }} <span class="text-xs text-slate-400 font-semibold">pcs</span></h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Stok Kritis / Habis</p>
                        <h4 class="text-2xl font-black text-rose-600 mt-1">{{ summary.low_stock_count }} <span class="text-xs text-slate-400 font-semibold">item</span></h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Filter Bar & Actions -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full sm:w-auto">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama atau SKU..."
                            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Category Filter -->
                    <select
                        v-model="selectedCategory"
                        class="w-full py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >
                        <option value="">Semua Kategori</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>

                    <!-- Stock Filter -->
                    <select
                        v-model="selectedStockFilter"
                        class="w-full py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >
                        <option value="">Semua Kondisi Stok</option>
                        <option value="low">Stok Menipis (1 - 5 unit)</option>
                        <option value="out">Stok Habis (0 unit)</option>
                    </select>
                </div>

                <!-- Add Product Button -->
                <button
                    @click="openAddModal"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/20 transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Produk Baru</span>
                </button>
            </div>

            <!-- Product Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-6 py-3.5">Produk</th>
                                <th class="px-6 py-3.5">SKU</th>
                                <th class="px-6 py-3.5">Kategori</th>
                                <th class="px-6 py-3.5">Harga Jual</th>
                                <th class="px-6 py-3.5">Harga Beli (HPP)</th>
                                <th class="px-6 py-3.5">Margin</th>
                                <th class="px-6 py-3.5">Sisa Stok</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="product in products.data"
                                :key="product.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="px-6 py-4 flex items-center gap-3">
                                    <img
                                        :src="product.image_url"
                                        :alt="product.name"
                                        class="w-10 h-10 rounded-xl object-cover bg-slate-100 border border-slate-200 shrink-0"
                                        @error="(e) => e.target.src = '/PepperoniChesse.png'"
                                    />
                                    <div>
                                        <p class="font-bold text-slate-900">{{ product.name }}</p>
                                        <p v-if="product.description" class="text-[11px] text-slate-400 line-clamp-1 max-w-xs">{{ product.description }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-slate-700">
                                    {{ product.sku || '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ product.category?.name || 'Umum' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    {{ formatCurrency(product.price) }}
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-500">
                                    {{ formatCurrency(product.cost_price || 0) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700">
                                        +{{ calculateMargin(product.price, product.cost_price) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider"
                                            :class="{
                                                'bg-rose-100 text-rose-800': product.stock <= 0,
                                                'bg-amber-100 text-amber-800': product.stock > 0 && product.stock <= 5,
                                                'bg-blue-100 text-blue-800': product.stock > 5
                                            }"
                                        >
                                            {{ product.stock <= 0 ? 'Habis' : product.stock + ' Unit' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                                    <!-- Restock Button -->
                                    <button
                                        @click="openRestockModal(product)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 font-bold transition text-[11px]"
                                        title="Tambah Stok"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        <span>Restock</span>
                                    </button>

                                    <!-- Edit Button -->
                                    <button
                                        @click="openEditModal(product)"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition"
                                        title="Edit Produk"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button
                                        @click="openDeleteModal(product)"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition"
                                        title="Hapus Produk"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!products.data.length">
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Tidak ada produk ditemukan sesuai filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="products.links?.length > 3" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <strong class="text-slate-800">{{ products.from || 0 }}</strong> - <strong class="text-slate-800">{{ products.to || 0 }}</strong> dari <strong class="text-slate-800">{{ products.total }}</strong> produk
                    </p>
                    <div class="flex items-center gap-1">
                        <component
                            :is="link.url ? 'Link' : 'span'"
                            v-for="(link, i) in products.links"
                            :key="i"
                            :href="link.url"
                            v-html="link.label"
                            class="px-3 py-1 text-xs rounded-lg font-medium transition"
                            :class="link.active
                                ? 'bg-blue-600 text-white font-bold'
                                : link.url
                                    ? 'text-slate-600 hover:bg-slate-100'
                                    : 'text-slate-300 cursor-not-allowed'"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 my-8">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="font-bold text-base text-slate-900">Tambah Produk Baru</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitAdd" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Produk</label>
                        <input
                            v-model="addForm.name"
                            type="text"
                            required
                            placeholder="Contoh: Kopi Susu Aren"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-bold text-slate-700">SKU / Kode Barcode</label>
                                <button type="button" @click="generateSku" class="text-[10px] text-blue-600 font-bold hover:underline">Acak</button>
                            </div>
                            <input
                                v-model="addForm.sku"
                                type="text"
                                placeholder="PRD-1234"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono"
                            />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kategori</label>
                            <select
                                v-model="addForm.category_id"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            >
                                <option value="">Pilih Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Harga Jual (Rp)</label>
                            <input
                                v-model="addForm.price"
                                type="number"
                                required
                                min="0"
                                placeholder="18000"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-bold"
                            />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Harga Beli / HPP</label>
                            <input
                                v-model="addForm.cost_price"
                                type="number"
                                min="0"
                                placeholder="12000"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Stok Awal</label>
                            <input
                                v-model="addForm.stock"
                                type="number"
                                required
                                min="0"
                                placeholder="50"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-bold"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Foto Produk (Opsional)</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="(e) => handleImageUpload(e, addForm)"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Deskripsi Tambahan</label>
                        <textarea
                            v-model="addForm.description"
                            rows="2"
                            placeholder="Catatan porsi, bahan, atau varian..."
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        ></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button
                            type="button"
                            @click="showAddModal = false"
                            class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="addForm.processing"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-md shadow-blue-600/20 disabled:opacity-50"
                        >
                            {{ addForm.processing ? 'Menyimpan...' : 'Simpan Produk' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 my-8">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="font-bold text-base text-slate-900">Edit Produk</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Produk</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            required
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">SKU / Kode Barcode</label>
                            <input
                                v-model="editForm.sku"
                                type="text"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono"
                            />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kategori</label>
                            <select
                                v-model="editForm.category_id"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            >
                                <option value="">Pilih Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Harga Jual (Rp)</label>
                            <input
                                v-model="editForm.price"
                                type="number"
                                required
                                min="0"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 font-bold"
                            />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Harga Beli / HPP</label>
                            <input
                                v-model="editForm.cost_price"
                                type="number"
                                min="0"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200"
                            />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Stok Fisik</label>
                            <input
                                v-model="editForm.stock"
                                type="number"
                                required
                                min="0"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 font-bold"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ganti Foto (Opsional)</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="(e) => handleImageUpload(e, editForm)"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700"
                        />
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-md shadow-blue-600/20 disabled:opacity-50"
                        >
                            {{ editForm.processing ? 'Menyimpan...' : 'Perbarui Produk' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Restock Modal -->
        <div v-if="showRestockModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">Restock Barang</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ productToRestock?.name }}</p>
                    </div>
                    <button @click="showRestockModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitRestock" class="mt-4 space-y-4 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Stok Saat Ini:</span>
                        <span class="font-bold text-slate-900 text-sm">{{ productToRestock?.stock }} Unit</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jumlah Unit yang Ditambahkan</label>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="restockForm.added_stock = Math.max(1, restockForm.added_stock - 5)"
                                class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200"
                            >
                                -5
                            </button>
                            <input
                                v-model="restockForm.added_stock"
                                type="number"
                                required
                                min="1"
                                class="flex-1 py-2 px-3 text-center text-sm font-bold rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            />
                            <button
                                type="button"
                                @click="restockForm.added_stock += 10"
                                class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200"
                            >
                                +10
                            </button>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button
                            type="button"
                            @click="showRestockModal = false"
                            class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="restockForm.processing"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-md shadow-blue-600/20"
                        >
                            {{ restockForm.processing ? 'Menyimpan...' : 'Tambahkan Stok' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Product Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="font-bold text-base text-slate-900 mt-3">Hapus Produk?</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Anda yakin ingin menghapus <strong>{{ productToDelete?.name }}</strong>? Data produk akan dihapus dari katalog kasir.
                </p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        @click="confirmDelete"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/20"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
