import assert from 'node:assert/strict';
import test from 'node:test';

let themeModule;
try {
    themeModule = await import('../../resources/js/theme.js');
} catch {
    themeModule = {};
}

test('offers the five named themes and keeps the existing theme as default', () => {
    assert.equal(themeModule.DEFAULT_THEME, 'current');
    assert.deepEqual(
        themeModule.THEMES?.map(({ id, label }) => [id, label]),
        [
            ['current', 'Actual'],
            ['gothic', 'Monja gótica'],
            ['hopecore', 'Sunshine esperanza (hopecore)'],
            ['dark', 'Dark'],
            ['white', 'White'],
        ],
    );
});

test('applies a theme to the document root and persists the selection', () => {
    assert.equal(typeof themeModule.applyTheme, 'function');
    assert.equal(typeof themeModule.readTheme, 'function');

    const root = { dataset: {} };
    const saved = new Map();
    const storage = {
        getItem: (key) => saved.get(key) ?? null,
        setItem: (key, value) => saved.set(key, value),
    };

    assert.equal(themeModule.applyTheme('hopecore', root, storage), 'hopecore');
    assert.equal(root.dataset.vnTheme, 'hopecore');
    assert.equal(themeModule.readTheme(storage), 'hopecore');
});

test('falls back to the current theme for unknown or unavailable preferences', () => {
    assert.equal(typeof themeModule.readTheme, 'function');
    assert.equal(typeof themeModule.applyTheme, 'function');
    assert.equal(themeModule.readTheme({ getItem: () => 'not-a-theme' }), 'current');
    assert.equal(themeModule.applyTheme('not-a-theme', { dataset: {} }, null), 'current');
});
