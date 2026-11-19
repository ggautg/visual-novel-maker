<script setup>
defineProps({
    message: { type: String, default: '' },
    inputId: { type: String, default: '' },
});
</script>

<template>
    <div v-show="message" :id="inputId ? `${inputId}-error` : undefined" role="alert">
        <p class="vn-input-error">{{ message }}</p>
    </div>
</template>
