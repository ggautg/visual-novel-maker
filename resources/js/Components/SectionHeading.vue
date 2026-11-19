<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="vn-section-heading">
        <h2 class="vn-page-title">{{ title }}</h2>
        <p v-if="description" class="vn-card-subtitle">{{ description }}</p>
    </div>
</template>
