<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="ポイント購入 完了" />
  <div class="max-w-xl mx-auto p-6 bg-white rounded shadow text-center space-y-4">
    <h1 class="text-xl font-semibold">ポイント購入が完了しました</h1>
    <p>反映には数秒かかることがあります（Webhook 経由）。</p>
    <Link href="/points" class="px-4 py-2 rounded bg-emerald-600 text-white">ポイント画面へ</Link>
  </div>
</template>
