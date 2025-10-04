<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { ref } from 'vue';

const produk = ref('');
const harga = ref('');
const kategori = ref('');
const imageFile = ref(null);
const imagePreview = ref(null);
const totalTagihan = ref('Rp 224.000');
const cartItems = ref([]);

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        imageFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submitForm = () => {
    // Handle form submission
    console.log({
        produk: produk.value,
        harga: harga.value,
        kategori: kategori.value,
        image: imageFile.value
    });
};
</script>

<template>

    <Head title="Tambah Produk" />

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
                        <!-- Cart + Total Tagihan (merged design) -->
                        <div class="inline-flex items-center pr-4">
                            <Link :href="route('cart')" aria-label="Keranjang"
                                class="relative z-10 px-4 py-2 bg-blue-600 text-white rounded-l-lg hover:bg-blue-700 transition flex items-center justify-center shadow-md">
                            <!-- Badge angka item -->
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

                        <!-- Avatar + Dropdown (divider remains on the left) -->
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

        <!-- Form Tambah Produk -->
        <div class="flex items-center justify-center mt-12">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Tambah Produk</h2>

                <form @submit.prevent="submitForm">
                    <!-- Upload Image Area -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Gambar Produk
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center bg-gray-50 hover:bg-gray-100 transition cursor-pointer"
                            @click="$refs.fileInput.click()">
                            <div v-if="!imagePreview" class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500 mb-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm text-gray-600 mb-1">
                                    Seret & Letakan atau klik <span class="text-blue-600 font-medium">Pilih File</span>
                                    untuk
                                    diunggah
                                </p>
                                <p class="text-xs text-gray-500">
                                    Format yang diaksep: .jpg, .png
                                </p>
                            </div>
                            <div v-else class="relative">
                                <img :src="imagePreview" alt="Preview" class="max-h-48 mx-auto rounded-lg" />
                                <button type="button" @click.stop="imagePreview = null; imageFile = null"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <input ref="fileInput" type="file" accept="image/*" class="hidden"
                                @change="handleFileUpload" />
                        </div>
                    </div>

                    <!-- Input Produk -->
                    <div class="mb-4">
                        <label for="produk" class="block text-sm font-medium text-gray-700 mb-2">
                            Produk
                        </label>
                        <input type="text" id="produk" v-model="produk" placeholder="Produk"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>

                    <!-- Input Harga -->
                    <div class="mb-4">
                        <label for="harga" class="block text-sm font-medium text-gray-700 mb-2">
                            Harga
                        </label>
                        <input type="text" id="harga" v-model="harga" placeholder="Harga"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>

                    <!-- Select Kategori -->
                    <div class="mb-6">
                        <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Kategori
                        </label>
                        <select id="kategori" v-model="kategori"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white"
                            style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 24 24%27 stroke=%27%236b7280%27%3E%3Cpath stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%272%27 d=%27M19 9l-7 7-7-7%27/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25rem;">
                            <option value="" disabled selected>Pilih kategori</option>
                            <option value="pizza">Pizza</option>
                            <option value="burger">Burger</option>
                            <option value="drink">Drink</option>
                        </select>
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