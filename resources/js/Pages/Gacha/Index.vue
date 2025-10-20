<script setup>
import { Head, usePage, useForm } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const page = usePage()
const conversations = ref(page.props.conversations ?? [])
const current = ref(conversations.value[0] ?? null)

const form = useForm({ body: '' })

const send = () => {
  if (!current.value) return
  form.post(route('messages.store', current.value.id), {
    onSuccess: () => { form.reset('body') }
  })
}

// Echo購読（任意）
onMounted(() => {
  if (current.value) {
    window.Echo.private(`chat.${current.value.id}`)
      .listen('MessageCreated', (e) => {
        // ここで受信メッセージを追記するUIへ（今回は省略 or 部分リロード活用）
      })
  }
})
</script>

<template>
  <Head title="Chat"/>
  <div class="max-w-5xl mx-auto grid grid-cols-12 gap-6 py-8">
    <aside class="col-span-4 bg-white rounded shadow p-4">
      <h2 class="font-bold mb-3">会話</h2>
      <ul>
        <li v-for="c in conversations" :key="c.id"
            @click="current=c"
            class="p-2 rounded hover:bg-gray-100 cursor-pointer"
            :class="{'bg-gray-100': c.id===current?.id}">
          <div class="text-sm text-gray-600">
            {{ c.members.map(m=>m.name).join(' / ') }}
          </div>
          <div class="text-xs text-gray-500 truncate">{{ c.last_message }}</div>
        </li>
      </ul>
    </aside>

    <main class="col-span-8 bg-white rounded shadow p-4">
      <div v-if="current">
        <div class="border rounded p-3 h-80 overflow-auto mb-3">
          <!-- メッセージ一覧は別APIや同propsで。ここでは省略 -->
          <div class="text-gray-500 text-sm">ここにメッセージを表示</div>
        </div>
        <form @submit.prevent="send" class="flex gap-2">
          <input v-model="form.body" class="flex-1 border rounded px-3 py-2" placeholder="メッセージを入力"/>
          <button class="px-4 py-2 rounded bg-emerald-600 text-white" :disabled="form.processing">送信</button>
        </form>
      </div>
      <div v-else class="text-gray-500">会話を選択してください。</div>
    </main>
  </div>
</template>
