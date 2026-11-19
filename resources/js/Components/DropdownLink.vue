<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: { type: String, required: true },
    method: { type: String, default: 'get' },
    as: { type: String, default: 'a' },
});
</script>

<template>
    <Link
        :href="href"
        :method="method"
        :as="as"
        :type="as === 'button' ? 'button' : undefined"
        class="vn-dropdown-link"
        role="menuitem"
    >
        <slot />
    </Link>
</template>
