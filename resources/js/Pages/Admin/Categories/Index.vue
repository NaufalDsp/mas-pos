<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    categories: Object,
    filters: Object,
    totalCategories: Number,
});

const search = ref(props.filters.search || '');

// Search with debounce
let searchTimer = null;
watch(search, (newVal) => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            route('admin.categories.index'),
            { search: newVal },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300);
});

// Modals
const showAddModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const categoryToEdit = ref(null);
const categoryToDelete = ref(null);

const addForm = useForm({
    name: '',
});

const editForm = useForm({
    name: '',
});

const openAddModal = () => {
    addForm.reset();
    addForm.clearErrors();
    showAddModal.value = true;
};

const submitAdd = () => {
    addForm.post(route('admin.categories.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (cat) => {
    categoryToEdit.value = cat;
    editForm.name = cat.name;
    editForm.clearErrors();
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!categoryToEdit.value) return;
    editForm.put(route('admin.categories.update', categoryToEdit.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            categoryToEdit.value = null;
        },
    });
};

const openDeleteModal = (cat) => {
    categoryToDelete.value = cat;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!categoryToDelete.value) return;
    router.delete(route('admin.categories.destroy', categoryToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            categoryToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Manajemen Kategori - Mas POS" />

    <AdminLayout title="Kategori Menu & Produk">
        <template #header>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Kategori Produk</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelompokkan produk dan menu untuk memudahkan navigasi kasir.</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter Bar & Actions -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative w-full sm:w-72">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama kategori..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                    />
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <button
                    @click="openAddModal"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Kategori Baru</span>
                </button>
            </div>

            <!-- Category Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-6 py-3.5">Nama Kategori</th>
                                <th class="px-6 py-3.5">Total Produk Terkait</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="cat in categories.data"
                                :key="cat.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="px-6 py-4 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">
                                        🏷️
                                    </div>
                                    <span class="font-bold text-slate-900 text-sm">{{ cat.name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-800">
                                        {{ cat.products_count || 0 }} Produk
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button
                                        @click="openEditModal(cat)"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition"
                                        title="Edit Kategori"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="openDeleteModal(cat)"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition"
                                        title="Hapus Kategori"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!categories.data.length">
                                <td colspan="3" class="py-12 text-center text-slate-400">
                                    Tidak ada kategori ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="categories.links?.length > 3" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <strong class="text-slate-800">{{ categories.from || 0 }}</strong> - <strong class="text-slate-800">{{ categories.to || 0 }}</strong> dari <strong class="text-slate-800">{{ categories.total }}</strong> kategori
                    </p>
                    <div class="flex items-center gap-1">
                        <component
                            :is="link.url ? 'Link' : 'span'"
                            v-for="(link, i) in categories.links"
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

        <!-- Add Category Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="font-extrabold text-base text-slate-900">Tambah Kategori Baru</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitAdd" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Kategori</label>
                        <input
                            v-model="addForm.name"
                            type="text"
                            required
                            placeholder="Contoh: Coffee & Latte"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                        />
                        <p v-if="addForm.errors.name" class="text-rose-500 text-[11px] mt-1">{{ addForm.errors.name }}</p>
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
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-600/20"
                        >
                            {{ addForm.processing ? 'Menyimpan...' : 'Simpan Kategori' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="font-extrabold text-base text-slate-900">Edit Kategori</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Kategori</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            required
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                        />
                        <p v-if="editForm.errors.name" class="text-rose-500 text-[11px] mt-1">{{ editForm.errors.name }}</p>
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
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md shadow-indigo-600/20"
                        >
                            {{ editForm.processing ? 'Menyimpan...' : 'Perbarui' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="font-extrabold text-base text-slate-900 mt-3">Hapus Kategori?</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Anda yakin ingin menghapus kategori <strong>{{ categoryToDelete?.name }}</strong>? Produk dalam kategori ini akan menjadi tidak berkategori.
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

