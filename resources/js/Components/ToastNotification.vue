<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success'); // 'success', 'error', 'info'
let timer = null;

const showToast = (msg, toastType = 'success') => {
    message.value = msg;
    type.value = toastType;
    visible.value = true;

    if (timer) clearTimeout(timer);
    timer = setTimeout(() => {
        visible.value = false;
    }, 4000);
};

const close = () => {
    visible.value = false;
    if (timer) clearTimeout(timer);
};

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            showToast(flash.success, 'success');
        } else if (flash?.error) {
            showToast(flash.error, 'error');
        } else if (flash?.info) {
            showToast(flash.info, 'info');
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="visible"
            class="fixed top-5 right-5 z-50 max-w-sm w-full bg-white dark:bg-slate-900 rounded-xl shadow-2xl border p-4 pointer-events-auto"
            :class="{
                'border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-100': type === 'success',
                'border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-100': type === 'error',
                'border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-100': type === 'info'
            }"
        >
            <div class="flex items-start gap-3">
                <!-- Icon -->
                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                    :class="{
                        'bg-emerald-100 text-emerald-600': type === 'success',
                        'bg-rose-100 text-rose-600': type === 'error',
                        'bg-blue-100 text-blue-600': type === 'info'
                    }"
                >
                    <svg v-if="type === 'success'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else-if="type === 'error'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <!-- Text -->
                <div class="flex-1 pt-0.5">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-75">
                        {{ type === 'success' ? 'Sukses' : type === 'error' ? 'Peringatan' : 'Informasi' }}
                    </p>
                    <p class="text-sm font-medium mt-0.5 leading-snug">{{ message }}</p>
                </div>

                <!-- Close -->
                <button
                    @click="close"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition p-1"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>

