<script setup>
import { Head } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { ref } from 'vue';

const kategori = ref('');
const totalTagihan = ref('Rp 224.000');
</script>

<template>

    <Head title="Tambah Kategori" />

    <div class="p-6 bg-gray-100 min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow rounded-lg p-6 mb-6">
            <!-- Bar Atas: Logo + Tombol + Keranjang + Avatar -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-300">
                <!-- Logo -->
                <h1 class="text-xl font-extrabold text-blue-600">MASPOS</h1>

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
                        <!-- Keranjang dengan Total Tagihan -->
                        <button type="button" aria-label="Keranjang"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l3-7H5.4M7 13L5.4 5M7 13l-2 9m12-9l2 9M9 21a2 2 0 11-4 0 2 2 0zm10 0a2 2 0 11-4 0 2 2 0" />
                            </svg>
                            <span class="text-sm font-medium">Total Tagihan {{ totalTagihan }}</span>
                        </button>

                        <!-- Avatar + Dropdown -->
                        <div class="pl-4">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button type="button"
                                        class="inline-flex items-center gap-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                                        aria-label="Menu profil">
                                        <img src="/PepperoniChesse.png" alt="User"
                                            class="w-10 h-10 rounded-full object-cover" />
                                        <svg class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
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
                        <input type="text" id="kategori" v-model="kategori" placeholder="Kategori"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>

                    <!-- Tombol Action -->
                    <div class="flex gap-3">
                        <button type="button"
                            class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                            Tambah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>