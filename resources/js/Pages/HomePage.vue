<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const products = [
  { name: "Pepperoni Cheese", price: "Rp 45.000" },
  { name: "Pepperoni Fiesta", price: "Rp 55.000" },
  { name: "Smoky Bacon Ranch", price: "Rp 47.500" },
  { name: "Mediterranean Feast", price: "Rp 57.000" },
  { name: "Mushroom & Truffle", price: "Rp 38.000" },
  { name: "French Fries", price: "Rp 18.000" },
  { name: "Burger Gilz", price: "Rp 29.000" },
  { name: "Big Mac Cheese", price: "Rp 31.000" },
  { name: "Lechy Tea", price: "Rp 12.000" },
  { name: "Coca Cola", price: "Rp 10.000" },
  { name: "BBQ Chicken Pizza", price: "Rp 52.000" },
  { name: "Veggie Supreme", price: "Rp 40.000" },
  { name: "Spicy Wings", price: "Rp 28.000" },
  { name: "Chocolate Shake", price: "Rp 16.000" },
  { name: "Iced Coffee", price: "Rp 15.000" },
  { name: "Smoky Bacon Ranch", price: "Rp 47.500" },
  { name: "Mediterranean Feast", price: "Rp 57.000" },
  { name: "Mushroom & Truffle", price: "Rp 38.000" },
]
const totalTagihan = ref('Rp 224.000');
const cartItems = ref([]);
</script>

<template>

  <Head title="Home" />

  <div class="p-6 bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow rounded-lg p-6 mb-6">
      <!-- Bar Atas: Logo + Tombol + Keranjang + Avatar -->
      <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-300">
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l3-7H5.4M7 13L5.4 5M7 13l-2 9m12-9l2 9M9 21a2 2 0 11-4 0 2 2 0zm10 0a2 2 0 11-4 0 2 2 0" />
                </svg>
              </Link>
              <div class="px-4 py-2 bg-blue-50 text-blue-700 rounded-r-lg text-sm font-semibold">
                Total Tagihan {{ totalTagihan }}
              </div>
            </div>

            <!-- Avatar + Dropdown -->
            <div class="pl-4">
              <Dropdown align="right" width="48">
                <template #trigger>
                  <button type="button"
                    class="inline-flex items-center gap-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                    aria-label="Menu profil">
                    <img src="/PepperoniChesse.png" alt="User" class="w-10 h-10 rounded-full object-cover" />
                    <svg class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                      fill="currentColor">
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

      <!-- Bar Bawah: Search + Kategori -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4">
        <!-- Search -->
        <div class="flex-1 max-w-md">
          <input type="text" placeholder="Cari nama produk ..."
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-300" />
        </div>

        <!-- Filter Kategori -->
        <div class="flex gap-2 mt-3 sm:mt-0">
          <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Semua</button>
          <button class="px-4 py-2 bg-gray-200 rounded-lg text-sm hover:bg-gray-300">Burger</button>
          <button class="px-4 py-2 bg-gray-200 rounded-lg text-sm hover:bg-gray-300">Pizza</button>
          <button class="px-4 py-2 bg-gray-200 rounded-lg text-sm hover:bg-gray-300">Drink</button>
        </div>
      </div>
    </header>


    <!-- Grid Produk -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-y-6 gap-x-2 mt-4">
      <div v-for="(product, index) in products" :key="index"
        class="bg-white rounded-xl shadow p-2 flex flex-col w-[216px]">
        <img src="/PepperoniChesse.png" alt="Product" class="w-full h-[177px] object-cover rounded-md mb-4" />
        <h3 class="text-sm font-semibold text-gray-800">{{ product.name }}</h3>
        <p class="text-green-600 font-medium mb-3">{{ product.price }}</p>
        <button class="w-full py-2 px-3 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
          + Keranjang
        </button>
      </div>
    </div>
  </div>
</template>