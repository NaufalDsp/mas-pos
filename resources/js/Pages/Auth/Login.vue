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

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout>

    <Head title="Login" />
    <h2 class="text-[32px] font-semibold text-gray-900 mb-2">Selamat Datang di MASPOS</h2>
    <p class="text-[18px] text-gray-900 mb-4">
      Masuk untuk mengelola bisnis Anda dengan mudah dan efisien.
      MASPOS menghadirkan solusi point-of-sale terbaik untuk kemudahan operasional sehari-hari.
    </p>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Username -->
      <div>
        <input type="text" v-model="form.username" placeholder="Username" autocomplete="username"
          class="w-full border-b border-gray-400 bg-transparent focus:outline-none focus:border-blue-500 py-2"
          :class="{ 'border-red-500 focus:border-red-600': form.errors.username }"
          :aria-invalid="!!form.errors.username" :aria-describedby="form.errors.username ? 'username-error' : null"
          required />
        <p v-if="form.errors.username" id="username-error" class="mt-1 text-sm text-red-600">
          Username yang anda masukkan salah.
        </p>
      </div>

      <!-- Password -->
      <div class="relative">
        <input :type="showPassword ? 'text' : 'password'" v-model="form.password" placeholder="Password"
          autocomplete="current-password"
          class="w-full border-b border-gray-400 bg-transparent focus:outline-none focus:border-blue-500 py-2 pr-10"
          :class="{ 'border-red-500 focus:border-red-600': form.errors.password }"
          :aria-invalid="!!form.errors.password" :aria-describedby="form.errors.password ? 'password-error' : null"
          required />
        <button type="button" class="absolute right-0 top-1/2 -translate-y-1/2 p-3   text-gray-500 hover:text-gray-700"
          @click="showPassword = !showPassword"
          :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
          :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
          <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.036 12.322a1 1 0 010-.644C3.423 7.51 7.36 5 12 5c4.64 0 8.577 2.51 9.964 6.678a1 1 0 010 .644C20.577 16.49 16.64 19 12 19c-4.64 0-8.577-2.51-9.964-6.678z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3.98 8.223A10.477 10.477 0 001.934 12C3.32 16.168 7.258 18.678 11.897 18.678c1.74 0 3.392-.36 4.86-1.013M9.88 9.88a3 3 0 104.243 4.243M6.1 6.1l11.8 11.8M12.103 5.322c1.74 0 3.392.36 4.86 1.013a10.478 10.478 0 013.046 3.688" />
          </svg>
        </button>
        <p v-if="form.errors.password" id="password-error" class="mt-1 text-sm text-red-600">
          Password yang anda masukkan salah.
        </p>
      </div>

      <!-- Tombol -->
      <div>
        <button type="submit"
          class="w-full py-3 px-4 rounded-lg text-white font-medium bg-gradient-to-r from-blue-500 to-blue-700 hover:opacity-90 transition">
          Masuk
        </button>
      </div>
      <div class="text-gray-700">
        Belum punya akun?
        <Link href="/register" class="text-blue-600 hover:underline ml-1">Daftar di Sini</Link>
      </div>
    </form>

  </GuestLayout>
</template>
