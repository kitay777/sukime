<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

defineProps({ status: { type: String } })

// Ziggy が無くても安全に
const safeRoute = (name, params = {}, fallback = '/') => {
  try { if (typeof route === 'function') { const u = route(name, params); if (typeof u === 'string' && u.length) return u } } catch(e) {}
  return fallback
}

const form = useForm({ email: '' })
const submit = () => {
  form.post(safeRoute('password.email'))
}
</script>

<template>
  <GuestLayout>
    <Head title="パスワード再設定" />

    <div class="relative isolate w-full flex items-center justify-center">
      <div class="mx-auto w-full max-w-md rounded-[28px] border-2 border-violet-200/90 bg-white/90 backdrop-blur-[2px] shadow-[0_20px_60px_-20px_rgba(139,92,246,.35)] p-6 sm:p-8">
        <!-- ロゴ：Welcomeへ -->
        <div class="text-center mb-4">
          <Link :href="safeRoute('welcome')" class="inline-block transition hover:scale-105 active:scale-95">
            <img src="/assets/imgs/sukimilogo.png" alt="Sukime" class="mx-auto w-32 drop-shadow-[0_2px_0_rgba(255,255,255,.9)]" />
          </Link>
          <h1 class="mt-3 text-2xl font-black text-violet-700">パスワードをお忘れですか？</h1>
          <p class="text-sm text-gray-600">メールアドレス宛に再設定リンクをお送りします。</p>
        </div>

        <div class="mb-4 text-sm text-gray-600">
          ご登録のメールアドレスを入力してください。再設定用のリンクをお送りします。
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl p-3">
          {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <InputLabel for="email" value="メールアドレス" class="text-gray-700" />
            <TextInput
              id="email"
              type="email"
              class="mt-1 block w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-violet-400"
              v-model="form.email"
              required
              autofocus
              autocomplete="username"
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <div class="mt-4 flex items-center justify-between">
            <Link :href="safeRoute('login')" class="text-sm text-pink-600 hover:underline font-semibold">ログインへ戻る</Link>
            <PrimaryButton
              :class="{ 'opacity-60': form.processing }"
              :disabled="form.processing"
              class="rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 px-5 py-3 text-white font-bold shadow hover:scale-[1.02] active:scale-[.98] transition"
            >
              再設定リンクを送信
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </GuestLayout>
</template>
