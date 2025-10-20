<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Sukime" />

  <div
    class="min-h-screen flex flex-col items-center justify-center bg-white dark:bg-white text-gray-800 dark:text-white"
  >
    <!-- ロゴ -->
    <img
      src="/assets/imgs/sukimilogo.png"
      alt="Sukime Logo"
      class="w-[30%] max-w-[640px] mb-10"
    />

    <!-- ボタン -->
    <div class="flex gap-6">
      <Link
        :href="route('login')"
        class="px-6 py-3 bg-pink-500 hover:bg-pink-600 text-white rounded-full text-lg font-semibold shadow-md transition"
      >
        ログイン
      </Link>
    </div>
    <div class="flex gap-6">
      <Link
        :href="route('register')"
        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-full text-lg font-semibold shadow-md transition"
      >
        登録
      </Link>
    </div>
  </div>
</template>
