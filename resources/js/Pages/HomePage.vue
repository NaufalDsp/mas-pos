<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
  products: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  cart_count: { type: Number, default: 0 },
  cart_total: { type: [Number, String], default: 0 },
  cart_total_formatted: { type: String, default: '' },
});

const searchTerm = ref('');
const selectedCategoryId = ref(null);
const showDeleteModal = ref(false);
const productToDelete = ref(null);

const formatCurrency = (value) => {
  if (typeof value === 'string' && value.trim().startsWith('Rp')) return value;
  const number = Number(value || 0);
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(number);
};

const totalTagihan = computed(() => props.cart_total_formatted || formatCurrency(props.cart_total));

const filteredProducts = computed(() => {
  const term = searchTerm.value.trim().toLowerCase();
  return props.products.filter((p) => {
    const inCategory =
      !selectedCategoryId.value ||
      p.category_id === selectedCategoryId.value ||
      p.category?.id === selectedCategoryId.value;
    const inSearch = !term || p.name?.toLowerCase().includes(term);
    return inCategory && inSearch;
  });
});

const addToCart = (product) => {
  router.post(route('cart.store'), { product_id: product.id, qty: 1 }, { preserveScroll: true });
};

const openDeleteModal = (product) => {
  productToDelete.value = product;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  productToDelete.value = null;
};

const confirmDelete = () => {
  if (productToDelete.value) {
    router.delete(route('products.destroy', productToDelete.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        closeDeleteModal();
      }
    });
  }
};

// Simplified scrollable category logic
const scrollContainer = ref(null);
const showLeft = ref(false);
const showRight = ref(false);

const handleScroll = () => {
  const el = scrollContainer.value;
  if (!el) return;

  const { scrollLeft, clientWidth, scrollWidth } = el;
  showLeft.value = scrollLeft > 5;
  showRight.value = scrollLeft + clientWidth < scrollWidth - 5;
};

const scrollLeft = () => {
  const el = scrollContainer.value;
  if (el) {
    el.scrollBy({ left: -el.clientWidth, behavior: 'smooth' });
  }
};

const scrollRight = () => {
  const el = scrollContainer.value;
  if (el) {
    el.scrollBy({ left: el.clientWidth, behavior: 'smooth' });
  }
};

onMounted(() => {
  nextTick(() => handleScroll());
  window.addEventListener('resize', handleScroll);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleScroll);
});
</script>

<style>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>

