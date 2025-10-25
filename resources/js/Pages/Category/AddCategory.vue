<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    totalTagihan: String,
    cartItemsCount: Number,
});

const page = usePage();
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success'); // success or error

// Inertia form helper
const form = useForm({
    name: ''
});

const submitForm = () => {
    form.post(route('categories.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showToastNotification('Kategori berhasil ditambahkan!', 'success');
        },
        onError: (errors) => {
            // Toast notification untuk error umum
            showToastNotification('Kategori tidak dapat ditambahkan', 'error');
            // Error message detail tetap ditampilkan di bawah form via form.errors.name
        }
    });
};

const cancelForm = () => {
    form.reset();
    window.history.back();
};

const showToastNotification = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;

    setTimeout(() => {
        showToast.value = false;
    }, 4000);
};

const closeToast = () => {
    showToast.value = false;
};

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        showToastNotification(flash.success, 'success');
    }
    if (flash?.error) {
        showToastNotification(flash.error, 'error');
    }
}, { deep: true });
</script>

<template>

    <Head title="Tambah Kategori" />

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
                        <!-- Cart + Total Tagihan -->
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

                        <!-- Avatar + Dropdown -->
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


        
        <!-- Form Tambah Kategori -->
        <div class="flex items-center justify-center mt-40">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Tambah Kategori</h2>

                <form @submit.prevent="submitForm">
                    <!-- Input Kategori -->
                    <div class="mb-6">
                        <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori
                        </label>
                        <input type="text" id="kategori" v-model="form.name" placeholder="Kategori"
                            class="w-full border rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="form.errors.name ? 'border-red-500' : 'border-gray-300'" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Tombol Action -->
                    <div class="flex gap-3">
                        <button type="button" @click="cancelForm"
                            class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition"
                            :disabled="form.processing">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition disabled:opacity-50"
                            :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Tambah' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toast Notification -->
        <transition enter-active-class="transform transition ease-out duration-300"
            enter-from-class="translate-x-full opacity-0" enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transform transition ease-in duration-200" leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0">
            <div v-if="showToast"
                class="fixed top-36 right-6 z-50 w-[309px] h-[43px] shadow-lg rounded-lg overflow-hidden"
                :class="toastType === 'success' ? 'bg-green-300 border-l-4 border-green-500' : 'bg-red-200 border-l-4 border-red-800'">
                <!-- Adjust padding and alignment -->
                <div class="h-full px-3 flex items-center">
                    <!-- Icon - adjusted size -->
                    <div class="flex-shrink-0">
                        <svg v-if="toastType === 'success'" class="h-5 w-5 text-green-500" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="h-5 w-5 text-red-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>


                    <!-- Message - adjusted spacing -->
                    <div class="ml-2 flex-1">
                        <p class="text-xs font-medium leading-none"
                            :class="toastType === 'success' ? 'text-green-800' : 'text-red-800'">
                            {{ toastMessage }}
                        </p>
                    </div>


                    <!-- Close Button -->
                    <button @click="closeToast"
                        class="ml-4 flex-shrink-0 inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>