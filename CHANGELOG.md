# Registro de cambios de la revisión total

Fecha: 19 de noviembre de 2026.
Alcance: todo el repositorio (backend Laravel, frontend Vue, configuración, idioma, pruebas y documentación).

Cada entrada explica **qué se cambió**, **dónde** y **por qué**. Los modelos y controladores además llevan PHPDoc en español junto a cada clase y método.

---

## 1. Arranque, base de datos y dependencias

| Cambio | Archivos | Motivo |
| --- | --- | --- |
| Creado `.env` local con MySQL, contraseña `ionysmaster666`, Idioma `es`, `APP_DEBUG=true` y clave generada. | `.env` (ignorado por Git) | La aplicación no arrancaba: no existía `.env` y Laravel caía en `production` sin clave. |
| Base de datos `visual_novel_maker` creada en MySQL 8.0.35 y migraciones ejecutadas. | MySQL + `database/migrations` | Las 17 migraciones fallaban sin base de datos; ahora las tablas existen. |
| Plantilla de entorno convertida a español y MySQL, con `APP_DEBUG=false` ymarca `VN Maker`. | `.env.example` | La plantilla anterior dejaba la app en inglés, con SQLite y depuración abierta en producción. |
| Laravel Boost instalado como dependencia de desarrollo y guías generadas. | `composer.json`, `composer.lock`, `CLAUDE.md`, `boost.json` | Requisito de `AGENTS.md`. Las guías quedan en `CLAUDE.md`; `AGENTS.md` se actualizó para no repetir la instalación. |
| Actualizadas 65 dependencias PHP (`laravel/framework` 13.32 → 13.43, Symfony, Guzzle, PHPUnit…). | `composer.lock` | `composer audit` reportaba 10 avisos de seguridad en 6 paquetes; ahora informa 0. |
| Instaladas dependencias npm y corregidas 7 vulnerabilidades (Vite, `shell-quote`, `follow-redirects`, PostCSS…). | `package-lock.json` | `npm audit` pasó de 7 avisos (2 críticos) a 0. |
| Eliminado `@tailwindcss/vite` (Tailwind 4) porque el proyecto compila con Tailwind 3 + PostCSS. | `package.json`, `package-lock.json` | Dos motores CSS simultáneos hacían ambigua la compilación. |
| `.npmrc` ya no desactiva los scripts de instalación y fija `package-lock`. | `.npmrc` | `ignore-scripts=true` impedía builds reproducibles. |
| `composer setup` usa `npm ci` en vez de `npm install --ignore-scripts`. | `composer.json` | Instalación determinista y con scripts de dependencias activos. |
| Eliminados los archivos accidentales `id`, `scenes`, `tags`, `sync([` y el duplicado `yarn.lock`. | raíz del repositorio | Eran ficheros de 0 bytes creados por un comando mal pegado; `yarn.lock` convivía con `package-lock.json`. |

---

## 2. Backend: correcciones funcionales y de seguridad

