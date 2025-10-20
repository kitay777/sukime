<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps({
  question: Object,
  answered_count: Number,
})

const form = useForm({
  question_id: props.question.id,
  answer: '',
})

// 質問が切り替わったら state を更新してリセット
watch(() => props.question.id, (newId) => {
  form.question_id = newId
  form.answer = ''
})

const submit = () => {
  form.post(route('questions.store'))
}
</script>

<template>
  <div class="p-6">
    <h2 class="text-lg font-bold mb-4">
      初回質問 {{ answered_count + 1 }} / 10
    </h2>

    <p class="mb-4">{{ question.content }}</p>
    <form @submit.prevent="submit" class="space-y-4">
      <textarea v-model="form.answer" class="w-full border rounded p-2" rows="3"/>
      <input type="hidden" name="question_id" :value="form.question_id" />
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
        回答する
      </button>
    </form>
  </div>
</template>
