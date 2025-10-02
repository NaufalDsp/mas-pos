<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
</script>

<template>
  <header class="flex items-center justify-between px-6 py-4 bg-white shadow rounded-lg mb-6">
    <!-- Logo -->
    <h1 class="text-xl font-bold text-blue-600">MASPOS</h1>

    <!-- Search -->
    <div class="flex items-center gap-2 flex-1 max-w-md mx-6">
      <input type="text" placeholder="Cari nama produk ..."
        class="flex-1 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-300" />
    </div>

    <!-- Filter Kategori -->
    <div class="flex gap-2">
      <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Semua</button>
      <button class="px-4 py-2 bg-gray-200 rounded-lg text-sm hover:bg-gray-300">Burger</button>
      <button class="px-4 py-2 bg-gray-200 rounded-lg text-sm hover:bg-gray-300">Pizza</button>
      <button class="px-4 py-2 bg-gray-200 rounded-lg text-sm hover:bg-gray-300">Drink</button>
    </div>

    <!-- Tambah -->
    <div class="flex gap-2 ml-6">
      <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">+ Tambah Kategori</button>
      <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">+ Tambah Produk</button>
      <button type="button" aria-label="Keranjang"
        class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 3h2l.4 2M7 13h10l3-7H5.4M7 13L5.4 5M7 13l-2 9m12-9l2 9M9 21a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0" />
        </svg>
      </button>
    </div>

    <!-- Avatar + Dropdown -->
    <div class="ml-6 relative">
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
  </header>
</template>