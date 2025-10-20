<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { usePage, Head, Link, useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const page = usePage()

// ===== Props / State =====
const crush          = computed(() => page.props?.crush ?? null)
const oshiList       = computed(() => page.props?.oshi_list ?? [])
const canChangeCrush = computed(() => page.props?.can_change_crush ?? true)
const nextChangeDate = computed(() => page.props?.next_change_date ?? '')
const fansCount      = computed(() => page.props?.fans_count ?? 0)
const oshiMeCount    = computed(() => page.props?.oshi_me_count ?? 0)
const balance        = computed(() => page.props?.balance ?? 0)

const showCrushModal = ref(false)
const showOshiModal  = ref(false)

const blankPerson = () => ({ name: '', school: '', faculty: '', grade: '', gender: '' })

// ===== Crush =====
const crushForm = useForm(blankPerson())
const openCrush = () => {
  if (crush.value) {
    crushForm.name    = crush.value.name ?? ''
    crushForm.school  = crush.value.school ?? ''
    crushForm.faculty = crush.value.faculty ?? ''
    crushForm.grade   = crush.value.grade ?? ''
    crushForm.gender  = crush.value.gender ?? ''
  } else {
    Object.assign(crushForm, blankPerson())
  }
  showCrushModal.value = true
}
const submitCrush = () => {
  crushForm.post(route('crush.store'), {
    onSuccess: () => {
      showCrushModal.value = false
      router.reload({ only: ['crush','can_change_crush','next_change_date','fans_count','oshi_me_count','balance'], preserveScroll: true, preserveState: true })
    },
  })
}

// ===== Oshi =====
const oshiForm = useForm(blankPerson())
const openOshi = () => { Object.assign(oshiForm, blankPerson()); showOshiModal.value = true }
const submitOshi = () => {
  oshiForm.post(route('oshi.store'), {
    onSuccess: () => {
      showOshiModal.value = false
      router.reload({ only: ['oshi_list','fans_count','oshi_me_count','balance'], preserveScroll: true, preserveState: true })
    },
  })
}
const delOshi = (id) => {
  const url = new URL(`/oshi/${id}`, window.location.origin).toString()
  router.visit(url, {
    method: 'delete',
    data: {},
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      router.reload({ only: ['oshi_list','fans_count','oshi_me_count','balance'], preserveScroll: true, preserveState: true })
    },
  })
}
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <!-- ==== POP Header (明るめパステル) ==== -->
    <template #header>
      <div
        class="relative overflow-hidden rounded-3xl border-2 border-pink-200 px-5 py-7 shadow-[0_12px_40px_-12px_rgba(236,72,153,.45)]
               bg-[radial-gradient(80%_140%_at_10%_-10%,#ffe4f2,transparent_60%),radial-gradient(80%_140%_at_110%_10%,#e6f4ff,transparent_60%),radial-gradient(120%_120%_at_50%_120%,#fff6d1,transparent_60%)]">
        <!-- コンフェッティ & スター -->
        <div class="pointer-events-none absolute inset-0 opacity-30 mix-blend-screen">
          <div class="absolute inset-0 bg-[url('/assets/patterns/confetti.svg')] [mask-image:radial-gradient(circle_at_30%_30%,black,transparent_70%)]"></div>
        </div>

        <div class="relative flex items-center justify-between gap-4">
          <div>
            <h2 class="text-3xl font-black text-pink-700 tracking-tight drop-shadow-[0_2px_0_rgba(255,255,255,.9)]">🌈 Dashboard</h2>
            <p class="text-sm text-gray-700 mt-1">今日の状況をひと目で確認しよう ✨</p>
          </div>

          <!-- Desktop: balance + actions -->
          <div class="hidden sm:flex items-center gap-3">
            <div class="rounded-2xl border-2 border-emerald-300 bg-white/85 px-4 py-2">
              <div class="text-[11px] text-gray-600">現在のポイント</div>
              <div class="text-lg font-black text-emerald-600">{{ balance.toLocaleString() }}<span class="text-xs"> pt</span></div>
            </div>
            <Link :href="route('points.dashboard')" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 px-4 py-2 text-white shadow hover:scale-105 active:scale-95 transition">
              💰 チャージ
            </Link>
            <Link :href="route('gacha.play')" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-br from-pink-500 to-fuchsia-500 px-4 py-2 text-white shadow hover:scale-105 active:scale-95 transition animate-pulse">
              💖 恋ガチャ
            </Link>
          </div>
        </div>

        <!-- Mobile: balance + buttons (復活) -->
        <div class="mt-4 sm:hidden flex flex-col gap-3">
          <div class="rounded-2xl border-2 border-emerald-300 bg-white/85 px-4 py-2 w-full">
            <div class="text-[11px] text-gray-600">現在のポイント</div>
            <div class="text-xl font-black text-emerald-600">{{ balance.toLocaleString() }}<span class="text-xs"> pt</span></div>
          </div>
          <div class="flex gap-3">
            <Link :href="route('points.dashboard')" class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 px-4 py-2 text-white shadow hover:scale-105 active:scale-95 transition">💰 チャージ</Link>
            <Link :href="route('gacha.play')" class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-pink-500 to-fuchsia-500 px-4 py-2 text-white shadow hover:scale-105 active:scale-95 transition">💖 恋ガチャ</Link>
          </div>
        </div>
      </div>
    </template>

    <!-- ==== 明るい背景 ==== -->
    <div class="relative">
      <div class="pointer-events-none absolute inset-0 -z-10 bg-[linear-gradient(180deg,#fff,rgba(255,255,255,.9)),repeating-linear-gradient(90deg,rgba(255,255,255,0),rgba(255,255,255,0)_14px,rgba(255,240,253,.6)_14px,rgba(255,240,253,.6)_28px)]"></div>

      <!-- Summary Cards -->
      <div class="mx-auto max-w-7xl px-6 lg:px-8 mt-8">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
          <div class="group rounded-3xl border-2 border-pink-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_14px_28px_-10px_rgba(236,72,153,.35)]">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs uppercase tracking-wide text-gray-600">自分を好きと言ってくれてる人</p>
                <p class="mt-2 text-4xl font-black text-pink-600">{{ fansCount }} <span class="text-base font-medium text-gray-600">人</span></p>
              </div>
              <div class="rounded-xl bg-pink-50 p-3 text-pink-600 shadow-inner">💘</div>
            </div>
          </div>
          <div class="group rounded-3xl border-2 border-indigo-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_14px_28px_-10px_rgba(99,102,241,.35)]">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs uppercase tracking-wide text-gray-600">自分を推しにしてくれてる人</p>
                <p class="mt-2 text-4xl font-black text-indigo-600">{{ oshiMeCount }} <span class="text-base font-medium text-gray-600">人</span></p>
              </div>
              <div class="rounded-xl bg-indigo-50 p-3 text-indigo-600 shadow-inner">⭐️</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main 2 columns -->
      <div class="mx-auto max-w-7xl px-6 lg:px-8 mt-8 pb-24">
        <div class="grid gap-8 md:grid-cols-2">
          <!-- CRUSH -->
          <section class="rounded-3xl border-2 border-pink-200 bg-gradient-to-br from-pink-50 to-white p-6 shadow-lg">
            <div class="mb-4 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="rounded-lg bg-pink-100 px-2.5 py-1 text-xs font-bold text-pink-700">CRUSH</div>
                <h3 class="text-lg font-bold text-gray-800">好きな人（週1回まで変更可）</h3>
              </div>

              <div class="flex items-center gap-2">
                <button
                  class="px-4 py-2 rounded-2xl text-white font-semibold shadow bg-gradient-to-br from-pink-500 to-rose-500 hover:scale-105 active:scale-95 transition disabled:opacity-60"
                  :disabled="!canChangeCrush"
                  @click="openCrush"
                  :title="!canChangeCrush && nextChangeDate ? `次回変更可能: ${nextChangeDate}` : ''"
                >
                  {{ crush ? '変更する' : '登録する' }}
                </button>

                <!-- 有料ガチャ（100pt）/ チャージ誘導  (復活) -->
                <Link v-if="balance >= 100" :href="route('gacha.play', { paid: 1 })" class="px-4 py-2 rounded-2xl text-white font-semibold shadow bg-gradient-to-br from-fuchsia-500 to-pink-500 hover:scale-105 active:scale-95 transition" title="100pt消費してガチャ">🎰 ガチャ（100pt）</Link>
                <Link v-else :href="route('points.dashboard')" class="px-4 py-2 rounded-2xl text-white font-semibold shadow bg-gradient-to-br from-emerald-500 to-teal-500 hover:scale-105 active:scale-95 transition" title="ポイントが不足しています（100pt必要）">💰 チャージしてガチャ</Link>
              </div>
            </div>

            <div v-if="crush" class="grid grid-cols-1 gap-3 text-sm">
              <div class="rounded-lg border border-pink-100 bg-white/80 px-3 py-2"><span class="text-gray-600">名前：</span><span class="font-semibold text-gray-800">{{ crush.name }}</span></div>
              <div class="rounded-lg border border-pink-100 bg-white/80 px-3 py-2"><span class="text-gray-600">学校名：</span><span class="font-semibold text-gray-800">{{ crush.school }}</span></div>
              <div class="rounded-lg border border-pink-100 bg-white/80 px-3 py-2"><span class="text-gray-600">学部：</span><span class="font-semibold text-gray-800">{{ crush.faculty }}</span></div>
              <div class="rounded-lg border border-pink-100 bg-white/80 px-3 py-2"><span class="text-gray-600">学年：</span><span class="font-semibold text-gray-800">{{ crush.grade }}</span></div>
              <div class="rounded-lg border border-pink-100 bg-white/80 px-3 py-2"><span class="text-gray-600">性別：</span><span class="font-semibold text-gray-800">{{ crush.gender }}</span></div>
              <p v-if="!canChangeCrush && nextChangeDate" class="text-xs text-gray-600">次回変更可能日：{{ nextChangeDate }}</p>
            </div>
            <div v-else class="rounded-2xl border border-dashed border-pink-300 p-6 text-center text-gray-500 bg-white/70">まだ登録されていません。</div>

            <!-- チャット誘導（復活） -->
            <div v-if="page.props.match_conversation_id" class="mt-4">
              <Link :href="route('chat.room', page.props.match_conversation_id)" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 px-4 py-2 text-white shadow hover:scale-105 active:scale-95 transition">
                <span>💬</span><span class="font-semibold">マッチした相手とチャットする</span>
              </Link>
            </div>
          </section>

          <!-- OSHI -->
          <section class="rounded-3xl border-2 border-indigo-200 bg-gradient-to-br from-indigo-50 to-white p-6 shadow-lg">
            <div class="mb-4 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="rounded-lg bg-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-700">OSHI</div>
                <h3 class="text-lg font-bold text-gray-800">推し（人数制限なし!）</h3>
              </div>
              <button class="px-4 py-2 rounded-2xl text-white font-semibold shadow bg-gradient-to-br from-indigo-500 to-violet-500 hover:scale-105 active:scale-95 transition" @click="openOshi">追加する</button>
            </div>

            <ul class="divide-y rounded-2xl border border-indigo-100">
              <li v-for="o in oshiList" :key="o.id" class="flex items-center justify-between gap-4 p-4 hover:bg-indigo-50/50 transition">
                <div class="min-w-0">
                  <p class="font-medium text-gray-800 flex items-center gap-2">
                    {{ o.name }}
                    <span class="inline-block h-2.5 w-2.5 rounded-full" :class="o.exists ? 'bg-emerald-500' : 'bg-red-500'" :title="o.exists ? '存在を確認' : '未確認'"></span>
                  </p>
                  <p class="truncate text-sm text-gray-600">{{ o.school }}・{{ o.faculty }}・{{ o.grade }}・{{ o.gender }}</p>
                </div>
                <button type="button" class="text-xs px-3 py-1.5 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 shadow-sm hover:shadow transition" @click.stop.prevent="delOshi(o.id)">削除</button>
              </li>
              <li v-if="!oshiList.length" class="p-6 text-center text-gray-500">まだ追加されていません。</li>
            </ul>

            <!-- Counters (維持) -->
            <div class="mt-6 grid grid-cols-2 gap-4">
              <div class="rounded-2xl border border-pink-100 bg-gradient-to-br from-pink-50 to-white p-4">
                <div class="text-sm text-gray-600">自分を好きと言ってくれてる人</div>
                <div class="mt-1 text-2xl font-extrabold text-pink-600">{{ fansCount }} <span class="text-base font-medium text-gray-600">人</span></div>
              </div>
              <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-4">
                <div class="text-sm text-gray-600">自分を推しにしてくれてる人</div>
                <div class="mt-1 text-2xl font-extrabold text-indigo-600">{{ oshiMeCount }} <span class="text-base font-medium text-gray-600">人</span></div>
              </div>
            </div>
          </section>
        </div>

        <!-- 通知（復活） -->
        <div class="mt-10">
          <div class="mb-3 flex items-center gap-2">
            <div class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700">NOTIFICATION</div>
            <h2 class="text-lg font-bold text-gray-800">通知</h2>
          </div>
          <div class="rounded-3xl border-2 border-violet-200 bg-white shadow-sm">
            <ul class="divide-y">
              <li v-for="n in page.props.notifications" :key="n.id" class="p-4 hover:bg-violet-50/50 transition">
                <p class="text-gray-800">{{ n.data.message }}</p>
              </li>
              <li v-if="!page.props.notifications?.length" class="p-6 text-center text-gray-500">通知はありません。</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== Modals ===== -->
    <div v-if="showCrushModal" class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4" @keydown.escape.stop.prevent="showCrushModal=false">
      <div class="w-full max-w-lg rounded-2xl border-2 border-pink-200 bg-white p-6 shadow-xl">
        <h4 class="text-lg font-bold mb-4">好きな人を{{ crush ? '変更' : '登録' }}</h4>
        <form @submit.prevent="submitCrush" class="space-y-3">
          <label class="block text-sm"><span class="text-gray-700">名前</span>
            <input v-model="crushForm.name" name="name" type="text" required placeholder="山田 花子" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-400" />
          </label>
          <label class="block text-sm"><span class="text-gray-700">学校名</span>
            <input v-model="crushForm.school" name="school" type="text" placeholder="〇〇大学" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-400" />
          </label>
          <label class="block text-sm"><span class="text-gray-700">学部</span>
            <input v-model="crushForm.faculty" name="faculty" type="text" placeholder="経済学部" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-400" />
          </label>
          <label class="block text-sm"><span class="text-gray-700">学年</span>
            <input v-model="crushForm.grade" name="grade" type="text" placeholder="3年" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-400" />
          </label>
          <label class="block text-sm"><span class="text-gray-700">性別</span>
            <select v-model="crushForm.gender" name="gender" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-400">
              <option value="">未選択</option><option value="男性">男性</option><option value="女性">女性</option><option value="その他">その他</option><option value="未回答">未回答</option>
            </select>
          </label>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" class="rounded-xl border border-gray-300 px-4 py-2 hover:bg-gray-50" @click="showCrushModal=false">キャンセル</button>
            <button type="submit" class="rounded-xl bg-gradient-to-br from-pink-500 to-rose-500 px-5 py-2 font-semibold text-white hover:opacity-95 disabled:opacity-60" :disabled="crushForm.processing">保存する</button>
          </div>
          <p v-if="crushForm.errors && Object.keys(crushForm.errors).length" class="text-sm text-red-600 mt-2">{{ Object.values(crushForm.errors).join(' / ') }}</p>
        </form>
      </div>
    </div>

    <div v-if="showOshiModal" class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4" @keydown.escape.stop.prevent="showOshiModal=false">
      <div class="w-full max-w-lg rounded-2xl border-2 border-indigo-200 bg-white p-6 shadow-xl">
        <h4 class="text-lg font-bold mb-4">推しを追加</h4>
        <form @submit.prevent="submitOshi" class="space-y-3">
          <label class="block text-sm"><span class="text-gray-700">名前</span>
            <input v-model="oshiForm.name" name="name" type="text" required placeholder="田中 太郎" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
          </label>
          <label class="block text-sm"><span class="text-gray-700">学校名</span>
            <input v-model="oshiForm.school" name="school" type="text" placeholder="△△高校 / ○○大学" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
          </label>
          <label class="block text-sm"><span class="text-gray-700">学部</span>
            <input v-model="oshiForm.faculty" name="faculty" type="text" placeholder="普通科 / 理工学部 など" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
          </label>
          <label class="block text-sm"><span class="text-gray-700">学年</span>
            <input v-model="oshiForm.grade" name="grade" type="text" placeholder="2年" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
          </label>
          <label class="block text-sm"><span class="text-gray-700">性別</span>
            <select v-model="oshiForm.gender" name="gender" class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
              <option value="">未選択</option><option value="男性">男性</option><option value="女性">女性</option><option value="その他">その他</option><option value="未回答">未回答</option>
            </select>
          </label>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" class="rounded-xl border border-gray-300 px-4 py-2 hover:bg-gray-50" @click="showOshiModal=false">キャンセル</button>
            <button type="submit" class="rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 px-5 py-2 font-semibold text-white hover:opacity-95 disabled:opacity-60" :disabled="oshiForm.processing">追加する</button>
          </div>
          <p v-if="oshiForm.errors && Object.keys(oshiForm.errors).length" class="text-sm text-red-600 mt-2">{{ Object.values(oshiForm.errors).join(' / ') }}</p>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>