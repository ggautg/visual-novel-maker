<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    description: { type: String, required: true },
    actionLabel: { type: String, default: '' },
    actionHref: { type: String, default: '' },
});
</script>

<template>
    <div class="vn-card text-center">
        <h2 class="vn-card-title">{{ title }}</h2>
        <p class="vn-card-subtitle">{{ description }}</p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            <Link v-if="actionLabel && actionHref" :href="actionHref" class="vn-pill vn-pill--ghost">
                {{ actionLabel }}
            </Link>
            <slot name="action" />
            <slot />
        </div>
    </div>
</template>
