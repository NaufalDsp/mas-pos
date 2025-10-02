<template>
  <div class="min-h-screen overflow-x-hidden overflow-y-auto flex container bg-[#EDF0F2] font-sans">
    <!-- Bagian kiri -->
    <div class="hidden lg:flex w-1/2 items-center justify-center p-10">
      <div
        class="w-full max-w-[794px] self-stretch rounded-[40px] bg-[radial-gradient(84.13%_85.95%_at_26.18%_17.42%,_#4288F2_0%,_#113E82_100%)] text-white grid grid-rows-[auto_1fr] p-10 overflow-hidden">
        <!-- Gambar Mockup: mentok pojok kiri-atas, tetap dalam kotak -->
        <div class="row-start-1 justify-self-start self-start -mt-10 -ml-10">
          <img src="/mockup.png" alt="POS Mockup" class="w-[560px] h-auto pointer-events-none select-none" />
        </div>
        <div class="row-start-2 place-self-center max-w-md text-center">
          <h2 class="text-[28px] font-normal mb-2">Desain yang user-friendly membuat
            navigasi cepat dan mudah, bahkan bagi pemula.</h2>
        </div>
      </div>
    </div>

    <!-- Bagian kanan -->
    <div class="flex flex-col w-full lg:w-1/2 justify-center px-8 relative">
      <!-- Overlay judul: top sejajar p-10 kiri, kanan sejajar tepi form -->
      <div class="absolute inset-x-0 top-10">
        <div class="max-w-md w-full mx-auto">
          <h1 class="text-3xl font-bold text-blue-600 text-right">MASPOS</h1>
        </div>
      </div>

      <div class="max-w-md w-full mx-auto">
        <!-- Slot untuk konten halaman (Login.vue) -->
        <slot />
      </div>
    </div>
  </div>
</template>