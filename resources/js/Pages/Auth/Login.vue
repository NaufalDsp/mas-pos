<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
  username: '',
  password: '',
  remember: false,
});

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
      <div>
        <input type="password" v-model="form.password" placeholder="Password" autocomplete="current-password"
          class="w-full border-b border-gray-400 bg-transparent focus:outline-none focus:border-blue-500 py-2"
          :class="{ 'border-red-500 focus:border-red-600': form.errors.password }"
          :aria-invalid="!!form.errors.password" :aria-describedby="form.errors.password ? 'password-error' : null"
          required />
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
