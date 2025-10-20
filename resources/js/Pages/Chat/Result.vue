<script setup>
import { ref, onMounted } from "vue"

const props = defineProps({
  rarity: String,
  isWin: Boolean,
  favorite: Object,
})

const symbols = ["🍎","🍒","🍇","⭐","💎","💖"]

const reels = ref([[],[],[]]) // 各リールの表示内容
const spinning = ref(true)

// 停止後の出目
const result = ref(["","",""])

// スロット開始
onMounted(() => {
  // 各リールに繰り返しシンボルをセット
  reels.value = [symbols.concat(symbols), symbols.concat(symbols), symbols.concat(symbols)]

  // 2.5秒後に停止
  setTimeout(() => {
    if (props.rarity === "normal") result.value = ["🍎","🍒","🍎"]
    if (props.rarity === "rare") result.value = ["⭐","🍒","⭐"]
    if (props.rarity === "super_rare") result.value = ["💎","⭐","💎"]
    if (props.rarity === "ultra_rare") result.value = ["💖","💎","💖"]
    if (props.rarity === "secret") result.value = ["💖","💖","💖"]

    spinning.value = false
  }, 2500)
})
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-pink-100">
    <h1 class="text-3xl font-bold mb-6">🎰 恋ガチャ 🎰</h1>

    <!-- スロット本体 -->
    <div class="flex space-x-4 text-6xl font-bold mb-6 overflow-hidden">
      <div v-for="(r, i) in reels" :key="i" class="w-20 h-20 flex items-center justify-center bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- 回転中 -->
        <div v-if="spinning" class="animate-spin-slow flex flex-col">
          <span v-for="s in r" :key="s">{{ s }}</span>
        </div>
        <!-- 停止後 -->
        <span v-else>{{ result[i] }}</span>
      </div>
    </div>

    <!-- 結果表示 -->
    <div v-if="!spinning" class="text-center mt-6">
      <div v-if="rarity === 'normal'" class="text-gray-600 text-xl">ハズレ…😢</div>
      <div v-else-if="rarity === 'rare'" class="text-blue-500 text-xl">レア！✨ 好感度UP</div>
      <div v-else-if="rarity === 'super_rare'" class="text-purple-500 text-xl">スーパーレア！💎</div>
      <div v-else-if="rarity === 'ultra_rare'" class="text-red-500 text-2xl font-bold">ウルトラレア！！🔥</div>
      <div v-else-if="rarity === 'secret'" class="text-pink-600 text-3xl font-extrabold">
        シークレットレア！！ 💖
        <div v-if="isWin" class="mt-4">
          両想い成立！！ {{ favorite?.name }} さんと両想いになりました 🎉
        </div>
        <div v-else class="mt-4">
          惜しい！まだ片想い…💔
        </div>
      </div>
    </div>

    <a href="/dashboard" class="mt-8 px-4 py-2 bg-blue-600 text-white rounded">ダッシュボードへ戻る</a>
  </div>
</template>

<style>
/* スロット回転アニメーション */
@keyframes slotSpin {
  0% { transform: translateY(0); }
  100% { transform: translateY(-100%); }
}
.animate-spin-slow {
  animation: slotSpin 0.5s linear infinite;
}
</style>
