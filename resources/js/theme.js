export const DEFAULT_THEME = 'current';
export const THEME_STORAGE_KEY = 'vn-maker-theme';

export const THEMES = Object.freeze([
    Object.freeze({ id: 'current', label: 'Púrpura' }),
    Object.freeze({ id: 'gothic', label: 'Monja gótica' }),
    Object.freeze({ id: 'hopecore', label: 'Esperanza luminosa' }),
    Object.freeze({ id: 'dark', label: 'Oscuro' }),
    Object.freeze({ id: 'white', label: 'Blanco' }),
    Object.freeze({ id: 'hellfire', label: 'Amor infernal' }),
]);

const themeIds = new Set(THEMES.map(({ id }) => id));

export function normalizeTheme(theme) {
    return themeIds.has(theme) ? theme : DEFAULT_THEME;
}

function resolveStorage(storage) {
    if (storage !== undefined) return storage;

    try {
        return globalThis.localStorage;
    } catch {
        return null;
    }
}

export function readTheme(storage) {
    try {
        return normalizeTheme(resolveStorage(storage)?.getItem(THEME_STORAGE_KEY));
    } catch {
        return DEFAULT_THEME;
    }
}

export function applyTheme(theme, root, storage) {
    const selectedTheme = normalizeTheme(theme);
    const targetRoot = root ?? globalThis.document?.documentElement;

    if (targetRoot?.dataset) {
        targetRoot.dataset.vnTheme = selectedTheme;
    }

    try {
        resolveStorage(storage)?.setItem(THEME_STORAGE_KEY, selectedTheme);
    } catch {
        // El tema elegido se mantiene en esta página aunque el navegador bloquee el almacenamiento.
    }

    return selectedTheme;
}
