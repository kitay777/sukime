<script setup>
import { Head, Link } from '@inertiajs/vue3'

/** Ziggy が未定義 or ルート未登録でも安全に動くヘルパ */
const safeRoute = (name, params = {}, fallback = '#') => {
  try {
    if (typeof route === 'function') {
      const u = route(name, params)
      if (typeof u === 'string' && u.length) return u
    }
  } catch (e) {}
  return fallback
}
const hasRoute = (name) => {
  try {
    if (typeof route === 'function') {
      // 呼び出しで例外が出る＝未登録
      route(name)
      return true
    }
  } catch (e) { return false }
  return false
}
</script>

<template>
  <Head title="Sukime" />

  <!-- POPな明るい背景（パステル×コンフェッティ×光のグラデ） -->
  <div class="min-h-screen relative flex flex-col items-center justify-center overflow-hidden">
    <!-- 背景レイヤー -->
    <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(70%_130%_at_10%_-10%,#ffe4f2,transparent_60%),radial-gradient(90%_120%_at_110%_10%,#e6f4ff,transparent_60%),radial-gradient(120%_100%_at_50%_120%,#fff6d1,transparent_60%)]"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 opacity-25 bg-[url('/assets/patterns/confetti.svg')] animate-[pulse_6s_ease-in-out_infinite]"></div>

    <!-- 枠（カード） -->
    <div class="w-[92%] max-w-2xl rounded-[28px] border-2 border-pink-200/90 bg-white/85 backdrop-blur-[2px] shadow-[0_20px_60px_-20px_rgba(236,72,153,.35)] p-8 sm:p-10 text-center">
      <!-- ロゴ -->
      <img
        src="/assets/imgs/sukimilogo.png"
        alt="Sukime Logo"
        class="mx-auto w-[40%] max-w-[420px] drop-shadow-[0_2px_0_rgba(255,255,255,.9)]"
      />

      <!-- キャッチコピー（任意で削除可） -->
      <p class="mt-6 text-gray-700 text-base sm:text-lg font-medium">
        つながる、ひろがる。<span class="text-pink-600 font-bold">Sukime</span> で毎日をちょっとカラフルに ✨
      </p>

      <!-- ボタン群 -->
      <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6">
        <!-- ログイン -->
        <Link
          :href="route('login')"
          class="inline-flex items-center justify-center px-7 py-3.5 rounded-2xl text-white text-lg font-bold shadow-md transition
                 bg-gradient-to-br from-pink-500 to-fuchsia-500 hover:scale-[1.03] active:scale-[0.98]"
        >
          ログイン
        </Link>

        <!-- 登録 -->
        <Link
          :href="route('register')"
          class="inline-flex items-center justify-center px-7 py-3.5 rounded-2xl text-gray-800 text-lg font-bold shadow-md transition
                 bg-gradient-to-br from-amber-200 to-yellow-200 hover:scale-[1.03] active:scale-[0.98] border border-amber-300"
        >
          登録
        </Link>
      </div>

      <!-- 追加：利用規約／プライバシー（任意リンク） -->
      <div v-if="hasRoute('terms') || hasRoute('privacy')" class="mt-6 text-sm text-gray-500 flex items-center justify-center gap-4">
        <Link v-if="hasRoute('terms')" :href="safeRoute('terms')" class="hover:text-gray-700 underline-offset-4 hover:underline">利用規約</Link>
        <span v-if="hasRoute('terms') && hasRoute('privacy')">・</span>
        <Link v-if="hasRoute('privacy')" :href="safeRoute('privacy')" class="hover:text-gray-700 underline-offset-4 hover:underline">プライバシー</Link>
      </div>
    </div>

    <!-- デコレーション（キラッ） -->
    <div class="pointer-events-none absolute -top-6 left-10 h-2.5 w-2.5 rounded-full bg-pink-400/70 blur-[1px] animate-ping"></div>
    <div class="pointer-events-none absolute top-24 right-16 h-2 w-2 rounded-full bg-indigo-400/70 blur-[1px] animate-ping [animation-delay:300ms]"></div>
    <div class="pointer-events-none absolute bottom-10 left-1/3 h-2 w-2 rounded-full bg-fuchsia-400/70 blur-[1px] animate-ping [animation-delay:600ms]"></div>
  </div>
</template>