| Cambio | Archivos | Motivo |
| --- | --- | --- |
| `NovelController::store()` ahora crea la novela del usuario autenticado. | `app/Http/Controllers/NovelController.php` | El formulario daba «éxito» pero no guardaba nada: era el bloqueo principal del sistema. |
| Validación de opciones de partida: deben pertenecer al nodo actual y a la novela de la partida; se exige `choice_id` si el nodo tiene opciones. | `PlaythroughController.php`, `PlaythroughEngine.php` | Un `choice_id` de otra novela permitía saltar a contenido ajeno (IDOR) y aplicar sus efectos. |
| Avance, inicio y reinicio de partidas envueltos en `DB::transaction()` con `lockForUpdate()`. | `PlaythroughController.php`, `models/Playthrough.php` | Dos peticiones simultáneas duplicaban efectos o violaban `unique(user_id, novel_id)`. |
| Los efectos crean la fila de variable ausente con `default_value` (o 0) y el `match` tiene caso `default`. | `PlaythroughEngine.php` | Crear una variable después de iniciar la partida provocaba un 500 al elegir una opción. |
| Nuevo `ValidadorDeNodos`: prohíbe `next_node_id` propio y detecta ciclos de nodos y opciones. | `app/Services/ValidadorDeNodos.php`, controladores de nodos y opciones | Un nodo que se apuntaba a sí mismo atrapaba al jugador para siempre. |
| Validaciones `exists` con alcance por novela/escena para personajes, expresiones y nodos destino. | `DialogueNodeController.php`, `ChoiceController.php` | Un autor podía guardar referencias a datos de otra novela; la UI ya impedía hacerlo. |
| Rutas `resource` limitadas a las acciones realmente implementadas. | `routes/web.php` | Rutas como `GET /novels/1` llamaban a métodos inexistentes y devolvían 500. |
| `GET /explore` movido fuera del grupo `auth,verified`. | `routes/web.php` | El catálogo público exigía sesión. |
| Likes alternados de forma idempotente y serializados por usuario. | `NovelLikeController.php` | Doble clic concurrente rompía la restricción `unique(user_id, novel_id)`. |
| `is_liked_by_me` se calcula con `withExists()` en los listados y salió de `$appends`. | `Novel.php`, `NovelController.php`, `PublicNovelController.php` | Listar 50 novelas ejecutaba 50 consultas extra (problema N+1). |
| `update()` conserva las etiquetas si no se envía la clave `tags`; límite de 10 etiquetas y 5000 caracteres de descripción. | `NovelController.php` | Una actualización parcial borraba todas las etiquetas; no había límite de abuso. |
| La portada antigua se elimina solo después de guardar la nueva; `destroy()` limpia archivos. | `NovelController.php`, `ProfileController.php` | Si fallaba la subida, la base de datos apuntaba a un archivo ya borrado. |
| Borrado protegido de escenas, nodos y variables referenciadas por partidas, efectos o saltos. | controladores correspondientes | Borrar contenido en una novela publicada terminaba las partidas de los lectores. |
| Autorización con `NovelPolicy` en todas las operaciones de edición. | `app/Policies/NovelPolicy.php`, controladores | Garantiza que solo el dueño modifique su novela. |

---

## 3. Idioma español

| Cambio | Archivos | Motivo |
| --- | --- | --- |
| `config/app.php` fija `locale=es`, `fallback_locale=es` y `faker_locale=es_ES` por defecto. | `config/app.php` | Sin `.env`, los mensajes volvían al inglés. |
| Creadas traducciones completas de validación, autenticación y contraseñas. | `lang/es/validation.php`, `auth.php`, `passwords.php` | «The title field is required.» aparecía en una interfaz española. |
| Mensajes de error de los controladores de Auth traducidos. | `app/Http/Controllers/Auth/*.php`, `app/Http/Requests/**` | Se mostraban claves literales como `verification-link-sent`. |
| Todas las pantallas y títulos del navegador en español neutro. | `resources/js/**` | Convivían «Entrar», «Entrá», «Registrarme», «Reset Password» y «Dashboard». |
| Etiquetas de los temas traducidas: Oscuro, Blanco, Amor infernal, Esperanza luminosa. | `resources/js/theme.js`, `tests/js/theme.test.js` | «Dark», «White» y «Hellfire love» rompían la coherencia del idioma. |
| Plurales calculados en lugar de `(s)`. | páginas de gestión | «3 nodo(s) de diálogo» parecía texto sin terminar. |

---

## 4. UI/UX, movimiento visual y temas

