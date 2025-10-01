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

    <h2 class="text-xl font-semibold text-gray-800 mb-4">Selamat Datang di MASPOS</h2>
    <p class="text-sm text-gray-600 mb-6">
      Masuk untuk mengelola bisnis Anda dengan mudah dan efisien.  
      MASPOS menghadirkan solusi point-of-sale terbaik untuk kemudahan operasional sehari-hari.
    </p>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Username -->
      <div>
        <input
          type="email"
          v-model="form.email"
          placeholder="Username"
          class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-300"
          required
        />
      </div>

      <!-- Password -->
      <div>
        <input
          type="password"
          v-model="form.password"
          placeholder="Password"
          class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-300"
          required
        />
      </div>

      <!-- Tombol -->
      <div>
        <button
          type="submit"
          class="w-full py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          Masuk
        </button>
      </div>
    </form>
  </GuestLayout>
</template>
