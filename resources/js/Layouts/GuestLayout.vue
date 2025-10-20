<script setup>
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <div
    class="min-h-screen flex flex-col items-center justify-center bg-[radial-gradient(70%_130%_at_10%_-10%,#ffe4f2,transparent_60%),radial-gradient(90%_120%_at_110%_10%,#e6f4ff,transparent_60%),radial-gradient(120%_100%_at_50%_120%,#fff6d1,transparent_60%)] relative overflow-hidden"
  >
    <!-- コンフェッティ背景 -->
    <div
      class="pointer-events-none absolute inset-0 -z-10 opacity-25 bg-[url('/assets/patterns/confetti.svg')] animate-[pulse_6s_ease-in-out_infinite]"
    ></div>

    <!-- スロット (ログイン/登録などの中身) -->
    <div class="w-full flex flex-col items-center justify-center px-4">
      <slot />
    </div>

    <!-- キラ装飾（お好み） -->
    <div class="pointer-events-none absolute -top-6 left-10 h-2.5 w-2.5 rounded-full bg-pink-400/70 blur-[1px] animate-ping"></div>
    <div class="pointer-events-none absolute top-24 right-16 h-2 w-2 rounded-full bg-indigo-400/70 blur-[1px] animate-ping [animation-delay:300ms]"></div>
    <div class="pointer-events-none absolute bottom-10 left-1/3 h-2 w-2 rounded-full bg-fuchsia-400/70 blur-[1px] animate-ping [animation-delay:600ms]"></div>
  </div>
</template>
