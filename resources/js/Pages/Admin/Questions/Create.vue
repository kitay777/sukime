<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  content: '',
  rarity: 'normal',
  is_active: true,
  sort_order: 0,
})

const submit = () => form.post(route('admin.questions.store'))
</script>

<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">新しい質問を作成</h1>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block mb-1">質問内容</label>
        <input v-model="form.content" class="border rounded w-full p-2" />
      </div>

      <div>
        <label class="block mb-1">レアリティ</label>
        <select v-model="form.rarity" class="border rounded w-full p-2">
          <option value="normal">ノーマルレア (53%)</option>
          <option value="rare">レア (30%)</option>
          <option value="super_rare">スーパーレア (15%)</option>
          <option value="ultra_rare">ウルトラレア (2%)</option>
          <option value="secret">シークレットレア (0.1%)</option>
        </select>
      </div>


      <div>
        <label><input type="checkbox" v-model="form.is_active" /> 有効</label>
      </div>

      <div>
        <label class="block mb-1">並び順</label>
        <input v-model="form.sort_order" type="number" class="border rounded w-full p-2" />
      </div>

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
        保存
      </button>
    </form>
  </div>
</template>
