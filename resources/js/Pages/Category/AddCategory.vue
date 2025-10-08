<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    totalTagihan: String,
    cartItemsCount: Number,
});

// Inertia form helper
const form = useForm({
    name: ''
});

const submitForm = () => {
    form.post(route('categories.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
};

const cancelForm = () => {
    form.reset();
    window.history.back();
};
</script>

<template>

    <Head title="Tambah Kategori" />

    <div class="p-6 bg-gray-100 min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow rounded-lg p-6 mb-6">
            <!-- Bar Atas: Logo + Tombol + Keranjang + Avatar -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-300">
                <!-- Logo -->
                <h1>
                    <Link :href="route('home')" class="text-xl font-extrabold text-blue-600 hover:opacity-80">
                    MASPOS
                    </Link>
                </h1>

                <!-- Kontrol Kanan: Tombol + Keranjang + Avatar -->
                <div class="flex items-center gap-3">
                    <Link :href="route('categories.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
                    + Tambah Kategori
                    </Link>
                    <Link :href="route('products.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
                    + Tambah Produk
                    </Link>

                    <div class="flex items-center divide-x-2 divide-gray-300">
                        <!-- Cart + Total Tagihan -->
                        <div class="inline-flex items-center pr-4">
                            <Link :href="route('cart')" aria-label="Keranjang"
                                class="relative z-10 px-4 py-2 bg-blue-600 text-white rounded-l-lg hover:bg-blue-700 transition flex items-center justify-center shadow-md">
                            <span
                                class="absolute -top-2 -right-1 bg-green-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center border-2 border-white">
                                {{ cartItemsCount || 0 }}
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                            </Link>
                            <div
                                class="px-4 py-2 bg-blue-50 text-blue-700 rounded-r-lg text-sm font-semibold whitespace-nowrap">
                                Total Tagihan {{ totalTagihan || 'Rp 0' }}
                            </div>
                        </div>

                        <!-- Avatar + Dropdown -->
                        <div class="pl-4">
                            <Dropdown align="right" width="48">
                                <template #trigger="{ open }">
                                    <button type="button"
                                        class="inline-flex items-center gap-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                                        aria-label="Menu profil" :aria-expanded="open">
                                        <img src="/PepperoniChesse.png" alt="User"
                                            class="w-10 h-10 rounded-full object-cover" />
                                        <span
                                            class="text-sm font-medium text-gray-800 max-w-[140px] truncate hidden sm:block">
                                            {{ $page.props.auth?.user?.name || 'User' }}
                                        </span>
                                        <svg class="text-gray-500" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor"
                                            :class="['h-4 w-4 transition-transform duration-200', open ? 'rotate-180' : '']">
                                            <path fill-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.25 8.29a.75.75 0 01-.02-1.08z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Edit Profile
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        Log Out
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Form Tambah Kategori -->
        <div class="flex items-center justify-center mt-20">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Tambah Kategori</h2>

                <form @submit.prevent="submitForm">
                    <!-- Input Kategori -->
                    <div class="mb-6">
                        <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori
                        </label>
                        <input type="text" id="kategori" v-model="form.name" placeholder="Kategori"
                            class="w-full border rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="form.errors.name ? 'border-red-500' : 'border-gray-300'" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Tombol Action -->
                    <div class="flex gap-3">
                        <button type="button" @click="cancelForm"
                            class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition"
                            :disabled="form.processing">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition disabled:opacity-50"
                            :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Tambah' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>