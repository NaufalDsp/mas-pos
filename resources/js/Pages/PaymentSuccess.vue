<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { ref } from 'vue';

const props = defineProps({
    transaction: Object,
    totalTagihan: String,
    cartItemsCount: Number,
});

const cartItems = ref([]);
</script>
<template>
    <Head title="Pembayaran Sukses" />
    <div class="p-6 bg-gray-100 min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-300">
                <h1>
                    <Link :href="route('home')" class="text-xl font-extrabold text-blue-600 hover:opacity-80">
                        MASPOS
                    </Link>
                </h1>
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

                        <div class="pl-4">
                            <Dropdown align="right" width="48">
                                <template #trigger="{ open }">
                                    <button type="button"
                                        class="inline-flex items-center gap-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                                        :aria-expanded="open">
                                        <img src="/PepperoniChesse.png" alt="User"
                                            class="w-10 h-10 rounded-full object-cover" />
                                        <span
                                            class="text-sm font-medium text-gray-800 max-w-[140px] truncate hidden sm:block">
                                            {{ $page.props.auth?.user?.name || 'User' }}
                                        </span>
                                        <svg class="h-4 w-4 text-gray-500 transition-transform duration-200"
                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                            :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.25 8.29a.75.75 0 01-.02-1.08z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">Edit Profile</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Log Out
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Success Content -->
        <div class="flex items-center justify-center py-12">
            <div class="max-w-md w-full">
                <!-- Success Card -->
                <div class="bg-white rounded-2xl p-6 text-center">
                    <div class="mb-2 relative flex items-center justify-center">
                        <div class="relative">
                            <img src="/Sucess.png" alt="Success" class="w-40 h-40 object-contain" />
                        </div>
                    </div>

                    <!-- Success Message -->
                    <h1 class="text-2xl font-bold text-green-600 mb-2">Pembayaran Sukses</h1>
                    <p class="text-gray-600 mb-4">Transaksi Anda telah berhasil diproses</p>

                    <!-- Transaction Details -->
                    <div class="bg-gray-50 rounded-2xl p-6 mb-1 text-center">
                        <div class="mb-4">
                            <div class="text-sm text-gray-400 mb-1">Total</div>
                            <div class="text-xl font-bold text-gray-900">{{ transaction.total_formatted }}</div>
                        </div>
                        <div>
                            <div class="text-gray-400 font-medium">{{ transaction.date }}</div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <Link :href="route('home')"
                        class="inline-flex items-center justify-center w-full px-4 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all transform hover:scale-[1.0] shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </Link>

                    <!-- Transaction ID -->
                    <p class="mt-6 text-xs text-gray-400">
                        ID Transaksi: #{{ transaction.id }}
                    </p>
                </div>

                <!-- Additional Info -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        Transaksi telah tercatat di sistem
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
