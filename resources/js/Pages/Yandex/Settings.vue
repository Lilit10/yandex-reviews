<script setup>
import { watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const exampleUrl = 'https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/';

const props = defineProps({
    yandexUrl: { type: String, default: '' },
});

const form = useForm({
    yandex_url: props.yandexUrl || '',
});

watch(() => props.yandexUrl, (url) => {
    form.yandex_url = url || '';
});

const submit = () => form.post(route('yandex.settings.store'), {
    preserveScroll: true,
});
</script>

<template>
    <Head title="Подключить Яндекс" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-end">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                    title="Выйти"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </Link>
            </div>
        </template>

        <div class="max-w-2xl">
            <h2 class="mb-2 text-lg font-semibold text-gray-800">
                Подключить Яндекс
            </h2>
            <p class="mb-2 text-gray-600">
                Укажите ссылку на Яндекс, пример
            </p>
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <a
                    :href="exampleUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="break-all text-sm text-blue-600 underline hover:text-blue-800"
                >
                    {{ exampleUrl }}
                </a>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <TextInput
                        id="yandex_url"
                        v-model="form.yandex_url"
                        type="url"
                        class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Вставьте ссылку на отзывы организации"
                        autocomplete="url"
                    />
                    <InputError class="mt-2" :message="form.errors.yandex_url" />
                </div>

                <div v-if="form.recentlySuccessful" class="rounded-lg bg-green-50 p-4 text-sm text-green-700">
                    Настройки сохранены. Перейдите в раздел «Отзывы» для просмотра.
                </div>

                <PrimaryButton variant="blue" :disabled="form.processing">
                    Сохранить
                </PrimaryButton>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
