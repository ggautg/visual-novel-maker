<script setup>
import SectionHeading from './SectionHeading.vue';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <section class="vn-card vn-form-card">
        <SectionHeading :title="title" :description="description" />
        <div class="mt-6">
            <slot />
        </div>
    </section>
</template>
