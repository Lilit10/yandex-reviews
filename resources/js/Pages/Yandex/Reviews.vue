<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    rating: { type: Number, default: null },
    reviewsCount: { type: Number, default: 0 },
    companyName: { type: String, default: null },
    reviews: { type: Array, default: () => [] },
    error: { type: String, default: null },
    success: { type: String, default: null },
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' });
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const h = String(d.getHours()).padStart(2, '0');
    const m = String(d.getMinutes()).padStart(2, '0');
    return `${day}.${month}.${year} ${h}:${m}`;
};

const fullStars = (rating) => Math.min(5, Math.floor(rating || 0));
const hasHalfStar = (rating) => (rating || 0) % 1 >= 0.5;
</script>

<template>
    <Head title="Отзывы" />

    <AuthenticatedLayout>
        <template #header>
            <div></div>
        </template>

        <div v-if="success" class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800">
            {{ success }}
        </div>
        <div v-if="error" class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800">
            {{ error }}
            <p class="mt-2 text-sm">
                <a :href="route('yandex.settings')" class="font-medium underline">Перейти в настройки</a> и указать ссылку.
            </p>
        </div>

        <template v-else>
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <span class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700">
                    <svg class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                    </svg>
                    <span>Яндекс Карты</span>
                </span>

                <div v-if="reviews && reviews.length > 0" class="rounded-xl border border-gray-200 bg-white px-8 py-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div v-if="rating !== null" class="text-5xl font-bold text-gray-900 leading-none">
                                {{ Number(rating).toFixed(1) }}
                            </div>
                            <div v-else class="text-5xl text-gray-400 leading-none">—</div>
                            <div class="flex gap-0.5 text-2xl">
                                <template v-for="i in 5" :key="i">
                                    <span v-if="i <= fullStars(rating)" class="text-amber-400">★</span>
                                    <span v-else-if="i === fullStars(rating) + 1 && hasHalfStar(rating)" class="text-amber-400 opacity-50">★</span>
                                    <span v-else class="text-gray-300">★</span>
                                </template>
                            </div>
                        </div>
                        <div class="border-t border-gray-200"></div>
                        <p class="text-sm text-gray-600">
                            Всего отзывов: {{ (reviewsCount ?? 0).toLocaleString('ru-RU') }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="!reviews || reviews.length === 0" class="flex flex-wrap gap-4 items-stretch">
                <div class="flex-1 min-w-[280px] rounded-xl border border-gray-200 bg-white p-10 text-center text-gray-500 flex items-center justify-center">
                    <template v-if="(rating !== null || (reviewsCount ?? 0) > 0)">
                        Рейтинг и количество отзывов загружены. Список текстов отзывов Яндекс.Карты подгружают по отдельному запросу в браузере, поэтому при загрузке страницы сервером они могут быть недоступны. Отображаем только сводку.
                    </template>
                    <template v-else>
                        Отзывов пока нет. Укажите ссылку в <a :href="route('yandex.settings')" class="font-medium text-blue-600 underline">настройках</a> — данные подгрузятся при загрузке страницы.
                    </template>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white px-8 py-6 shadow-sm shrink-0">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div v-if="rating !== null" class="text-5xl font-bold text-gray-900 leading-none">
                                {{ Number(rating).toFixed(1) }}
                            </div>
                            <div v-else class="text-5xl text-gray-400 leading-none">—</div>
                            <div class="flex gap-0.5 text-2xl">
                                <template v-for="i in 5" :key="i">
                                    <span v-if="i <= fullStars(rating)" class="text-amber-400">★</span>
                                    <span v-else-if="i === fullStars(rating) + 1 && hasHalfStar(rating)" class="text-amber-400 opacity-50">★</span>
                                    <span v-else class="text-gray-300">★</span>
                                </template>
                            </div>
                        </div>
                        <div class="border-t border-gray-200"></div>
                        <p class="text-sm text-gray-600">
                            Всего отзывов: {{ (reviewsCount ?? 0).toLocaleString('ru-RU') }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="(review, index) in reviews"
                    :key="index"
                    class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-3 text-sm text-gray-600">
                            <span>{{ formatDateTime(review.date) }}</span>
                            <span class="flex items-center gap-1.5">
                                <span class="font-medium">{{ companyName || 'Филиал 1' }}</span>
                                <svg class="h-3.5 w-3.5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                                </svg>
                            </span>
                        </div>

                        <div v-if="review.rating !== undefined && review.rating !== null" class="flex shrink-0 gap-0.5 text-xl">
                            <template v-for="i in 5" :key="i">
                                <span v-if="i <= (review.rating || 0)" class="text-amber-400">★</span>
                                <span v-else class="text-gray-300">★</span>
                            </template>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="font-semibold text-gray-900">
                            {{ review.author || 'Гость' }}
                        </p>
                        <p v-if="review.phone" class="text-sm text-gray-600">
                            {{ review.phone }}
                        </p>
                    </div>

                    <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">
                        {{ review.text || '—' }}
                    </p>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
