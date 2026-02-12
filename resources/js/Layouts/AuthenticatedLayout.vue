<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <aside class="fixed left-0 top-0 z-40 hidden h-screen w-56 border-r border-gray-200 sm:block" style="background-color: #F6F8FA">
            <div class="flex h-full flex-col">
                <div class="flex items-center gap-2 border-b border-gray-200 px-3 pb-4 pt-4">
                    <img
                        src="/storage/icons/Group%20188.svg"
                        alt=""
                        class="h-9 w-9 shrink-0 object-contain"
                    />
                    <span class="font-semibold text-gray-900">Daily</span>
                    <span class="font-bold text-gray-900">Grow</span>
                </div>

                <div class="px-3 py-3">
                    <p class="text-xs text-gray-500">Название аккаунта</p>
                    <p class="truncate text-sm font-medium text-gray-700">{{ $page.props.auth.user?.name ?? '—' }}</p>
                </div>

                <nav class="flex-1 space-y-0.5 px-2 py-4">
                    <div class="mb-1 flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium uppercase tracking-wide text-gray-500" style="background-color: #FFFFFF">
                        <img src="/storage/icons/Vector.svg" alt="" class="h-4 w-4 shrink-0 opacity-70" />
                        <span>Отзывы</span>
                    </div>
                    <Link
                        :href="route('yandex.reviews')"
                        :class="[
                            'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition',
                            route().current('yandex.reviews')
                                ? 'bg-gray-200 text-gray-900'
                                : 'text-gray-600 hover:bg-gray-200/70 hover:text-gray-900'
                        ]"
                    >
                        <span class="w-4 shrink-0" />
                        <span>Отзывы</span>
                    </Link>
                    <Link
                        :href="route('yandex.settings')"
                        :class="[
                            'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition',
                            route().current('yandex.settings')
                                ? 'bg-gray-200 text-gray-900'
                                : 'text-gray-600 hover:bg-gray-200/70 hover:text-gray-900'
                        ]"
                    >
                        <span class="w-4 shrink-0" />
                        <span>Настройка</span>
                    </Link>
                </nav>
                <div class="border-t border-gray-200 p-2">
                    <Dropdown align="left" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-200/70"
                            >
                                <span class="truncate">{{ $page.props.auth.user?.name }}</span>
                                <svg class="ml-auto h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Профиль</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Выйти</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </aside>

        <div class="pb-20 sm:pl-56 sm:pb-6">
            <header v-if="$slots.header" class="border-b border-gray-200 bg-white">
                <div class="px-6 py-4">
                    <slot name="header" />
                </div>
            </header>
            <main class="p-6">
                <slot />
            </main>
        </div>

        <div class="fixed bottom-0 left-0 right-0 z-50 flex border-t border-gray-200 bg-white px-2 py-2 sm:hidden">
            <Link
                :href="route('yandex.reviews')"
                class="flex flex-1 flex-col items-center rounded-lg py-2 text-xs text-gray-600"
            >
                Отзывы
            </Link>
            <Link
                :href="route('yandex.settings')"
                class="flex flex-1 flex-col items-center rounded-lg py-2 text-xs text-gray-600"
            >
                Настройка
            </Link>
        </div>
        <div class="h-16 sm:hidden" aria-hidden="true" />
    </div>
</template>