| Cambio | Archivos | Motivo |
| --- | --- | --- |
| El tema se aplica en el `<head>` antes de Vite. | `resources/views/app.blade.php` | Evita el destello púrpura al recargar con otro tema guardado. |
| Fuentes Fredoka y Rubik cargadas una sola vez; eliminados Figtree y los `@import` repetidos. | `app.blade.php`, componentes | Cinco peticiones externas y parpadeo de tipografía. |
| Los seis temas definen las mismas 23 variables CSS. | `resources/css/app.css` | Algunos temas no definían `--vn-status` ni `--vn-danger-text` y heredaban colores del tema púrpura. |
| Clases `vn-*` centralizadas en `app.css`; eliminado el CSS `<style scoped>` duplicado de nueve páginas. | `app.css`, páginas Vue | El mismo botón tenía alturas y radios distintos según la página. |
| Estados hover/focus visibles, transiciones suaves y respeto a `prefers-reduced-motion`. | `app.css` | Mejora la lectura y no obliga a seguir animaciones. |
| Texto narrativo con `max-w-prose` e `leading-relaxed`; previsualizaciones recortadas con `ClampedText`. | `Play/Show.vue`, listados | Líneas de ~90 caracteres y tarjetas que crecían sin control cansaban la vista. |
| Selector de tema único en móvil y sincronizado entre pestañas. | `AuthenticatedLayout.vue`, `useTheme.js`, `ThemePicker.vue` | Aparecían dos selectores y el valor se desactualizaba. |
| Menú de usuario reemplazado por `Dropdown`/`DropdownLink` (cierre con Escape y overlay). | `AuthenticatedLayout.vue` | El menú artesanal no se cerraba y dejaba un enlace «Dashboard» inexistente. |
| Reproductor temeado con `GameFrame`, `GameTopBar`, `DialogueBox`, `ChoiceButton` y `GameAudioToggle`. | `Pages/Play/Show.vue` y componentes nuevos | El juego ignoraba el tema elegido, tenía tres formas de salir y música sin control de silencio. |
| Botones de avance y opciones desactivados mientras se envía la petición, con mensaje «Avanzando…». | `Play/Show.vue` | Clics rápidos saltaban diálogos y duplicaban efectos. |
| `Login.vue` usa `GuestLayout` como el resto de las pantallas de autenticación. | `Pages/Auth/Login.vue` | Era el único formulario sin selector de tema ni layout compartido. |
| Perfil con `SettingsCard`, `SectionHeading` y `FormField`; modal themeado. | `Pages/Profile/**`, `Modal.vue` | Las tarjetas del perfil seguían blancas con cualquier tema oscuro. |

---

## 5. Componentización y formularios

| Componente nuevo | Función documentada |
| --- | --- |
| `SaveButton.vue` | Unifica «Guardar», «Guardando…» y «Guardado»; recibe `processing` y `success`, desactiva el botón y emite `click`. |
| `ConfirmDeleteButton.vue` | Sustituye los siete `confirm()` nativos por un modal accesible; emite `confirm` y bloquea durante la petición. |
| `EmptyState.vue` | Distingue «no hay datos» de «no hay resultados de búsqueda» y ofrece limpiar filtros. |
| `LikeButton.vue` | Actualiza el contador de forma optimista, revierte en error y expone `aria-pressed`. |
| `FormField.vue` | Junta etiqueta, control, pista y error, y añade `aria-invalid` / `aria-describedby` al input. |
| `ClampedText.vue` | Recorta textos largos y conserva el contenido completo en el atributo `title`. |
| `SettingsCard.vue` / `SectionHeading.vue` | Contenedor y cabecera reutilizables del perfil. |
| `GameFrame.vue`, `GameTopBar.vue`, `DialogueBox.vue`, `ChoiceButton.vue`, `GameAudioToggle.vue` | Piezas del reproductor, todas gobernadas por variables de tema. |
| `composables/useTheme.js` | Estado global del tema, normalización y sincronización entre pestañas. |

Los archivos de página conservan su lógica de edición en línea (`editingId`, `editForm` y `watch`), pero ahora usan estos componentes en lugar de repetir marcado.

---

## 6. Pruebas y documentación

| Cambio | Archivos | Motivo |
| --- | --- | --- |
| Añadidos tests del motor de partidas (set/increment/decrement, variable ausente, operación inválida). | `tests/Unit/PlaythroughEngineTest.php` | La lógica central del producto no tenía ninguna prueba. |
| Añadidos tests de creación de novelas, autorización, etiquetas, likes y opciones ajenas. | `tests/Feature/NovelManagementTest.php` | Los errores más graves del sistema no estaban cubiertos. |
| Creadas factories de Novel, Scene, DialogueNode, Choice, Variable, VariableEffect y Playthrough. | `database/factories/**` | Eran necesarias para probar el flujo real sin datos manuales. |
| Cargadas clave, idioma y entorno fijos en PHPUnit. | `phpunit.xml` | La suite dependía del `.env` del desarrollador. |
| Test JS ejecutable con `npm test` y sin `try/catch` que ocultara fallos de importación. | `package.json`, `tests/js/theme.test.js` | El test existía pero nadie lo ejecutaba. |
| README completo en español con arranque MySQL, variables, mapa del sistema y catálogo de componentes. | `README.md` | El README anterior tenía solo dos líneas. |
| PHPDoc en los 12 modelos, 23 controladores, servicio, validador, policy, middleware y requests. | `app/**` | Requisito explícito de documentación y comentarios del sistema. |

