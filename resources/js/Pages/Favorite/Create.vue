<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
  users: Array,
  favorite_user_id: Number,
  favorite_set_at: String,
});

const form = useForm({
  favorite_user_id: props.favorite_user_id ?? '',
});

const submit = () => form.post(route('favorite.store'));
</script>

<template>
  <Head title="好きな人を選ぶ" />
  <AuthenticatedLayout>
    <div class="max-w-2xl mx-auto py-12">
      <h2 class="text-xl font-bold mb-6">好きな人を1人選んでください</h2>

      <form @submit.prevent="submit" class="space-y-4">
        <select v-model="form.favorite_user_id" class="w-full border rounded p-2">
          <option value="">選択してください</option>
          <option v-for="u in props.users" :key="u.id" :value="u.id">
            {{ u.real_name ?? u.name }}
          </option>
        </select>

        <div v-if="form.errors.favorite_user_id" class="text-red-500 text-sm">
          {{ form.errors.favorite_user_id }}
        </div>

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
          登録
        </button>
      </form>

      <p v-if="props.favorite_set_at" class="text-gray-600 mt-4">
        最終更新: {{ new Date(props.favorite_set_at).toLocaleString() }}
      </p>
    </div>
  </AuthenticatedLayout>
</template>