<template>

  <Head title="Home" />
  <div class="p-6 bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow rounded-lg p-6 mb-6">
      <!-- Bar Atas -->
      <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-300">
        <h1>
          <Link :href="route('home')" class="text-xl font-extrabold text-blue-600 hover:opacity-80">MASPOS</Link>
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
                  {{ props.cart_count }}
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

            <div class="pl-4">
              <Dropdown align="right" width="48">
                <template #trigger="{ open }">
                  <button type="button"
                    class="inline-flex items-center gap-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                    :aria-expanded="open">
                    <img src="/PepperoniChesse.png" alt="User" class="w-10 h-10 rounded-full object-cover" />
                    <span class="text-sm font-medium text-gray-800 max-w-[140px] truncate hidden sm:block">
                      {{ $page.props.auth?.user?.name || 'User' }}
                    </span>
                    <svg class="h-4 w-4 text-gray-500 transition-transform duration-200"
                      xmlns="http://www.w3.org/2000/svg" fill="currentColor" :class="open ? 'rotate-180' : ''"
                      viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.25 8.29a.75.75 0 01-.02-1.08z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>
                </template>
                <template #content>
                  <DropdownLink :href="route('profile.edit')">Edit Profile</DropdownLink>
                  <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                </template>
              </Dropdown>
            </div>
          </div>
        </div>
      </div>


      <!-- Search + Kategori Scrollable -->
      <div class="flex items-center gap-2">
        <!-- Search (fixed size, tidak boleh menyusut) -->
        <div class="relative w-[272px] h-[32px] flex-shrink-0">
          <!-- icon search -->
          <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
            class="absolute left-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none"
            stroke="currentColor">
            <circle cx="11" cy="11" r="7" stroke-width="2" />
            <path d="M21 21l-4.35-4.35" stroke-width="2" stroke-linecap="round" />
          </svg>

          <input type="text" placeholder="Cari nama produk ..."
            class="w-full h-full border rounded-lg pl-8 pr-2 text-sm focus:outline-none focus:ring focus:ring-blue-300"
            v-model="searchTerm" />
        </div>

        <!-- Tombol Scroll Kiri: tepat di sebelah kanan kotak search -->
        <button v-if="showLeft" @click="scrollLeft"
          class="h-[32px] w-8 flex items-center justify-center bg-white rounded-md shadow" aria-label="Scroll left">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <!-- Scrollable category area -->
        <div class="relative flex-1 min-w-0">
          <div ref="scrollContainer" class="flex gap-2 overflow-x-auto no-scrollbar py-1 px-2 items-center"
            @scroll="handleScroll">
            <button
              class="px-4 h-[32px] rounded-lg text-sm whitespace-nowrap flex items-center justify-center flex-shrink-0"
              :class="!selectedCategoryId ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600 hover:bg-blue-200'"
              @click="selectedCategoryId = null">
              Semua
            </button>

            <button v-for="cat in props.categories" :key="cat.id"
              class="px-4 h-[32px] rounded-lg text-sm whitespace-nowrap flex items-center justify-center flex-shrink-0"
              :class="selectedCategoryId === cat.id ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600 hover:bg-blue-200'"
              @click="selectedCategoryId = cat.id">
              {{ cat.name }}
            </button>
          </div>

          <!-- Fade overlays -->
          <div v-show="showLeft"
            class="pointer-events-none absolute left-0 top-0 h-full w-8 bg-gradient-to-r from-white to-transparent">
          </div>
          <div v-show="showRight"
            class="pointer-events-none absolute right-0 top-0 h-full w-8 bg-gradient-to-l from-white to-transparent">
          </div>
        </div>

        <!-- Tombol Scroll Kanan: berada di ujung kanan area -->
        <button v-if="showRight" @click="scrollRight"
          class="h-[32px] w-8 flex items-center justify-center bg-white rounded-md shadow" aria-label="Scroll right">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </header>

    <!-- Grid Produk -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-y-6 gap-x-9 mt-4">
      <div v-for="product in filteredProducts" :key="product.id || product.name"
        class="bg-white rounded-xl shadow p-2 flex flex-col w-[216px]">
        <div class="relative mb-4">
          <img :src="product.image_url || '/PepperoniChesse.png'" :alt="product.name"
            class="w-full h-[177px] object-cover rounded-md" />
          <!-- Trash Icon -->
          <button @click="openDeleteModal(product)"
            class="absolute bottom-2 right-2 w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-md flex items-center justify-center shadow-lg transition"
            aria-label="Hapus produk">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
        <h3 class="text-sm font-semibold text-gray-800">{{ product.name }}</h3>
        <p class="text-green-600 font-medium mb-3">{{ formatCurrency(product.price) }}</p>
        <button
          class="w-full py-2 px-3 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition flex items-center justify-center gap-1"
          @click="addToCart(product)">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Keranjang
        </button>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeDeleteModal"></div>

      <!-- Modal Content -->
      <div class="relative bg-white rounded-xl shadow-2xl w-[442px] h-[280px] z-10">
        <!-- Icon Trash di Pojok Kiri Atas -->
        <div class="absolute top-5 left-5">
          <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center">
            <img src="/Trash.png" alt="Trash" class="w-18 h-18" />
          </div>
        </div>

        <!-- Content -->
        <div class="pt-20 px-5 text-left">
          <h3 class="text-lg font-bold text-gray-800 pt-3 pb-1">Hapus Produk</h3>
          <p class="text-sm text-gray-500 mb-10">
            Apakah Anda yakin ingin menghapus produk ini?
          </p>
          <!-- Garis Batas -->
          <div class="border-t border-gray-300 pt-4 pb-2 -mx-5"></div>

          <div class="flex gap-3 pt-2">
            <button @click="closeDeleteModal"
              class="flex-1 py-2.5 px-4 bg-white border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium text-sm">
              Batal
            </button>
            <button @click="confirmDelete"
              class="flex-1 py-2.5 px-4 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-medium text-sm">
              Hapus
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>