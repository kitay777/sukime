<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
const page = usePage()
const balance = page.props.balance ?? 0
const history = page.props.history ?? []
const amount = ref(1000)

const checkout = async () => {
  const res = await fetch(route('points.checkout'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    body: JSON.stringify({ amount_yen: Number(amount.value) }),
  })
  const data = await res.json()
  window.location.href = data.url
}
</script>

<template>
  <AuthenticatedLayout>
  <Head title="ポイント" />
  <div class="max-w-3xl mx-auto p-6 space-y-6">
    <div class="bg-white p-4 rounded shadow">
      <h2 class="text-lg font-semibold mb-2">残高</h2>
      <div class="text-3xl font-bold">{{ balance.toLocaleString() }} pt</div>
    </div>

    <div class="bg-white p-4 rounded shadow space-y-3">
      <h3 class="font-semibold">チャージ</h3>
      <div class="flex items-center gap-2">
        <input type="number" min="100" step="100" v-model="amount" class="border rounded px-3 py-2 w-40" />
        <button @click="checkout" class="px-4 py-2 rounded bg-emerald-600 text-white">Stripeで購入</button>
      </div>
      <p class="text-xs text-gray-500">100円以上。1円=1ポイント</p>
    </div>

    <div class="bg-white p-4 rounded shadow">
      <h3 class="font-semibold mb-2">履歴（最新30）</h3>
      <ul class="text-sm divide-y">
        <li v-for="h in history" :key="h.id" class="py-2 flex justify-between">
          <span>{{ h.type }} - {{ h.reason ?? '' }} - {{ new Date(h.created_at).toLocaleString() }}</span>
          <span :class="h.amount >=0 ? 'text-emerald-600' : 'text-rose-600'">
            {{ h.amount >= 0 ? '+' : '' }}{{ h.amount }}
          </span>
        </li>
      </ul>
    </div>
  </div>
  </AuthenticatedLayout>
</template>
