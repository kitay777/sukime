<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const safeRoute = (name, params = {}, fallback = '/') => {
  try { if (typeof route === 'function') { const u = route(name, params); if (typeof u === 'string' && u.length) return u } } catch(e) {}
  return fallback
}

const form = useForm({ password: '' })
const showPw = ref(false)

const submit = () => {
  if (form.processing) return
  form.post(safeRoute('password.confirm'), {
    onFinish: () => form.reset(),
    preserveScroll: true,
  })
}
</script>

<template>
  <GuestLayout>
    <Head title="パスワード確認" />

    <div class="relative isolate w-full flex items-center justify-center">
      <div class="mx-auto w-full max-w-md rounded-[28px] border-2 border-fuchsia-200/90 bg-white/90 backdrop-blur-[2px] shadow-[0_20px_60px_-20px_rgba(217,70,239,.35)] p-6 sm:p-8">
        <!-- ヘッダ -->
        <div class="text-center mb-4">
          <h1 class="text-2xl font-black text-fuchsia-700">パスワード確認</h1>
          <p class="text-sm text-gray-600">セキュア操作の前に、パスワードをもう一度入力してください。</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <InputLabel for="password" value="パスワード" class="text-gray-700" />
            <div class="relative mt-1">
              <TextInput
                :type="showPw ? 'text' : 'password'"
                id="password"
                class="block w-full rounded-2xl px-4 py-3 pr-24 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-fuchsia-400"
                v-model="form.password"
                required
                autocomplete="current-password"
                autofocus
              />
              <button type="button" @click="showPw = !showPw"
                class="absolute inset-y-0 right-2 my-auto h-9 rounded-xl px-3 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200">
                {{ showPw ? '隠す' : '表示' }}
              </button>
            </div>
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <div class="mt-4 flex items-center justify-between">
            <Link :href="safeRoute('dashboard')" class="text-sm text-gray-500 hover:underline">キャンセル</Link>
            <PrimaryButton
              class="ms-4 rounded-2xl bg-gradient-to-br from-fuchsia-500 to-pink-500 px-5 py-3 text-white font-bold shadow hover:scale-[1.02] active:scale-[.98] transition"
              :class="{ 'opacity-60': form.processing }"
              :disabled="form.processing"
            >
              確認して進む
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </GuestLayout>
</template>
