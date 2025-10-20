<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  questions: Object
})
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">質問管理</h1>
    <Link href="/admin/questions/create"
          class="bg-blue-600 text-white px-4 py-2 rounded">新規作成</Link>

    <table class="w-full mt-6 border">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2">ID</th>
          <th class="p-2">内容</th>
          <th class="p-2">レアリティ</th>
          <th class="p-2">有効</th>
          <th class="p-2">操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="q in props.questions.data" :key="q.id" class="border-t">
          <td class="p-2">{{ q.id }}</td>
          <td class="p-2">{{ q.content }}</td>
          <td class="p-2">{{ q.rarity }}</td>
          <td class="p-2">{{ q.is_active ? '✅' : '❌' }}</td>
          <td class="p-2 space-x-2">
            <Link :href="`/admin/questions/${q.id}/edit`"
                  class="text-blue-600">編集</Link>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- ページネーション -->
<div class="mt-6 flex space-x-2">
  <template v-for="link in props.questions.links" :key="link.label">
    <Link
      v-if="link.url"
      :href="link.url"
      v-html="link.label"
      class="px-3 py-1 border rounded"
      :class="{ 'bg-blue-600 text-white': link.active }"
    />
    <span
      v-else
      v-html="link.label"
      class="px-3 py-1 border rounded text-gray-400"
    />
  </template>
</div>

  </div>
</template>
