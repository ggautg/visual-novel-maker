import assert from 'node:assert/strict';
import test from 'node:test';

// La importación se hace sin try/catch: si theme.js se rompe, el test debe fallar.
const themeModule = await import('../../resources/js/theme.js');

test('ofrece los seis temas con etiquetas en español', () => {
    assert.equal(themeModule.DEFAULT_THEME, 'current');
    assert.deepEqual(
        themeModule.THEMES.map(({ id, label }) => [id, label]),
        [
            ['current', 'Púrpura'],
            ['gothic', 'Monja gótica'],
            ['hopecore', 'Esperanza luminosa'],
            ['dark', 'Oscuro'],
            ['white', 'Blanco'],
            ['hellfire', 'Amor infernal'],
        ],
    );
});

test('aplica el tema al documento y recuerda la elección', () => {
    assert.equal(typeof themeModule.applyTheme, 'function');
    assert.equal(typeof themeModule.readTheme, 'function');

    const root = { dataset: {} };
    const guardado = new Map();
    const almacenamiento = {
        getItem: (clave) => guardado.get(clave) ?? null,
        setItem: (clave, valor) => guardado.set(clave, valor),
    };

    assert.equal(themeModule.applyTheme('hopecore', root, almacenamiento), 'hopecore');
    assert.equal(root.dataset.vnTheme, 'hopecore');
    assert.equal(themeModule.readTheme(almacenamiento), 'hopecore');
});

test('usa el tema púrpura cuando la preferencia no existe o no es válida', () => {
    assert.equal(themeModule.readTheme({ getItem: () => 'no-existe' }), 'current');
    assert.equal(themeModule.applyTheme('no-existe', { dataset: {} }, null), 'current');
});
