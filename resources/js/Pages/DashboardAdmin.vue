<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';

const props = defineProps({
    auth: Object,
    stats: Object,
    chartData: Array,
    currentPeriod: String,
    topProducts: Array,
    users: Array,
    products: Array,
    transactions: Array,
});

const activeTab = ref('users');
const chartCanvas = ref(null);
let chartInstance = null;
const loading = ref(false);
const selectedPeriod = ref(props.currentPeriod || 'day');

const periodOptions = [
    { value: 'day', label: 'Hari', description: '7 Hari Terakhir' },
    { value: 'week', label: 'Minggu', description: '8 Minggu Terakhir' },
    { value: 'month', label: 'Bulan', description: '12 Bulan Terakhir' },
    { value: 'year', label: 'Tahun', description: '5 Tahun Terakhir' },
];

const tabs = [
    { id: 'users', name: 'Daftar User', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
    { id: 'products', name: 'Daftar Produk', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
    { id: 'transactions', name: 'Transaksi', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
];

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const changePeriod = (period) => {
    if (selectedPeriod.value === period) return;

    selectedPeriod.value = period;
    loading.value = true;

    router.get(route('admin.dashboard'), { period: period }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            nextTick(() => {
                initChart();
            });
        },
        onError: () => {
            loading.value = false;
        }
    });
};

const initChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }

    const ctx = chartCanvas.value?.getContext('2d');
    if (!ctx) return;

    const labels = props.chartData.map(item => item.label);
    const revenueData = props.chartData.map(item => parseFloat(item.revenue) || 0);
    const transactionData = props.chartData.map(item => parseInt(item.transactions) || 0);

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue (Rp)',
                    data: revenueData,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: 'Transaksi',
                    data: transactionData,
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        title: function (context) {
                            const index = context[0].dataIndex;
                            return props.chartData[index].fullLabel;
                        },
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 0) {
                                label += formatCurrency(context.parsed.y);
                            } else {
                                label += context.parsed.y + ' transaksi';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                    },
                    ticks: {
                        callback: function (value) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                            } else if (value >= 1000) {
                                return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                            }
                            return 'Rp ' + value;
                        },
                        font: {
                            size: 11
                        }
                    },
                    title: {
                        display: true,
                        text: 'Revenue',
                        color: 'rgb(59, 130, 246)',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        stepSize: 1,
                    },
                    title: {
                        display: true,
                        text: 'Jumlah Transaksi',
                        color: 'rgb(34, 197, 94)',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
};

watch(() => props.chartData, () => {
    if (chartInstance) {
        nextTick(() => {
            initChart();
        });
    }
}, { deep: true });

onMounted(() => {
    nextTick(() => {
        initChart();
    });
});
</script>

<template>

    <Head title="Dashboard Admin" />

    <div class="p-6 bg-gray-100 min-h-screen">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Dashboard Admin</h1>
                <p class="text-gray-600 mt-1">Selamat datang, <span class="font-semibold">{{ auth.user.name }}</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('home')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center gap-2 shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                POS Home
                </Link>
                <Link :href="route('admin.logout')" method="post" as="button"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition flex items-center gap-2 shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
                </Link>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Users</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total_users }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Products -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Produk</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total_products }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Transaksi</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total_transactions }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatCurrency(stats.total_revenue) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Traffic Pendapatan</h3>
                    <p class="text-sm text-gray-500 mt-1">Grafik pendapatan dan transaksi</p>
                </div>

                <!-- Chart Canvas -->
                <div class="h-80 relative">
                    <canvas ref="chartCanvas"></canvas>
                    <div v-if="loading"
                        class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <p class="text-sm text-gray-600 mt-2 font-medium">Loading...</p>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex items-center justify-center gap-6 mt-4 pt-4 border-t">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                        <span class="text-sm text-gray-600 font-medium">Revenue</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                        <span class="text-sm text-gray-600 font-medium">Transaksi</span>
                    </div>
                </div>

                <!-- Period Filter -->
                <div class="mt-6 pt-4 border-t">
                    <p class="text-xs text-gray-500 mb-3 font-semibold uppercase tracking-wide">Filter Periode:</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button v-for="option in periodOptions" :key="option.value" @click="changePeriod(option.value)"
                            :disabled="loading"
                            class="px-4 py-3 text-sm font-medium rounded-lg transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                            :class="selectedPeriod === option.value
                                ? 'bg-blue-600 text-white shadow-md'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                            <div class="flex flex-col items-center">
                                <span class="font-bold">{{ option.label }}</span>
                                <span class="text-xs mt-1"
                                    :class="selectedPeriod === option.value ? 'opacity-90' : 'opacity-70'">{{
                                        option.description }}</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 5 Produk Terlaris</h3>
                <div class="space-y-4">
                    <div v-for="(product, index) in topProducts" :key="product.id"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold text-sm">
                                {{ index + 1 }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ product.name }}</p>
                                <p class="text-sm text-gray-500">{{ product.category?.name || 'No Category' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">{{ product.total_sold || 0 }}</p>
                            <p class="text-xs text-gray-500">Terjual</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-xl shadow mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                        class="group inline-flex items-center px-6 py-4 border-b-2 font-medium text-sm transition"
                        :class="activeTab === tab.id
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                        <svg class="mr-2 h-5 w-5" :class="activeTab === tab.id ? 'text-blue-600' : 'text-gray-400'"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                        </svg>
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Users Tab -->
                <div v-if="activeTab === 'users'">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Bergabung</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                        user.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{
                                        formatDate(user.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Products Tab -->
                <div v-if="activeTab === 'products'">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama
                                        Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                        product.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{
                                        product.category?.name || '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                        formatCurrency(product.price) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span
                                            :class="product.stock <= 10 ? 'text-red-600 font-semibold' : 'text-gray-900'">
                                            {{ product.stock }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Transactions Tab -->
                <div v-if="activeTab === 'transactions'">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ transaction.id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                        transaction.user?.name || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ formatCurrency(transaction.total) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ transaction.items?.length || 0 }} item(s)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(transaction.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>