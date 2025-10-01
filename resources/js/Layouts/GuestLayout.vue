<template>
    <div class="min-h-screen flex bg-gray-100">
      <!-- Bagian kiri -->
      <div class="hidden lg:flex w-1/2 bg-blue-600 items-center justify-center p-10 text-white">
        <div class="max-w-md text-center">
          <!-- Gambar Mockup -->
          <img src="/images/mockup.png" alt="POS Mockup" class="rounded-xl shadow-lg mb-6">
          <h2 class="text-lg font-semibold mb-2">Desain yang user-friendly membuat</h2>
          <p class="text-sm">
            navigasi cepat dan mudah, bahkan bagi pemula.
          </p>
        </div>
      </div>
  
      <!-- Bagian kanan -->
      <div class="flex flex-col w-full lg:w-1/2 bg-white justify-center px-8">
        <div class="max-w-md w-full mx-auto">
          <!-- Logo -->
          <h1 class="text-2xl font-bold text-blue-600 mb-6 text-right">MASPOS</h1>
  
          <!-- Slot untuk konten halaman (Login.vue) -->
          <slot />
        </div>
      </div>
    </div>
  </template>
  