---

## 7. Novelas largas de ejemplo

| Cambio | Archivos | Motivo |
| --- | --- | --- |
| Se añadió un seeder reproducible que crea «Shinshin Jimi: el color de lo que no se dice» en la cuenta de Gabriel. | `database/seeders/ShinshinJimiSeeder.php` | El usuario pidió una novela larga de temática lésbica y ambiente anime en su usuario. Como «Shinshin Jimi» no es un anime reconocido, se creó un reparto original en vez de usar personajes ajenos. |
| La historia tiene 80 nodos en nueve capítulos, 18 decisiones, cinco variables y final convergente. | `database/seeders/ShinshinJimiSeeder.php` | Cada decisión modifica confianza, coraje, secretos, celos o borradores, de modo que la partida refleje el tono de la relación. |
| Se generan portada, fondo y 42 retratos SVG, y se inicializa la partida de Gabriel. | `database/seeders/ShinshinJimiSeeder.php`, `public/storage/**` | La novela se ve completa sin imágenes externas y el autor puede abrirla y continuarla de inmediato. |
| Se documentó el comando de siembra. | `README.md` | Para poder recrear la novela en cualquier instalación. |
| Se añadió «Hechizos de luna: el pacto de las magas», otra novela yuri de anime en la cuenta de Gabriel. | `database/seeders/MagasAnimeSeeder.php` | El usuario pidió una segunda historia de magas anime; se creó un reparto original de chicas mágicas en vez de usar personajes de terceros. |
| La nueva historia tiene 88 nodos en nueve capítulos, 18 decisiones, cinco variables y un final bajo el eclipse. | `database/seeders/MagasAnimeSeeder.php` | Las decisiones modifican confianza, coraje, resonancia mágica, secretos y recuerdos, para que la partida refleje el costo de los hechizos. |
| Se generaron portada, fondo y 42 retratos SVG para la nueva novela, y se inicializó su partida. | `database/seeders/MagasAnimeSeeder.php`, `public/storage/**` | La novela se ve completa sin imágenes externas y Gabriel puede abrirla de inmediato. |
| Se amplió la documentación con el segundo seeder. | `README.md` | Para recrear cualquiera de las dos novelas en otra instalación. |

---

## 8. Tarjetas de los índices y exploración pública

| Cambio | Archivos | Motivo |
| --- | --- | --- |
| Se añadieron los estilos base de `.vn-tile`, portada, título, descripción, etiquetas, insignias y acciones. | `resources/css/app.css` | Las clases se usaban en `Novels/Index.vue` y `Novels/Public.vue`, pero no tenían definición: las tarjetas se veían como texto e imágenes sin contenedor. Ahora forman una tarjeta temeada con hover, bordes, sombra y acciones legibles. |
| El layout compartido tolera visitantes sin sesión. | `resources/js/Layouts/AuthenticatedLayout.vue` | `/explore` es público y `auth.user` es `null`; el encabezado intentaba leer `.name` y dejaba la página en blanco. Ahora muestra «Invitado» y enlaces de inicio de sesión y registro. |
| En Explorar, los visitantes ven un enlace para iniciar sesión en vez de un «Me gusta» que no pueden usar. | `resources/js/Pages/Novels/Public.vue` | Evita un botón que parecía funcional pero fallaba sin autenticación. |

---

## 9. Estado final de las comprobaciones

- `php artisan migrate --force`: 17 migraciones aplicadas en MySQL.
- `php artisan test`: en verde tras ajustar dos pruebas del motor (la opción no cambia de nodo y la restricción de la tabla impide guardar una operación inválida, por lo que se simula en memoria).
- `npm test`: en verde.
- `npm run build`: compilado sin errores.
- `composer audit`: 0 avisos.
- `npm audit`: 0 vulnerabilidades.
- `php -l`: sin errores de sintaxis en los 56 archivos PHP modificados y creados.
