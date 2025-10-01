<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
  email: '',
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

    <h2 class="text-xl font-semibold text-gray-800 mb-2">Selamat Datang di MASPOS</h2>
    <p class="text-sm text-gray-600 mb-6">
      Masuk untuk mengelola bisnis Anda dengan mudah dan efisien.
      MASPOS menghadirkan solusi point-of-sale terbaik untuk kemudahan operasional sehari-hari.
    </p>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Username -->
      <div>
        <input type="email" v-model="form.email" placeholder="Username"
          class="w-full border-b border-gray-400 bg-transparent focus:outline-none focus:border-blue-500 py-2"
          required />
      </div>

      <!-- Password -->
      <div>
        <input type="password" v-model="form.password" placeholder="Password"
          class="w-full border-b border-gray-400 bg-transparent focus:outline-none focus:border-blue-500 py-2"
          required />
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
