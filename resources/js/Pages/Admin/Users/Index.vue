<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    users: Object,
    filters: Object,
    roles: Array,
});

const page = usePage();
const authUser = page.props.auth?.user;

const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');

// Search & Filter with debounce
let searchTimer = null;
watch([search, selectedRole], () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            route('admin.users.index'),
            { search: search.value, role: selectedRole.value },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300);
});

// Modals State
const showAddModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const userToEdit = ref(null);
const userToDelete = ref(null);

// Forms
const addForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'user',
});

const editForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'user',
});

const openAddModal = () => {
    addForm.reset();
    addForm.clearErrors();
    showAddModal.value = true;
};

const submitAdd = () => {
    addForm.post(route('admin.users.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (user) => {
    userToEdit.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    editForm.password = '';
    editForm.clearErrors();
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!userToEdit.value) return;
    editForm.put(route('admin.users.update', userToEdit.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            userToEdit.value = null;
        },
    });
};

const openDeleteModal = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!userToDelete.value) return;
    router.delete(route('admin.users.destroy', userToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            userToDelete.value = null;
        },
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Manajemen Pengguna - Mas POS" />

    <AdminLayout title="Manajemen Pengguna & Kasir">
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola akun staf kasir dan administrator sistem POS.</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter Bar & Actions -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-72">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama atau email..."
                            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                        />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Role Filter -->
                    <select
                        v-model="selectedRole"
                        class="w-full sm:w-40 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                    >
                        <option value="">Semua Peran</option>
                        <option value="user">Kasir</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <!-- Add User Button -->
                <button
                    @click="openAddModal"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/20 transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Pengguna</span>
                </button>
            </div>

            <!-- Table of Users -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-6 py-3.5">Nama & Profil</th>
                                <th class="px-6 py-3.5">Email</th>
                                <th class="px-6 py-3.5">Peran</th>
                                <th class="px-6 py-3.5">Terdaftar Sejak</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="px-6 py-4 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-200">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ user.name }}</p>
                                        <span v-if="user.id === authUser?.id" class="text-[10px] text-blue-600 font-semibold">(Akun Anda)</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider"
                                        :class="user.role === 'admin'
                                            ? 'bg-purple-100 text-purple-800'
                                            : 'bg-blue-100 text-blue-800'"
                                    >
                                        {{ user.role === 'admin' ? 'Administrator' : 'Kasir' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ formatDate(user.created_at) }}</td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button
                                        @click="openEditModal(user)"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition"
                                        title="Edit Pengguna"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button
                                        v-if="user.id !== authUser?.id"
                                        @click="openDeleteModal(user)"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition"
                                        title="Hapus Pengguna"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!users.data.length">
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    Tidak ada data pengguna ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links?.length > 3" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <strong class="text-slate-800">{{ users.from || 0 }}</strong> - <strong class="text-slate-800">{{ users.to || 0 }}</strong> dari <strong class="text-slate-800">{{ users.total }}</strong> pengguna
                    </p>
                    <div class="flex items-center gap-1">
                        <component
                            :is="link.url ? 'Link' : 'span'"
                            v-for="(link, i) in users.links"
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

        <!-- Add User Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="font-bold text-base text-slate-900">Tambah Pengguna Baru</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitAdd" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap</label>
                        <input
                            v-model="addForm.name"
                            type="text"
                            required
                            placeholder="Contoh: Budi Santoso"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                        <p v-if="addForm.errors.name" class="text-rose-500 text-[11px] mt-1">{{ addForm.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Email</label>
                        <input
                            v-model="addForm.email"
                            type="email"
                            required
                            placeholder="staf@maspos.com"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                        <p v-if="addForm.errors.email" class="text-rose-500 text-[11px] mt-1">{{ addForm.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kata Sandi (Password)</label>
                        <input
                            v-model="addForm.password"
                            type="password"
                            required
                            minlength="6"
                            placeholder="Minimal 6 karakter"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                        <p v-if="addForm.errors.password" class="text-rose-500 text-[11px] mt-1">{{ addForm.errors.password }}</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Peran / Hak Akses</label>
                        <select
                            v-model="addForm.role"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        >
                            <option value="user">Kasir</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <p v-if="addForm.errors.role" class="text-rose-500 text-[11px] mt-1">{{ addForm.errors.role }}</p>
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
                            {{ addForm.processing ? 'Menyimpan...' : 'Simpan Pengguna' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="font-bold text-base text-slate-900">Edit Pengguna</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            required
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                        <p v-if="editForm.errors.name" class="text-rose-500 text-[11px] mt-1">{{ editForm.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Email</label>
                        <input
                            v-model="editForm.email"
                            type="email"
                            required
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                        <p v-if="editForm.errors.email" class="text-rose-500 text-[11px] mt-1">{{ editForm.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ganti Password (Opsional)</label>
                        <input
                            v-model="editForm.password"
                            type="password"
                            minlength="6"
                            placeholder="Kosongkan jika tidak ingin mengganti"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                        <p v-if="editForm.errors.password" class="text-rose-500 text-[11px] mt-1">{{ editForm.errors.password }}</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Peran / Hak Akses</label>
                        <select
                            v-model="editForm.role"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        >
                            <option value="user">Kasir</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <p v-if="editForm.errors.role" class="text-rose-500 text-[11px] mt-1">{{ editForm.errors.role }}</p>
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
                            {{ editForm.processing ? 'Menyimpan...' : 'Perbarui Pengguna' }}
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="font-bold text-base text-slate-900 mt-3">Hapus Pengguna?</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Anda yakin ingin menghapus akun <strong>{{ userToDelete?.name }}</strong>? Tindakan ini tidak dapat dibatalkan.
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
