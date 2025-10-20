<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { onMounted, onBeforeUnmount, ref, nextTick } from 'vue'

const page = usePage()
const conversation = page.props.conversation
const messages = ref(page.props.messages ?? [])
const form = useForm({ body: '' })

const scroller = ref(null)
const scrollToBottom = () => nextTick(() => {
  if (scroller.value) scroller.value.scrollTop = scroller.value.scrollHeight
})

const send = () => {
  if (!form.body.trim()) return
  form.post(route('messages.store', conversation.id), {
    onSuccess: () => { form.reset('body'); },
    preserveScroll: true,
  })
}

let channel = null
onMounted(() => {
  scrollToBottom()
  if (window.Echo) {
    channel = window.Echo.private(`chat.${conversation.id}`)
      .listen('MessageCreated', (e) => {
        messages.value.push({
          id: e.id,
          user_id: e.user_id,
          user_name: e.user_name ?? '', // broadcastWith を拡張したら使える
          body: e.body,
          created_at: e.created_at,
        })
        scrollToBottom()
      })
  }
})
onBeforeUnmount(() => {
  if (channel && window.Echo) window.Echo.leave(`private-chat.${conversation.id}`)
})
</script>

<template>
  <Head title="Chat" />
  <div class="max-w-4xl mx-auto p-4">
    <h1 class="text-xl font-semibold mb-3">
      チャット
      <span class="text-sm text-gray-500 ml-2">
        {{ conversation.members.map(m => m.name).join(' / ') }}
      </span>
    </h1>

    <div ref="scroller" class="h-[60vh] overflow-y-auto bg-white rounded shadow p-4 space-y-3">
      <div v-for="m in messages" :key="m.id"
           class="flex"
           :class="m.user_id === $page.props.auth.user.id ? 'justify-end' : 'justify-start'">
        <div class="max-w-[70%] rounded-2xl px-3 py-2"
             :class="m.user_id === $page.props.auth.user.id ? 'bg-emerald-100' : 'bg-gray-100'">
          <p class="text-sm whitespace-pre-wrap break-words">{{ m.body }}</p>
          <p class="text-[10px] text-gray-500 mt-1">{{ m.created_at }}</p>
        </div>
      </div>
    </div>

    <form @submit.prevent="send" class="mt-3 flex gap-2">
      <input v-model="form.body" class="flex-1 border rounded px-3 py-2"
             placeholder="メッセージを入力…" />
      <button class="px-4 py-2 rounded bg-emerald-600 text-white" :disabled="form.processing">
        送信
      </button>
    </form>
  </div>
</template>
