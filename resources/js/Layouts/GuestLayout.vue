<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const currentSlide = ref(0);
let intervalId = null;

const slides = [
  {
    image: '/mockup.png',
    text: 'Desain yang user-friendly membuat navigasi cepat dan mudah, bahkan bagi pemula.'
  },
  {
    image: '/mockup.png',
    text: 'Kelola transaksi dan inventori dengan mudah dalam satu platform terintegrasi.'
  },
  {
    image: '/mockup.png',
    text: 'Laporan real-time membantu Anda membuat keputusan bisnis yang lebih baik.'
  }
];

const goToSlide = (index) => {
  currentSlide.value = index;
};

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % slides.length;
};

onMounted(() => {
  intervalId = setInterval(nextSlide, 5000); //setiap 5 detik
});

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId);
  }
});
</script>

<template>
  <div class="h-screen overflow-hidden flex bg-[#EDF0F2] font-sans">
    <!-- Bagian kiri -->
    <div class="hidden lg:flex w-1/2 items-center justify-center p-8">
      <div
        class="w-full max-w-[650px] h-[90vh] rounded-[30px] bg-[radial-gradient(84.13%_85.95%_at_26.18%_17.42%,_#4288F2_0%,_#113E82_100%)] text-white flex flex-col p-8 overflow-hidden relative">

        <!-- Wrapper untuk transisi gambar -->
        <div class="flex-shrink-0 -mt-8 -ml-8 mb-auto relative">
          <div v-for="(slide, index) in slides" :key="`img-${index}`"
            class="transition-opacity duration-700 ease-in-out absolute top-0 left-0"
            :class="currentSlide === index ? 'opacity-100' : 'opacity-0'">
            <img :src="slide.image" alt="POS Mockup" class="w-[600px] h-[530px] pointer-events-none select-none" />
          </div>
          <!-- Spacer untuk maintain layout -->
          <img src="/mockup.png" alt="" class="w-[500px] h-auto invisible pointer-events-none select-none" />
        </div>

        <!-- Text area -->
        <div class="flex-shrink-0 -mt-10 mb-3">
          <div class="max-w-md text-left relative">
            <div v-for="(slide, index) in slides" :key="`text-${index}`"
              class="transition-opacity duration-700 ease-in-out absolute inset-0"
              :class="currentSlide === index ? 'opacity-100' : 'opacity-0'">
              <h2 class="text-[22px] font-normal leading-normal">{{ slide.text }}</h2>
            </div>
            <!-- Spacer untuk maintain height -->
            <h2 class="text-[22px] font-normal leading-normal invisible">{{ slides[0].text }}</h2>
          </div>
        </div>

        <!-- Dots Navigation -->
        <div class="flex-shrink-0 flex justify-center items-center gap-2 mt-6">
          <button v-for="(slide, index) in slides" :key="`dot-${index}`" @click="goToSlide(index)"
            class="h-3 rounded-full transition-all duration-300"
            :class="currentSlide === index ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/75 w-3'"
            :aria-label="`Go to slide ${index + 1}`">
          </button>
        </div>
      </div>
    </div>

    <!-- Bagian kanan -->
    <div class="flex flex-col w-full lg:w-1/2 justify-center px-8 py-8 relative">
      <!-- Overlay judul: top sejajar p-10 kiri, kanan sejajar tepi form -->
      <div class="absolute inset-x-0 top-8">
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