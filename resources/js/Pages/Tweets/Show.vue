<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  tweet: { type: Object, required: true }, // controllerで {media:[{thumb,url,...}], viewerUnlocked,...}
})

const unlock = () => {
  router.post(route('tweets.unlock', props.tweet.id), {}, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ['tweet'] }),
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head :title="`ツイート #${props.tweet.id}`" />

    <div class="max-w-4xl mx-auto p-6 space-y-6">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
          <Link :href="route('tweets.index')" class="underline">一覧へ</Link>
        </div>
        <div class="text-xs">
          <span v-if="props.tweet.is_paid" class="rounded bg-rose-100 text-rose-700 px-2 py-1">
            有償 {{ props.tweet.price_points }}pt
          </span>
          <span v-else class="rounded bg-gray-100 text-gray-700 px-2 py-1">無料</span>
        </div>
      </div>

      <header class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="text-sm text-gray-600">
          <span class="font-semibold text-gray-800">{{ props.tweet.user.name }}</span>
        </div>
        <h1 v-if="props.tweet.title" class="text-xl font-bold mt-1">{{ props.tweet.title }}</h1>

        <div class="mt-4">
          <p v-if="props.tweet.body" class="whitespace-pre-line text-gray-900">
            {{ props.tweet.body }}
          </p>
          <div v-else class="text-gray-500">
            このツイートは有償です。アンロックしてください。
          </div>
        </div>

        <div v-if="props.tweet.is_paid && !props.tweet.viewerUnlocked" class="mt-4">
          <button
            @click="unlock"
            class="px-4 py-2 rounded bg-rose-600 text-white hover:bg-rose-700"
          >
            アンロック ({{ props.tweet.price_points }}pt)
          </button>
        </div>
      </header>

      <section v-if="props.tweet.media?.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-lg font-semibold mb-3">メディア</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <div
            v-for="m in props.tweet.media"
            :key="m.id"
            class="relative group rounded-lg border overflow-hidden"
          >
            <template v-if="m.kind === 'image'">
              <img
                :src="props.tweet.viewerUnlocked && m.url ? m.url : m.thumb"
                class="w-full h-48 object-cover"
              />
            </template>

            <template v-else>
              <video
                v-if="props.tweet.viewerUnlocked && m.url"
                controls
                class="w-full h-48 object-cover bg-black"
                :src="m.url"
              />
              <div v-else class="w-full h-48 grid place-items-center bg-black/5">
                🎬
              </div>
            </template>
          </div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
