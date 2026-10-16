<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';

defineProps({
    title: {
        type: String,
        default: 'Admin Dashboard',
    },
});

const page = usePage();
const user = page.props.auth?.user;
const sidebarOpen = ref(false);
const currentTime = ref('');

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleDateString('id-ID', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

let timer = null;
onMounted(() => {
    updateTime();
    timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const logout = () => {
    router.post(route('logout'));
};

const navigation = [
    {
        name: 'Dashboard',
        href: route('admin.dashboard'),
        active: route().current('admin.dashboard'),
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
        name: 'Manajemen User',
        href: route('admin.users.index'),
        active: route().current('admin.users.*'),
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    },
    {
        name: 'Produk & Inventori',
        href: route('admin.products.index'),
        active: route().current('admin.products.*'),
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    },
    {
        name: 'Kategori',
        href: route('admin.categories.index'),
        active: route().current('admin.categories.*'),
        icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
    },
    {
        name: 'Laporan Penjualan',
        href: route('admin.transactions.index'),
        active: route().current('admin.transactions.*'),
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    },
];
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 flex">
        <ToastNotification />

        <!-- Mobile Sidebar Overlay -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden transition-opacity"
        ></div>

        <!-- Sidebar Navigation -->
        <aside
            class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white flex flex-col justify-between transition-transform duration-300 ease-in-out lg:translate-x-0 shadow-xl"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div>
                <!-- Brand Header -->
                <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800">
                    <Link :href="route('admin.dashboard')" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center font-black text-xl text-white shadow-lg shadow-emerald-500/20">
                            P
                        </div>
                        <div>
                            <span class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">Mas POS</span>
                            <span class="block text-[10px] font-semibold text-emerald-400 uppercase tracking-widest">Admin Backoffice</span>
                        </div>
                    </Link>
                    <button
                        @click="sidebarOpen = false"
                        class="lg:hidden text-slate-400 hover:text-white p-1"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 space-y-1.5">
                    <p class="px-3 pb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">Menu Utama</p>
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-150"
                        :class="item.active
                            ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                            : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                        </svg>
                        <span>{{ item.name }}</span>
                    </Link>

                    <div class="pt-4 pb-2 px-3">
                        <p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Aplikasi Kasir</p>
                    </div>
                    <Link
                        :href="route('pos.index')"
                        class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm text-emerald-400 hover:text-emerald-300 bg-emerald-950/40 border border-emerald-800/50 hover:bg-emerald-900/40 transition"
                    >
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Buka Mesin Kasir</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold">POS</span>
                    </Link>
                </nav>
            </div>

            <!-- User Footer Profile -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-800/50">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center font-bold text-white shrink-0 border border-slate-600">
                            {{ user?.name ? user.name.charAt(0).toUpperCase() : 'A' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-white truncate">{{ user?.name || 'Administrator' }}</p>
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-emerald-400">
                                {{ user?.role === 'admin' ? 'Super Admin' : 'Kasir' }}
                            </span>
                        </div>
                    </div>
                    <button
                        @click="logout"
                        title="Keluar"
                        class="text-slate-400 hover:text-rose-400 p-2 rounded-lg hover:bg-slate-700/60 transition"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Top Navbar -->
            <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-30 px-4 sm:px-8 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button
                        @click="sidebarOpen = true"
                        class="lg:hidden text-slate-500 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <slot name="header">
                            <h1 class="text-lg font-bold text-slate-900 tracking-tight">{{ title }}</h1>
                        </slot>
                    </div>
                </div>

                <!-- Right Navbar Actions -->
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-2 text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ currentTime }}</span>
                    </div>

                    <Link
                        :href="route('pos.index')"
                        class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Kasir POS</span>
                    </Link>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

