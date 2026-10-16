<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { ref } from 'vue';

const form = useForm({
  username: '',
  password: '',
  remember: false,
});

const showPassword = ref(false);

const fillAdmin = () => {
  form.username = 'admin@maspos.com';
  form.password = 'password';
};

const fillKasir = () => {
  form.username = 'kasir@maspos.com';
  form.password = 'password';
};

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Login - Mas POS" />

    <div class="space-y-6 max-w-md w-full">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-3">
          <span>Point of Sale v2.0</span>
        </div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tight">Selamat Datang di Mas POS</h2>
        <p class="text-sm text-slate-500 mt-1">
          Masuk ke akun Anda untuk mengakses Terminal Kasir atau Dashboard Manajemen Admin.
        </p>
      </div>

      <!-- Quick Demo Login Presets -->
      <div class="p-3.5 rounded-2xl bg-slate-100/80 border border-slate-200 text-xs space-y-2">
        <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Demo Akun Siap Pakai:</span>
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="fillAdmin"
            class="flex-1 py-1.5 px-2.5 rounded-xl bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 font-bold transition text-[11px]"
          >
            🛡️ Akun Admin
          </button>
          <button
            type="button"
            @click="fillKasir"
            class="flex-1 py-1.5 px-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 font-bold transition text-[11px]"
          >
            🛒 Akun Kasir
          </button>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Username / Email -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Email atau Username</label>
          <input
            type="text"
            v-model="form.username"
            placeholder="admin@maspos.com atau kasir@maspos.com"
            autocomplete="username"
            class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
            :class="{ 'border-rose-500': form.errors.username }"
            required
          />
          <p v-if="form.errors.username" class="mt-1 text-xs text-rose-500">
            {{ form.errors.username }}
          </p>
        </div>

        <!-- Password -->
        <div class="relative">
          <div class="flex items-center justify-between mb-1">
            <label class="text-xs font-bold text-slate-700">Kata Sandi</label>
          </div>
          <div class="relative">
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              placeholder="Masukkan kata sandi..."
              autocomplete="current-password"
              class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition pr-10"
              :class="{ 'border-rose-500': form.errors.password }"
              required
            />
            <button
              type="button"
              class="absolute right-0 top-1/2 -translate-y-1/2 p-2.5 text-slate-400 hover:text-slate-600"
              @click="showPassword = !showPassword"
              tabindex="-1"
            >
              <svg v-if="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
              </svg>
            </button>
          </div>
          <p v-if="form.errors.password" class="mt-1 text-xs text-rose-500">
            {{ form.errors.password }}
          </p>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3 px-4 rounded-xl text-white font-extrabold text-xs bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition disabled:opacity-50"
          >
            {{ form.processing ? 'Memverifikasi...' : 'Masuk ke Sistem &rarr;' }}
          </button>
        </div>
      </form>
    </div>
  </GuestLayout>
</template>
