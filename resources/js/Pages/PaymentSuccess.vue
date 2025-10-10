<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    transaction: Object,
});
</script>

<template>

    <Head title="Pembayaran Sukses" />

    <div
        class="min-h-screen bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Success Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 text-center">
                <!-- Animated Success Icon -->
                <div class="mb-6 relative">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-24 h-24 bg-green-100 rounded-full animate-ping opacity-20"></div>
                    </div>
                    <div class="relative flex items-center justify-center">
                        <div
                            class="w-24 h-24 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-lg">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Pembayaran Sukses</h1>
                <p class="text-gray-600 mb-6">Transaksi Anda telah berhasil diproses</p>

                <!-- Transaction Details -->
                <div class="bg-gray-50 rounded-2xl p-6 mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm text-gray-600">Total</span>
                        <span class="text-2xl font-bold text-gray-900">{{ transaction.total_formatted }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Tanggal</span>
                        <span class="text-gray-900 font-medium">{{ transaction.date }}</span>
                    </div>
                </div>

                <!-- Action Button -->
                <Link :href="route('home')"
                    class="inline-flex items-center justify-center w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all transform hover:scale-[1.02] shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Home
                </Link>

                <!-- Transaction ID -->
                <p class="mt-6 text-xs text-gray-400">
                    ID Transaksi: #{{ transaction.id }}
                </p>
            </div>

            <!-- Additional Info -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    Transaksi telah tercatat di sistem
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes ping {

    75%,
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

.animate-ping {
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>