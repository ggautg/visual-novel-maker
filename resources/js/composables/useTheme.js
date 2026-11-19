import { ref } from 'vue';
import { applyTheme, normalizeTheme, readTheme, THEMES, THEME_STORAGE_KEY } from '../theme.js';

// Estado único para que todos los selectores de tema reflejen el mismo valor.
const theme = ref(readTheme());

if (typeof window !== 'undefined') {
    window.addEventListener('storage', (event) => {
        if (event.key !== THEME_STORAGE_KEY) return;

        theme.value = normalizeTheme(event.newValue);
        document.documentElement.dataset.vnTheme = theme.value;
    });
}

export function useTheme() {
    const setTheme = (id) => {
        theme.value = applyTheme(id);
    };

    return { theme, themes: THEMES, setTheme };
}
