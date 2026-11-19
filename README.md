# Creador de Novelas Visuales (VN Maker)

Aplicación web para escribir, publicar y jugar novelas visuales. Está hecha con **Laravel 13**, **Inertia 2** y **Vue 3**. La interfaz es 100 % española, funciona con seis temas de color y guía la vista del lector con bloques cortos, contraste coherente y estados de carga visibles.

---

## 1. Requisitos

| Herramienta | Versión comprobada |
| --- | --- |
| PHP | 8.3.36 |
| Composer | 2.10.2 |
| Node.js / npm | los que instalan Vite 8 y Vue 3 |
| MySQL | 8.0.35 (servicio `MySQL80` en Windows) |

El proyecto usa **MySQL**, no SQLite. La contraseña local del usuario `root` está configurada en el archivo `.env` (ignorado por Git).

---

## 2. Puesta en marcha (Windows / PowerShell)

```powershell
# 1. Crear la base de datos una sola vez
mysql --host=127.0.0.1 --user=root --password=ionysmaster666 `
  -e "CREATE DATABASE IF NOT EXISTS visual_novel_maker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Copiar la plantilla de entorno y generar la clave
Copy-Item .env.example .env
php artisan key:generate

# 3. Editar .env: DB_PASSWORD=ionysmaster666 (y revisar APP_URL)

# 4. Instalar dependencias
composer install
npm ci

# 5. Crear tablas y compilar el frontend
php artisan migrate --force
npm run build

