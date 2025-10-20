<script setup>
import Checkbox from '@/Components/Checkbox.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  canResetPassword: { type: Boolean },
  status: { type: String },
})

const form = useForm({ email: '', password: '', remember: false })
const showPw = ref(false)

const submit = () => {
  form.post(route('login'), { onFinish: () => form.reset('password') })
}
</script>

<template>
  <GuestLayout>
    <Head title="ログイン" />

    <!-- === POP背景（GuestLayoutの中でも視覚効果） === -->
    <div class="relative isolate">
      <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(70%_130%_at_10%_-10%,#ffe4f2,transparent_60%),radial-gradient(90%_120%_at_110%_10%,#e6f4ff,transparent_60%),radial-gradient(120%_100%_at_50%_120%,#fff6d1,transparent_60%)]"></div>
      <div class="pointer-events-none absolute inset-0 -z-10 opacity-25 bg-[url('/assets/patterns/confetti.svg')] animate-[pulse_6s_ease-in-out_infinite]"></div>

      <!-- カード -->
      <div class="mx-auto w-full max-w-md rounded-[28px] border-2 border-pink-200/90 bg-white/90 backdrop-blur-[2px] shadow-[0_20px_60px_-20px_rgba(236,72,153,.35)] p-6 sm:p-8">
        <!-- ロゴ（必要なら表示） -->
        <div class="text-center mb-4">
                 
          <Link :href="route('welcome')" class="inline-block transition hover:scale-105 active:scale-95">
            <img
              src="/assets/imgs/sukimilogo.png"
              alt="Sukime"
              class="mx-auto w-36 drop-shadow-[0_2px_0_rgba(255,255,255,.9)]"
            />
          </Link>
          <h1 class="mt-3 text-2xl font-black text-pink-700">ログイン</h1>
          <p class="text-sm text-gray-600">おかえりなさい ✨</p>
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
              class="mt-1 block w-full rounded-2xl px-4 py-3 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-pink-400"
              v-model="form.email"
              required
              autofocus
              autocomplete="username"
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <div>
            <InputLabel for="password" value="パスワード" class="text-gray-700" />
            <div class="relative mt-1">
              <TextInput
                :type="showPw ? 'text' : 'password'"
                id="password"
                class="block w-full rounded-2xl px-4 py-3 pr-24 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-pink-400"
                v-model="form.password"
                required
                autocomplete="current-password"
              />
              <button type="button" @click="showPw = !showPw"
                class="absolute inset-y-0 right-2 my-auto h-9 rounded-xl px-3 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200">
                {{ showPw ? '隠す' : '表示' }}
              </button>
            </div>
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
              <Checkbox name="remember" v-model:checked="form.remember" />
              <span>ログイン状態を保持</span>
            </label>
            <Link
              v-if="canResetPassword"
              :href="route('password.request')"
              class="text-sm text-indigo-600 hover:underline"
            >
              パスワードをお忘れですか？
            </Link>
          </div>

          <div class="flex items-center justify-between">
            <Link :href="route('register')" class="text-sm text-pink-600 hover:underline font-semibold">
              新規登録へ
            </Link>
            <PrimaryButton
              class="ms-4 rounded-2xl bg-gradient-to-br from-pink-500 to-fuchsia-500 px-5 py-3 text-white font-bold shadow hover:scale-[1.02] active:scale-[.98] transition"
              :class="{ 'opacity-60': form.processing }"
              :disabled="form.processing"
            >
              ログイン
            </PrimaryButton>
          </div>
        </form>
      </div>

      <!-- デコ粒（お好みで） -->
      <div class="pointer-events-none absolute -top-6 left-10 h-2.5 w-2.5 rounded-full bg-pink-400/70 blur-[1px] animate-ping"></div>
      <div class="pointer-events-none absolute top-24 right-16 h-2 w-2 rounded-full bg-indigo-400/70 blur-[1px] animate-ping [animation-delay:300ms]"></div>
      <div class="pointer-events-none absolute bottom-10 left-1/3 h-2 w-2 rounded-full bg-fuchsia-400/70 blur-[1px] animate-ping [animation-delay:600ms]"></div>
    </div>
  </GuestLayout>
</template>
