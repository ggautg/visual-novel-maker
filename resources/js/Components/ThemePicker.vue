<script setup>
import { ref } from 'vue';
import { applyTheme, readTheme, THEMES } from '../theme.js';

const selectedTheme = ref(readTheme());

const changeTheme = (event) => {
    selectedTheme.value = applyTheme(event.target.value);
};
</script>

<template>
    <label class="vn-theme-picker">
        <span class="vn-theme-picker__label">Estilo visual</span>
        <select
            class="vn-theme-picker__select"
            :value="selectedTheme"
            aria-label="Elegir estilo visual"
            @change="changeTheme"
        >
            <option v-for="theme in THEMES" :key="theme.id" :value="theme.id">
                {{ theme.label }}
            </option>
        </select>
    </label>
</template>

<style scoped>
.vn-theme-picker {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    color: var(--vn-muted, #d9cffb);
    font-family: 'Rubik', system-ui, sans-serif;
    font-size: 0.78rem;
}

.vn-theme-picker__label {
    white-space: nowrap;
    font-weight: 600;
}

.vn-theme-picker__select {
    min-width: 9.5rem;
    border: 1px solid var(--vn-border, rgba(201, 184, 255, 0.25));
    border-radius: 999px;
    background: var(--vn-control-bg, rgba(18, 11, 38, 0.92));
    padding: 0.42rem 0.78rem;
    color: var(--vn-text, #fff6fb);
    font: inherit;
    cursor: pointer;
}

.vn-theme-picker__select:focus-visible {
    outline: 2px solid var(--vn-accent, #ff5fa2);
    outline-offset: 2px;
}
</style>
