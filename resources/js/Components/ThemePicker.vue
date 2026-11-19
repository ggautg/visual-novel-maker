<script setup>
import { useTheme } from '../composables/useTheme.js';

const { theme, themes, setTheme } = useTheme();

const changeTheme = (event) => setTheme(event.target.value);
</script>

<template>
    <label class="vn-theme-picker">
        <span class="vn-theme-picker__label">Estilo visual</span>
        <select
            class="vn-theme-picker__select"
            :value="theme"
            aria-label="Elige el estilo visual"
            @change="changeTheme"
        >
            <option v-for="item in themes" :key="item.id" :value="item.id">
                {{ item.label }}
            </option>
        </select>
    </label>
</template>

<style scoped>
.vn-theme-picker {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.78rem;
}

.vn-theme-picker__label {
    white-space: nowrap;
    font-weight: 600;
}

.vn-theme-picker__select {
    min-width: 9.5rem;
    border: 1px solid var(--vn-border);
    border-radius: 999px;
    background: var(--vn-control-bg);
    padding: 0.42rem 0.78rem;
    color: var(--vn-text);
    font: inherit;
    cursor: pointer;
    transition: border-color 180ms ease, box-shadow 180ms ease;
}

.vn-theme-picker__select:focus-visible {
    outline: 2px solid var(--vn-accent);
    outline-offset: 2px;
}
</style>
