<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  questions: Array,
  answered: Number,
});

const form = useForm({
  answers: props.questions.map(q => ({ question_id: q.id, answer: '' })),
});

const submit = () => {
  form.answers.forEach(ans => {
    form.post(route('questions.store'), ans, { preserveScroll: true });
  });
};
</script>

<template>
  <AuthenticatedLayout>
    <div class="max-w-2xl mx-auto py-12">
      <h2 class="text-xl font-bold mb-6">プロフィール質問に答えてください</h2>

      <div v-for="(q,i) in props.questions" :key="q.id" class="mb-4">
        <p class="font-medium mb-1">{{ i+1 }}. {{ q.content }}</p>
        <input v-model="form.answers[i].answer"
               class="w-full border rounded p-2"
               type="text" />
      </div>

      <button @click="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
        送信
      </button>
    </div>
  </AuthenticatedLayout>
</template>
