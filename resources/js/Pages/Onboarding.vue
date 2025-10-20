<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  user: { type: Object, required: true }
});

const form = useForm({
  real_name: props.user.real_name ?? '',
  school_name: props.user.school_name ?? '',
  grade: props.user.grade ?? '',
  class_or_department: props.user.class_or_department ?? '',
});

const submit = () => {
  form.post(route('onboarding.store'), { preserveScroll: true });
};
</script>

<template>
  <Head title="はじめに - 基本情報の登録" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        はじめに：基本情報の登録
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <InputLabel for="real_name" value="本名" />
                <TextInput id="real_name" v-model="form.real_name" type="text" class="mt-1 block w-full" autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.real_name" />
              </div>

              <div>
                <InputLabel for="school_name" value="学校名" />
                <TextInput id="school_name" v-model="form.school_name" type="text" class="mt-1 block w-full" autocomplete="organization" />
                <InputError class="mt-2" :message="form.errors.school_name" />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                  <InputLabel for="grade" value="学年" />
                  <TextInput id="grade" v-model="form.grade" type="text" class="mt-1 block w-full" placeholder="例）1年 / 高1 / B2 など" />
                  <InputError class="mt-2" :message="form.errors.grade" />
                </div>
                <div>
                  <InputLabel for="class_or_department" value="クラス または 学部・学科" />
                  <TextInput id="class_or_department" v-model="form.class_or_department" type="text" class="mt-1 block w-full" placeholder="例）1組 / 経済学部 経済学科 など" />
                  <InputError class="mt-2" :message="form.errors.class_or_department" />
                </div>
              </div>

              <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">保存してはじめる</PrimaryButton>
                <span v-if="form.recentlySuccessful" class="text-sm text-gray-600">保存しました</span>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
