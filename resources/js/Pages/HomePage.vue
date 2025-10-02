<script setup>

import { Head } from '@inertiajs/vue3';
import Header from "@/Components/Header.vue"
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
  { name: "Coca Cola", price: "Rp 10.000" }
]

</script>

<template>
  <Head title="Home" />
  <div class="p-6 bg-gray-100 min-h-screen">
    <!-- Header -->
    <Header />

    <!-- Grid Produk -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6 mt-6">
      <div v-for="(product, index) in products" :key="index"
        class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
        <img src="/PepperoniChesse.png" alt="Product" class="w-full h-32 object-cover rounded-md mb-4" />
        <h3 class="text-sm font-semibold text-gray-800">{{ product.name }}</h3>
        <p class="text-green-600 font-medium mb-3">{{ product.price }}</p>
        <button class="w-full py-2 px-3 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
          + Keranjang
        </button>
      </div>
    </div>
  </div>
</template>
