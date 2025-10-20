<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const showPw = ref(false)
const showPw2 = ref(false)

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <GuestLayout>
    <Head title="新規登録" />

    <div class="relative isolate w-full flex items-center justify-center">
      <div
        class="mx-auto w-full max-w-md rounded-[28px] border-2 border-indigo-200/90 bg-white/90 backdrop-blur-[2px] shadow-[0_20px_60px_-20px_rgba(99,102,241,.35)] p-6 sm:p-8"
      >
        <!-- ロゴ押下でWelcomeへ戻る -->
        <div class="text-center mb-4">
          <Link :href="route('welcome')" class="inline-block transition hover:scale-105 active:scale-95">
            <img
              src="/assets/imgs/sukimilogo.png"
              alt="Sukime"
              class="mx-auto w-36 drop-shadow-[0_2px_0_rgba(255,255,255,.9)]"
            />
          </Link>
          <h1 class="mt-3 text-2xl font-black text-indigo-700">新規登録</h1>
          <p class="text-sm text-gray-600">ようこそ！1分でスタート 🚀</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <InputLabel for="name" value="お名前" class="text-gray-700" />
            <TextInput
              id="name"
              type="text"
              class="mt-1 block w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
              v-model="form.name"
              required
              autofocus
              autocomplete="name"
            />
            <InputError class="mt-2" :message="form.errors.name" />
          </div>

          <div>
            <InputLabel for="email" value="メールアドレス" class="text-gray-700" />
            <TextInput
              id="email"
              type="email"
              class="mt-1 block w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
              v-model="form.email"
              required
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
                class="block w-full rounded-2xl px-4 py-3 pr-24 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                v-model="form.password"
                required
                autocomplete="new-password"
              />
              <button
                type="button"
                @click="showPw = !showPw"
                class="absolute inset-y-0 right-2 my-auto h-9 rounded-xl px-3 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200"
              >
                {{ showPw ? '隠す' : '表示' }}
              </button>
            </div>
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <div>
            <InputLabel for="password_confirmation" value="パスワード（確認）" class="text-gray-700" />
            <div class="relative mt-1">
              <TextInput
                :type="showPw2 ? 'text' : 'password'"
                id="password_confirmation"
                class="block w-full rounded-2xl px-4 py-3 pr-24 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                v-model="form.password_confirmation"
                required
                autocomplete="new-password"
              />
              <button
                type="button"
                @click="showPw2 = !showPw2"
                class="absolute inset-y-0 right-2 my-auto h-9 rounded-xl px-3 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200"
              >
                {{ showPw2 ? '隠す' : '表示' }}
              </button>
            </div>
            <InputError class="mt-2" :message="form.errors.password_confirmation" />
          </div>

          <div class="flex items-center justify-between mt-4">
            <Link
              :href="route('login')"
              class="text-sm text-pink-600 hover:underline font-semibold"
            >
              すでに登録済みの方
            </Link>

            <PrimaryButton
              class="ms-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 px-5 py-3 text-white font-bold shadow hover:scale-[1.02] active:scale-[.98] transition"
              :class="{ 'opacity-60': form.processing }"
              :disabled="form.processing"
            >
              登録する
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </GuestLayout>
</template>