# 6. Arrancar
php artisan serve
```

En otro terminal, para desarrollo con recarga automática:

```powershell
npm run dev
```

### Variables de entorno importantes

| Variable | Valor local | Para qué sirve |
| --- | --- | --- |
| `APP_NAME` | `Creador de Novelas Visuales` | Nombre en correos y cabeceras. |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | `es` | Traducciones de validación y mensajes de Laravel. |
| `APP_FAKER_LOCALE` | `es_ES` | Datos falsos en español para pruebas. |
| `DB_CONNECTION` | `mysql` | Motor de base de datos. |
| `DB_DATABASE` | `visual_novel_maker` | Nombre de la base de datos. |
| `DB_USERNAME` / `DB_PASSWORD` | `root` / `ionysmaster666` | Credenciales locales de MySQL. |
| `VITE_APP_NAME` | `VN Maker` | Marca que aparece en el título del navegador. |

> Seguridad: la contraseña real solo vive en `.env`, que está en `.gitignore`. Nunca subas ese archivo. En producción usa un usuario de base de datos propio, no `root`.

---

## 3. Verificación rápida

```powershell
php artisan test          # 33 pruebas PHP (motor de partidas + flujo de novelas)
npm test                  # pruebas del sistema de temas
npm run build             # compilación de producción
composer audit            # sin avisos de seguridad
npm audit                 # sin vulnerabilidades
php artisan route:list    # rutas públicas y privadas
```

---

## 4. Mapa del sistema

### Flujo de datos

1. **Registro / inicio de sesión** → Breeze + Inertia. Todas las pantallas usan `GuestLayout`.
2. **Mis novelas** → el autor crea la novela, sus personajes, escenas, nodos de diálogo, opciones, variables y efectos.
3. **Explorar** (`/explore`) → catálogo público de novelas publicadas, con búsqueda por texto y etiqueta y botón de «Me gusta» (requiere sesión).
4. **Jugar** (`/novels/{novel}/play`) → `PlaythroughController` crea o recupera una partida y `PlaythroughEngine` aplica efectos y decide el siguiente nodo.

### Modelos (todos documentados con PHPDoc en `app/Models`)

| Modelo | Función |
| --- | --- |
| `User` | Autor o lector; relaciona novelas, partidas y likes. |
| `Novel` | Obra; estados `draft` / `published`, portada, etiquetas y likes. |
| `Tag` | Etiqueta reutilizable, se crea en minúsculas. |
| `Character` / `CharacterExpression` | Personaje y sus imágenes de expresión. |
| `Scene` | Escena dentro de una novela; agrupa nodos. |
| `DialogueNode` | Fragmento de diálogo con texto, personaje y salida lineal. |
| `Choice` | Opción del jugador; lleva efectos y nodo de destino. |
| `Variable` / `VariableEffect` | Valor de partida y operación `set`, `increment` o `decrement`. |
| `Playthrough` / `PlaythroughVariableValue` | Partida de un usuario y valores acumulados de sus variables. |

### Controladores y servicios

Cada controlador tiene PHPDoc de clase y de método explicando validación, autorización y respuesta. Los grupos son:

- `NovelController`, `SceneController`, `CharacterController`, `CharacterExpressionController`, `DialogueNodeController`, `ChoiceController`, `VariableController`, `VariableEffectController`: CRUD con `authorize()` y validación con alcance (un autor no puede referenciar datos de otra novela).
- `PublicNovelController` y `NovelLikeController`: catálogo y likes idempotentes.
- `PlaythroughController`: inicia, reinicia y avanza partidas dentro de transacciones con bloqueo.
- `PlaythroughEngine`: valida la opción, crea valores de variable ausentes y aplica efectos.
- `ValidadorDeNodos`: impide saltos a nodos de otra novela y ciclos.
- `NovelPolicy`: solo el dueño puede editar o borrar una novela.

---

## 5. Sistema de temas

Los seis temas viven en `resources/css/app.css` como variables CSS (`--vn-page-bg`, `--vn-surface`, `--vn-text`, `--vn-accent`, …). Todos definen **las mismas 23 variables**, de modo que ningún componente hereda un color del tema anterior.

| Identificador | Etiqueta |
| --- | --- |
| `current` | Púrpura (predeterminado) |
| `gothic` | Monja gótica |
| `hopecore` | Esperanza luminosa |
| `dark` | Oscuro |
| `white` | Blanco |
| `hellfire` | Amor infernal |

- `resources/js/theme.js` normaliza y persiste la elección en `localStorage` (`vn-maker-theme`).
- `resources/js/composables/useTheme.js` mantiene un `ref` global y escucha cambios de otras pestañas, para que los selectores nunca se desincronicen.
- `resources/views/app.blade.php` aplica el tema **antes** de cargar Vite, evitando el destello púrpura inicial.
- El reproductor usa clases `vn-game-*`, `vn-dialogue` y `vn-choice`, gobernadas por esas mismas variables: cambiar de tema también cambia el juego.

---

## 6. Componentes Vue compartidos

Todos están en `resources/js/Components` con `<script setup>`, props y eventos explícitos. Centralizan la interfaz para no repetir marcado ni estados.

| Componente | Propósito | API principal |
| --- | --- | --- |
| `SettingsCard` | Tarjeta de ajustes del perfil. | `title`, `description`, slot. |
| `SectionHeading` | Título + descripción de una sección. | `title`, `description`. |
| `SaveButton` | Botón con estados Guardar / Guardando… / Guardado. | `processing`, `success`, `label`, eventos `click`. |
| `ConfirmDeleteButton` | Confirmación con modal, sin `confirm()` nativo. | `title`, `message`, `label`, `processing`, evento `confirm`. |
| `EmptyState` | Estado vacío con acción opcional. | `title`, `description`, `actionLabel`, `actionHref`. |
| `LikeButton` | Me gusta optimista y accesible. | `count`, `liked`, `disabled`, evento `toggle`. |
| `FormField` | Etiqueta, control, error y pista; añade `aria-invalid`. | `label`, `error`, `id`, slots `default` y `hint`. |
| `ClampedText` | Texto largo recortado a dos líneas con título completo. | `text`, `lines`. |
| `GameFrame`, `GameTopBar`, `DialogueBox`, `ChoiceButton`, `GameAudioToggle` | Reproductor temeado, con una sola salida, audio controlable y bloqueo durante el avance. | ver cada archivo. |
| `ThemePicker` | Selector de tema sincronizado entre pestañas. | usa `useTheme()`. |

Los listados (`Novels`, `Characters`, `Scenes`, `Variables`, `Choices`, `DialogueNodes`) reutilizan estos componentes. Ya no hay `confirm()` nativo, botones sin feedback ni bloques `<style scoped>` duplicados.

Las tarjetas de **Mis novelas** y **Explorar** usan la familia `.vn-tile`, definida en `resources/css/app.css`: portada con proporción fija, título, descripción recortada, etiquetas, autor y acciones dentro de una misma superficie temeada. En Explorar, un visitante sin sesión ve «Inicia sesión para dar Me gusta» en lugar de un botón que no puede usar; el layout compartido ya no falla cuando `auth.user` es `null`.

---

## 7. Idioma

- La interfaz usa **español neutro** (tú): «Inicia sesión», «Regístrate», «Crea tu cuenta», «¿Ya tienes cuenta? Inicia sesión».
- No quedan textos en inglés en pantallas, títulos del navegador, placeholders ni mensajes de validación.
- Las traducciones de Laravel están en `lang/es/validation.php`, `auth.php` y `passwords.php`.
- Los nombres de tema también están traducidos.
- Los plurales se calculan con `computed` («nodo / nodos», «elección / elecciones»); se eliminó la notación `(s)`.

---

## 8. Seguridad y correr correctamente bajo carga

- Las opciones de partida se validan contra el nodo actual y la novela de la partida: no se puede colar un `choice_id` ajeno.
- Todo avance, inicio y reinicio de partida, y el alternar likes, ocurre dentro de `DB::transaction()` con `lockForUpdate()`: dobles clics no duplican efectos ni rompen claves únicas.
- Las validaciones `exists` llevan alcance por novela/escena; un autor no puede guardar referencias cruzadas.
- No se pueden borrar escenas, nodos ni variables referenciadas por partidas en curso.
- Las portadas se borran después de guardar la nueva y también al eliminar la novela o la cuenta.
- `/explore` es público; el resto de la edición exige `auth` y `verified`.
- Las rutas `resource` solo exponen acciones implementadas, evitando 500 por métodos inexistentes.

---

## 9. Documentación de cambios

El detalle de cada cambio, con su motivo y su alcance, está en **[CHANGELOG.md](CHANGELOG.md)**. El código lleva PHPDoc en español en modelos, controladores, servicios, policy, middleware y requests; los componentes Vue nuevas tienen comentarios breves cuando el propósito no es evidente.

---

## 10. Novelas de ejemplo

Ambas novelas se crean en la cuenta de Gabriel (`admin@ionys.com.py`) con seeders
**idempotentes**: si la novela ya existe, no la duplican. Cada una inicializa también su
partida para que pueda continuarla al abrirla.

| Novela | Comando | Contenido |
| --- | --- | --- |
| **Shinshin Jimi: el color de lo que no se dice** | `php artisan db:seed --class=ShinshinJimiSeeder` | 80 nodos, 18 decisiones, 6 personajes, 5 variables. Yuri de ambiente anime sobre dibujo, trenes y guitarra. |
| **Hechizos de luna: el pacto de las magas** | `php artisan db:seed --class=MagasAnimeSeeder` | 88 nodos, 18 decisiones, 6 personajes, 5 variables. Yuri de chicas mágicas anime sobre tinta, luna y un eclipse. |

Los recursos SVG (portada, fondo y 42 retratos por novela) se generan en `public/storage`.
Los repartos son originales —Shin, Jimi, Nana, Rina, Yui y Aya; Mika, Rin, Sora, Yuna,
Kira y Nao— para no usar personajes de terceros.

---

## 11. Lo que queda fuera

- No se añadió integración continua: las comprobaciones se ejecutan con los comandos de la sección 3.
- SQLite en pruebas no bloquea filas con `lockForUpdate()`; la protección contra concurrencia real depende de MySQL/PostgreSQL en producción.
- Los saltos entre nodos se limitan a la misma escena, que es lo que ofrece la interfaz de edición.
