<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { reactive, onMounted, onBeforeUnmount, ref } from 'vue'

const props = defineProps({
  rarity:   { type: String,  required: true },
  isWin:    { type: Boolean, required: true },
  favorite: { type: Object,  default: null },
  balance:  { type: Number,  default: 0 },
  qa:       { type: Object,  default: null }, // ★ 追加: {question_text, answer_text, answer_user}
})

/* 表示用マップ */
const LABEL = { normal:'ノーマル', rare:'レア', super_rare:'スーパーレア', ultra_rare:'ウルトラレア', secret:'シークレット' }
const COLOR = { normal:'text-gray-800', rare:'text-indigo-600', super_rare:'text-emerald-600', ultra_rare:'text-orange-600', secret:'text-pink-600' }
const SYMBOL = { normal:'✨', rare:'🌟', super_rare:'💫', ultra_rare:'🔥', secret:'💖' }

/* リール候補 & 状態 */
const ReelSymbols = ['✨', '⭐️', '💫', '🔥', '💎', '🧩', '🎈', '🍀', '⚡️', '💖']
const reels = reactive([
  { spinning: true,  index: 0, timer: null },
  { spinning: true,  index: 3, timer: null },
  { spinning: true,  index: 6, timer: null },
])
const resultSymbol = SYMBOL[props.rarity] ?? '✨'
const startReel = (i, speed = 60) => { stopReel(i); reels[i].spinning = true; reels[i].timer = setInterval(()=>{ reels[i].index = (reels[i].index+1)%ReelSymbols.length }, speed) }
const stopReel  = (i) => { if (reels[i].timer) clearInterval(reels[i].timer); reels[i].timer = null; reels[i].spinning=false; const t=ReelSymbols.indexOf(resultSymbol); reels[i].index = t>=0?t:0 }

const showQA = ref(false) // ★ リール停止後に表示
onMounted(() => {
  startReel(0,55); startReel(1,65); startReel(2,75)
  setTimeout(()=>stopReel(0), 900)
  setTimeout(()=>stopReel(1), 1500)
  setTimeout(()=>{ stopReel(2); showQA.value = true }, 2100) // 最後にQ&Aを出す
})
onBeforeUnmount(()=> { reels.forEach((_,i)=>stopReel(i)) })

const playAgainHref = route('gacha.play') // GETのまま
</script>

<template>
  <Head title="Gacha Result - Sukime" />

  <div class="min-h-dvh flex items-center justify-center p-6 bg-gradient-to-b from-white via-white to-pink-50/40">
    <div class="w-full max-w-3xl rounded-2xl border border-gray-100 bg-white p-6 sm:p-8 shadow-xl relative overflow-hidden">
      <div class="pointer-events-none absolute -top-24 -right-10 h-56 w-56 rounded-full bg-pink-200/30 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-28 -left-16 h-64 w-64 rounded-full bg-indigo-200/30 blur-3xl"></div>

      <div class="relative">
        <h1 class="text-2xl font-bold text-gray-800">恋ガチャ 結果</h1>
        <p class="text-sm text-gray-500 mt-1">スロットが止まるまでお楽しみください…</p>

        <!-- リール -->
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div v-for="(reel, i) in reels" :key="i" class="relative h-44 rounded-2xl border border-gray-200 bg-gray-50/70 overflow-hidden flex items-center justify-center">
            <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_bottom,rgba(255,255,255,0.9),transparent_20%,transparent_80%,rgba(255,255,255,0.9))]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-1/2 -translate-y-1/2 h-[2px] bg-gradient-to-r from-transparent via-pink-400/60 to-transparent"></div>
            <div class="text-7xl leading-none select-none transition-transform duration-100 will-change-transform"
                 :class="reel.spinning ? 'scale-100 opacity-100' : 'scale-[1.08] opacity-100'">
              {{ ReelSymbols[reel.index] }}
            </div>
          </div>
        </div>

        <!-- 結果 -->
        <div class="mt-8 text-center">
          <div class="text-sm text-gray-500">本日のレアリティ</div>
          <div class="mt-1 text-3xl font-extrabold" :class="COLOR[props.rarity]">
            <span class="mr-2">{{ SYMBOL[props.rarity] }}</span>
            {{ LABEL[props.rarity] }}
          </div>

          <div v-if="props.isWin" class="mt-6 rounded-2xl border border-pink-200 bg-pink-50 p-5">
            <p class="text-pink-700 font-semibold">両想い成立！🎉</p>
            <p class="text-sm text-pink-700/90 mt-1">
              相手：<span class="font-medium">{{ props.favorite?.name ?? '非公開' }}</span>
            </p>
          </div>
        </div>

        <!-- ★ Q&A 表示（停止後にフェードイン） -->
        <transition name="fade" appear>
          <div v-if="showQA" class="mt-8">
<!-- Q&A 表示 -->
<div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
  <div class="text-xs font-semibold text-gray-500 mb-2">レア度に応じた質問</div>

  <!-- 質問 -->
  <div class="text-gray-900 text-lg font-bold">
    {{ props.qa?.question_text ?? '（該当する質問が見つかりませんでした）' }}
  </div>

  <!-- 回答 -->
  <div v-if="props.qa?.answer" class="mt-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
    <div class="text-xs text-gray-500 mb-1">相手の回答</div>
    <p class="text-gray-800 whitespace-pre-line">{{ props.qa.answer }}</p>
    <p class="mt-2 text-xs text-gray-500 text-right">
      — {{ props.qa?.answer_user?.name ?? '匿名' }}
    </p>
  </div>
  <div v-else class="mt-4 text-sm text-gray-500">
    この質問の回答はまだありません。
  </div>
</div>


            <!-- 残高/再挑戦導線 -->
            <div class="mt-6 flex flex-col items-center gap-2">
              <div class="text-sm text-gray-600">
                現在のポイント：<span class="font-bold text-emerald-600">{{ props.balance.toLocaleString() }} pt</span>
              </div>
              <div class="flex flex-wrap gap-3 justify-center">
                <Link :href="route('dashboard')" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-gray-800 hover:bg-gray-50">
                  ⬅ ダッシュボードへ戻る
                </Link>
                <Link :href="route('gacha.play', { paid: 1 })" class="inline-flex items-center gap-2 rounded-xl bg-pink-500 px-4 py-2 text-white shadow hover:bg-pink-600">
                  🎰 もう一度（100pt）
                </Link>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<style>
.fade-enter-from, .fade-leave-to { opacity: 0 }
.fade-enter-active, .fade-leave-active { transition: opacity .25s ease }
</style>
