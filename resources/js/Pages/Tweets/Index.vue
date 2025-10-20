<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, computed } from 'vue'

/**
 * サーバ側（TweetController@index）からの想定props
 * - tweets: 配列 もしくは ページネーションオブジェクト {data:[], links:[]}
 * - oshi_mode: boolean（推しモード＝この推しの投稿のみ表示）
 * - oshi_user: { id:number, name:string } | null
 */
const props = defineProps({
  tweets: { type: [Object, Array], required: true },
  oshi_mode: { type: Boolean, default: false },
  oshi_user: { type: Object, default: null },
  can_post: { type: Boolean, default: true }, 
})

/* ===== 投稿フォーム ===== */
const form = useForm({
  title: '',
  body: '',
  is_paid: false,
  price_points: null,
  media: [], // { file:File, kind:'image'|'video', sort:number }
})
const files = ref([]) // input[type=file] の表示用リスト

const onFilesSelected = (e) => {
  const list = Array.from(e.target.files || [])
  files.value = list.map((f, idx) => {
    const ext = f.name.toLowerCase().split('.').pop()
    const kind = ['mp4','mov','webm','m4v'].includes(ext) ? 'video' : 'image'
    return { file: f, kind, sort: idx }
  })
}

const submit = () => {
  form.media = files.value
  form.post(route('tweets.store'), {
    forceFormData: true,               // ← file送信に必須
    onSuccess: () => {
      form.reset()
      files.value = []
      router.reload({ only: ['tweets'] }) // TLだけ更新
    },
  })
}

/* ===== アンロック（購入） ===== */
const unlock = (id, price) => {
  router.post(route('tweets.unlock', id), { price_points: price }, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ['tweets'] }),
  })
}

/* ===== ヘルパ ===== */
const list = computed(() => {
  if (Array.isArray(props.tweets)) return props.tweets
  if (Array.isArray(props.tweets?.data)) return props.tweets.data
  return []
})
const links = computed(() => Array.isArray(props.tweets?.links) ? props.tweets.links : [])
</script>

<template>
  <AuthenticatedLayout>
    <Head :title="props.oshi_mode && props.oshi_user ? `${props.oshi_user.name}さんのツイート` : 'ツイート一覧'" />

    <div class="max-w-5xl mx-auto p-6 space-y-8">
      <!-- ヘッダー -->
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">
          {{ props.oshi_mode && props.oshi_user ? `${props.oshi_user.name} さんのツイート` : 'タイムライン' }}
        </h1>
        <Link v-if="props.oshi_mode" :href="route('dashboard')" class="text-sm underline">
          全体に戻る
        </Link>
      </div>

      <!-- 投稿フォーム（必要なければこのセクションを消してOK） -->
      <section v-if="props.can_post" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">

        <h2 class="text-lg font-semibold mb-3">新規ツイート</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid sm:grid-cols-2 gap-3">
            <label class="block">
              <span class="text-sm text-gray-600">タイトル（任意）</span>
              <input v-model="form.title" type="text" maxlength="120"
                     class="mt-1 w-full border rounded px-3 py-2" />
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">有償にする</span>
              <div class="mt-1 flex items-center gap-2">
                <input id="paid" type="checkbox" v-model="form.is_paid" class="h-4 w-4" />
                <label for="paid" class="text-sm text-gray-700">有償</label>
                <input
                  v-if="form.is_paid"
                  v-model.number="form.price_points"
                  type="number" min="1" step="1" placeholder="価格（pt）"
                  class="ml-3 w-40 border rounded px-3 py-2"
                />
              </div>
            </label>
          </div>

          <label class="block">
            <span class="text-sm text-gray-600">本文（最大 8,000 文字）</span>
            <textarea v-model="form.body" rows="5" maxlength="8000" required
                      class="mt-1 w-full border rounded px-3 py-2" />
          </label>

          <div class="space-y-2">
            <span class="text-sm text-gray-600">画像/動画（最大10件, 50MB/件）</span>
            <input type="file" multiple @change="onFilesSelected" class="block" />
            <div v-if="files.length" class="text-xs text-gray-500">
              選択: {{ files.map(f => f.file.name).join(', ') }}
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button type="submit" :disabled="form.processing"
                    class="px-5 py-2 rounded bg-emerald-600 text-white font-semibold hover:bg-emerald-700 disabled:opacity-60">
              投稿する
            </button>
            <div v-if="form.errors && Object.keys(form.errors).length" class="text-sm text-rose-600">
              {{ Object.values(form.errors).join(' / ') }}
            </div>
          </div>
        </form>
      </section>

      <!-- 一覧 -->
      <section>
        <div class="space-y-4">
          <article v-for="t in list" :key="t.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-600">
                <span class="font-semibold text-gray-800">{{ t.user.name }}</span>
                <span class="ml-2">{{ t.created_at }}</span>
              </div>
              <div class="text-xs">
                <span class="rounded px-2 py-1"
                      :class="t.is_paid ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-700'">
                  {{ t.is_paid ? `有償 ${t.unlock_price}pt` : '無料' }}
                </span>
              </div>
            </div>

            <h3 v-if="t.title" class="mt-2 text-lg font-bold">{{ t.title }}</h3>

            <!-- 本文：未解放なら抜粋 -->
            <p v-if="t.body" class="mt-2 whitespace-pre-line">{{ t.body }}</p>
            <p v-else-if="t.excerpt" class="mt-2 text-gray-600">{{ t.excerpt }}</p>

            <!-- メディア: サムネ= m.thumb(フルURL), 解放時= m.url(署名URL) -->
            <div v-if="t.media?.length" class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div v-for="m in t.media" :key="m.id" class="relative group">
                <template v-if="m.kind === 'image'">
                  <img :src="t.viewerUnlocked && m.url ? m.url : m.thumb"
                       class="w-full h-36 object-cover rounded-lg border" />
                </template>
                <template v-else>
                  <video v-if="t.viewerUnlocked && m.url"
                         controls class="w-full h-36 object-cover bg-black rounded-lg"
                         :src="m.url" />
                  <div v-else class="w-full h-36 grid place-items-center rounded-lg border bg-black/5">
                    🎬
                  </div>
                </template>
              </div>
            </div>

            <div class="mt-4 flex items-center gap-3">
              <Link :href="route('tweets.show', t.id)" class="px-3 py-1.5 rounded border hover:bg-gray-50 text-sm">
                詳細
              </Link>

              <button
                v-if="t.is_paid && !t.viewerUnlocked"
                @click="unlock(t.id, t.unlock_price)"
                class="px-3 py-1.5 rounded bg-rose-600 text-white text-sm hover:bg-rose-700"
              >
                アンロック ({{ t.unlock_price }}pt)
              </button>

              <span v-if="t.viewerUnlocked" class="text-sm text-emerald-600">アンロック済み</span>
            </div>
          </article>
        </div>

        <!-- ページネーション（ある場合のみ） -->
        <nav v-if="links.length" class="mt-6 flex flex-wrap gap-2">
          <Link v-for="(l,i) in links" :key="i" :href="l.url || '#'"
                class="px-3 py-1.5 rounded border text-sm"
                :class="l.active ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 hover:bg-gray-50'"
                v-html="l.label" />
        </nav>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
