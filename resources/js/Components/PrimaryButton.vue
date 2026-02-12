<script setup>
defineProps({
    variant: { type: String, default: 'default' },
});
</script>

<template>
    <button
        v-bind="$attrs"
        :class="[
            'inline-flex items-center justify-center rounded-md border border-transparent px-4 py-2 text-sm font-semibold text-white transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50',
            variant === 'blue'
                ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 active:bg-blue-800'
                : 'bg-gray-800 hover:bg-gray-700 focus:ring-indigo-500 active:bg-gray-900'
        ]"
    >
        <slot />
    </button>
</template>
