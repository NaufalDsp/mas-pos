<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { computed } from 'vue';

// Props dari Laravel Controller
const props = defineProps({
    cartItems: Array,
    totalTagihan: String,
});

// Helper untuk format harga (string/number)
const toNumber = (v) => (typeof v === 'number' ? v : Number(v || 0));
const formatPrice = (price) => `Rp ${toNumber(price).toLocaleString('id-ID')}`;

// Subtotal per item
const getSubTotal = (item) => {
    return formatPrice(item.price * item.quantity);
};

//Update jumlah item 
const updateQuantity = (item, newQty) => {
    if (newQty < 1) return;
    router.patch(route('cart.items.update', item.id), { quantity: newQty }, {
        preserveScroll: true,
    });
};

// Hapus item 
const removeItem = (id) => {
    if (confirm('Apakah kamu yakin ingin menghapus produk ini dari keranjang?')) {
        router.delete(route('cart.items.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Checkout
const checkout = () => {
    if (confirm('Lanjutkan ke pembayaran?')) {
        router.post(route('cart.checkout'), {}, {
            preserveScroll: true,
        });
    }
};

</script>

<template>

    <Head title="Keranjang" />

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
                                {{ cartItems.length }}
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                            </Link>
                            <div class="px-4 py-2 bg-blue-50 text-blue-700 rounded-r-lg text-sm font-semibold">
                                Total Tagihan {{ totalTagihan }}
                            </div>
                        </div>

                        <!-- Avatar -->
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

        <!-- Cart Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Produk</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Harga</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Jumlah</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Sub Total</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in cartItems" :key="item.id" class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <img :src="(item.product && (item.product.image_url || item.product.image)) || '/PepperoniChesse.png'"
                                    :alt="item.product ? item.product.name : 'Produk'"
                                    class="w-20 h-20 rounded-lg object-cover" />
                                <span class="text-sm font-medium text-gray-800">{{ item.product ? item.product.name :
                                    'Produk'
                                }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-800">{{ formatPrice(item.price) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-3">
                                <button @click="updateQuantity(item, item.quantity - 1)"
                                    class="w-8 h-8 flex items-center justify-center rounded bg-gray-200 hover:bg-gray-300 transition text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <span class="text-sm font-medium text-gray-800 w-8 text-center">{{ item.quantity
                                }}</span>
                                <button @click="updateQuantity(item, item.quantity + 1)"
                                    class="w-8 h-8 flex items-center justify-center rounded bg-gray-200 hover:bg-gray-300 transition text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-800">{{ getSubTotal(item) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="removeItem(item.id)"
                                class="text-red-500 hover:text-red-700 text-sm font-medium transition">
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3 border-t border-gray-200">
                <Link :href="route('home')"
                    class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-100 transition">
                Kembali
                </Link>
                <button @click="checkout"
                    class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Bayar
                </button>
            </div>
        </div>
    </div>
</template>
