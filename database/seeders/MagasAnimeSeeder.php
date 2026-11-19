<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\CharacterExpression;
use App\Models\Choice;
use App\Models\DialogueNode;
use App\Models\Novel;
use App\Models\Playthrough;
use App\Models\Scene;
use App\Models\Tag;
use App\Models\User;
use App\Models\Variable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Crea la novela larga «Hechizos de luna: el pacto de las magas»
 * en la cuenta de Gabriel. Es idempotente: si la novela ya existe, no la duplica.
 *
 * Es una historia yuri de ambiente anime protagonizada por magas originales:
 * Mika dibuja hechizos con tinta y Rin los enciende con la luna.
 */
class MagasAnimeSeeder extends Seeder
{
    /**
     * Punto de entrada del seeder.
     */
    public function run(): void
    {
        $gabriel = User::where('email', 'admin@ionys.com.py')->firstOrFail();

        $this->crearRecursosGraficos();

        $titulo = 'Hechizos de luna: el pacto de las magas';

        if (Novel::where('title', $titulo)->exists()) {
            $this->command?->info('La novela ya existe; no se duplicó.');

            return;
        }

        $novel = Novel::create([
            'user_id' => $gabriel->id,
            'title' => $titulo,
            'description' => <<<'TEXTO'
Mika Aoi dibuja runas en los márgenes de sus libros porque no confía en su voz. Rin Kagurazaka enciende esas runas con la luna porque no confía en quedarse quieta. Cuando una estrella cae sobre la librería de Kagurazaka, las dos descubren que el hechizo más poderoso no es la luz, sino la forma en que una mira a la otra.

Una novela visual yuri de chicas mágicas: nueve capítulos, dieciocho decisiones que cambian la confianza, la magia y los recuerdos compartidos, y un eclipse que exige un precio que ninguna de las dos quiere pagar sola.
TEXTO,
            'cover_image' => 'covers/hechizos-de-luna.svg',
            'status' => 'published',
        ]);

        $this->asignarEtiquetas($novel);
        $personajes = $this->crearPersonajes($novel);
        $variables = $this->crearVariables($novel);
        $escena = Scene::create([
            'novel_id' => $novel->id,
            'title' => 'De la librería al eclipse',
            'background_image' => 'backgrounds/tejado-lunar.svg',
            'background_music' => null,
            'order' => 1,
        ]);

        $this->crearHistoria($escena, $personajes, $variables);

        Playthrough::firstOrCreateFor($gabriel->id, $novel->id);

        $this->command?->info('Novela creada: '.$titulo);
    }

    /**
     * Etiquetas del catálogo público.
     */
    private function asignarEtiquetas(Novel $novel): void
    {
        $nombres = ['yuri', 'anime', 'magia', 'chicas mágicas', 'romance', 'instituto', 'fantasía'];

        $ids = collect($nombres)
            ->map(fn (string $nombre) => Tag::firstOrCreate(['name' => $nombre])->id);

        $novel->tags()->sync($ids);
    }

    /**
     * Reparto original. Cada personaje tiene siete expresiones reutilizables.
     *
     * @return array<string, Character>
     */
    private function crearPersonajes(Novel $novel): array
    {
        $datos = [
            'mika' => ['Mika Aoi', '#8fb3ff'],
            'rin' => ['Rin Kagurazaka', '#ff8ad1'],
            'sora' => ['Sora Tsukino', '#9dffd6'],
            'yuna' => ['Yuna Hoshino', '#ffd27f'],
            'kira' => ['Kira Mizuno', '#c59cff'],
            'nao' => ['Nao Serizawa', '#ff9c8a'],
        ];

        $expresiones = ['neutro', 'feliz', 'triste', 'sorprendida', 'enamorada', 'avergonzada', 'determinada'];
        $personajes = [];

        foreach ($datos as $clave => [$nombre, $color]) {
            $personaje = Character::create([
                'novel_id' => $novel->id,
                'name' => $nombre,
                'color' => $color,
            ]);

            foreach ($expresiones as $expresion) {
                CharacterExpression::create([
                    'character_id' => $personaje->id,
                    'name' => $expresion,
                    'image_path' => "expressions/{$clave}-{$expresion}.svg",
                ]);
            }

            $personajes[$clave] = $personaje;
        }

        return $personajes;
    }

    /**
     * Variables que registran el costo y el tono de los hechizos.
     *
     * @return array<string, Variable>
     */
    private function crearVariables(Novel $novel): array
    {
        $datos = [
            'confianza' => ['Confianza compartida', 0],
            'coraje' => ['Coraje de Mika', 1],
            'magia' => ['Resonancia mágica', 2],
            'secretos' => ['Secretos compartidos', 0],
            'recuerdos' => ['Recuerdos guardados', 0],
        ];

        $variables = [];

        foreach ($datos as $clave => [$etiqueta, $valor]) {
            $variables[$clave] = Variable::create([
                'novel_id' => $novel->id,
                'key' => $clave,
                'label' => $etiqueta,
                'default_value' => $valor,
            ]);
        }

        return $variables;
    }

    /**
     * Crea todos los nodos y, en una segunda pasada, sus saltos y opciones.
     * Se hace en dos fases para poder referenciar nodos por nombre simbólico.
     */
    private function crearHistoria(Scene $escena, array $personajes, array $variables): void
    {
        $historia = $this->guion();
        $ids = [];
        $orden = 1;

        foreach ($historia as $capitulo) {
            foreach ($capitulo['nodos'] as $clave => $nodo) {
                $creado = DialogueNode::create([
                    'scene_id' => $escena->id,
                    'character_id' => isset($nodo['personaje']) ? $personajes[$nodo['personaje']]->id : null,
                    'character_expression_id' => isset($nodo['personaje'], $nodo['expresion'])
                        ? $personajes[$nodo['personaje']]->expressions()->where('name', $nodo['expresion'])->firstOrFail()->id
                        : null,
                    'text' => $nodo['texto'],
                    'next_node_id' => null,
                    'order' => $orden++,
                ]);

                $ids[$clave] = $creado->id;
            }
        }

        foreach ($historia as $capitulo) {
            foreach ($capitulo['nodos'] as $clave => $nodo) {
                $creado = DialogueNode::findOrFail($ids[$clave]);

                if (! empty($nodo['siguiente'])) {
                    $creado->update(['next_node_id' => $ids[$nodo['siguiente']]]);
                }

                foreach ($nodo['opciones'] ?? [] as $indice => $opcion) {
                    $elegida = Choice::create([
                        'dialogue_node_id' => $creado->id,
                        'text' => $opcion['texto'],
                        'next_node_id' => $ids[$opcion['destino']],
                        'order' => $indice + 1,
                    ]);

                    foreach ($opcion['efectos'] ?? [] as $efecto) {
                        $elegida->variableEffects()->create([
                            'variable_id' => $variables[$efecto[0]]->id,
                            'operation' => $efecto[1],
                            'value' => $efecto[2],
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Guion completo: nueve capítulos, decisiones que convergen y final bajo el eclipse.
     *
     * @return array<int, array{nodos: array<string, array<string, mixed>>}>
     */
    private function guion(): array
    {
        return [
            [
                'nodos' => [
                    'c1_01' => ['texto' => 'Tokio, 21:14. La librería Kagurazaka huele a papel viejo y a lluvia. Mika Aoi está copiando una runa cuando la lámpara parpadea y algo cae del cielo entre los estantes.', 'siguiente' => 'c1_02'],
                    'c1_02' => ['personaje' => 'mika', 'expresion' => 'neutro', 'texto' => 'Solo buscaba un libro de hechizos. No esperaba que uno se me cayera encima… ni que brillara.', 'siguiente' => 'c1_03'],
                    'c1_03' => ['texto' => 'La puerta se abre sin viento. Una chica con lazo lunar y varita entró corriendo, con el aliento todavía cargado de estrellas.', 'siguiente' => 'c1_04'],
                    'c1_04' => ['personaje' => 'rin', 'expresion' => 'feliz', 'texto' => '¿También oíste caer la estrella? ¡Venga, dime que sí! No soporto ser la única que la vio.', 'siguiente' => 'c1_05'],
                    'c1_05' => ['texto' => 'Rin Kagurazaka sostiene un fragmento del tamaño de una moneda. No quema. Late. Como si supiera que alguien más lo está mirando.', 'siguiente' => 'c1_06'],
                    'c1_06' => ['personaje' => 'mika', 'expresion' => 'sorprendida', 'texto' => 'Yo… no hago magia de verdad. Solo dibujo runas en los márgenes. ¿Seguro que es conmigo?', 'opciones' => [
                        ['texto' => 'Tocar la estrella junto a Rin', 'destino' => 'c1_07a', 'efectos' => [['confianza', 'increment', 2], ['magia', 'increment', 1]]],
                        ['texto' => 'Retirar la mano', 'destino' => 'c1_07b', 'efectos' => [['coraje', 'decrement', 1]]],
                    ]],
                    'c1_07a' => ['texto' => 'Ambas manos se posan sobre el fragmento. La luz se dobla y dibuja una constelación que las dos reconocen sin haberla estudiado.', 'siguiente' => 'c1_08'],
                    'c1_07b' => ['personaje' => 'rin', 'expresion' => 'triste', 'texto' => 'Está bien. Las estrellas también saben esperar. Aunque se les dé mal disimularlo.', 'siguiente' => 'c1_08'],
                    'c1_08' => ['personaje' => 'mika', 'expresion' => 'avergonzada', 'texto' => 'Me llamo Mika. Dibujo lo que no me atrevo a decir. Gracias por no reírte del margen.', 'siguiente' => 'c1_09'],
                    'c1_09' => ['texto' => 'Esa noche, la luna se partió en dos sobre el tejado. Mika no supo si era el hechizo o la primera vez que alguien la miraba de verdad.', 'siguiente' => 'c2_01'],
                ],
            ],
            [
                'nodos' => [
                    'c2_01' => ['personaje' => 'sora', 'expresion' => 'feliz', 'texto' => '¡Mika! ¡El gato mensajero otra vez! Trae una tarjeta con olor a tinta y a luna. Eso nunca es bueno… nunca es malo… es emocionante.', 'siguiente' => 'c2_02'],
                    'c2_02' => ['texto' => 'Un gato negro con collar de estrella deja caer una invitación sobre el mostrador, en la esquina: Club de Magia de la Escuela. Se necesitan dos.', 'siguiente' => 'c2_03'],
                    'c2_03' => ['personaje' => 'rin', 'expresion' => 'neutro', 'texto' => 'Dos. Tú y yo. Ya lo decidí antes de preguntarte, lo siento. Es un defecto mío.', 'siguiente' => 'c2_04'],
                    'c2_04' => ['personaje' => 'mika', 'expresion' => 'neutro', 'texto' => 'Yo solo sé hacer tinta invisible. No creo que eso cuente como magia.', 'siguiente' => 'c2_05'],
                    'c2_05' => ['personaje' => 'kira', 'expresion' => 'feliz', 'texto' => 'La tinta invisible es la mejor base para un hechizo, Mika. Lo invisible también quiere decir algo.', 'siguiente' => 'c2_06'],
                    'c2_06' => ['personaje' => 'rin', 'expresion' => 'neutro', 'texto' => 'Entonces… ¿te sientas conmigo? Prometo no lanzar rayos. Prometo intentarlo.', 'opciones' => [
                        ['texto' => 'Sentarse al lado de Rin', 'destino' => 'c2_07a', 'efectos' => [['confianza', 'increment', 3], ['secretos', 'increment', 1]]],
                        ['texto' => 'Esconderse detrás de Sora', 'destino' => 'c2_07b', 'efectos' => [['coraje', 'decrement', 1], ['confianza', 'increment', 1]]],
                    ]],
                    'c2_07a' => ['texto' => 'Mika se sienta. Sus rodillas casi se tocan. Rin abre su grimorio y la tinta de Mika brilla por primera vez sin que nadie la nombre.', 'siguiente' => 'c2_08'],
                    'c2_07b' => ['personaje' => 'sora', 'expresion' => 'sorprendida', 'texto' => 'Oye, soy un escudo precioso, pero mi turno de ser tu excusa terminó hace tres minutos.', 'siguiente' => 'c2_08'],
                    'c2_08' => ['personaje' => 'nao', 'expresion' => 'feliz', 'texto' => 'Bienvenida al club, Mika. Aquí nadie se ríe de los bocetos… todavía.', 'siguiente' => 'c2_09'],
                    'c2_09' => ['texto' => 'El gato maulló una sola vez, como si aprobara. Rin sonrió sin mirar a nadie, y Mika guardó esa sonrisa en el margen.', 'siguiente' => 'c3_01'],
                ],
            ],
            [
                'nodos' => [
                    'c3_01' => ['texto' => 'Las runas de Mika y la varita de Rin encuentran el mismo compás. Una línea, una chispa. Otra línea, otra chispa.', 'siguiente' => 'c3_02'],
                    'c3_02' => ['personaje' => 'rin', 'expresion' => 'neutro', 'texto' => 'Cuando dibujas esta estación, ¿por qué siempre sale una luna partida en dos?', 'siguiente' => 'c3_03'],
                    'c3_03' => ['personaje' => 'mika', 'expresion' => 'avergonzada', 'texto' => 'Porque… una luna entera no sabría mirarme. Las dos, en cambio, se buscan.', 'siguiente' => 'c3_04'],
                    'c3_04' => ['personaje' => 'rin', 'expresion' => 'determinada', 'texto' => 'Enséñame la página que no copias. En serio. Lo que escondes en la última hoja.', 'opciones' => [
                        ['texto' => 'Mostrar el grimorio secreto', 'destino' => 'c3_05a', 'efectos' => [['secretos', 'increment', 3], ['confianza', 'increment', 2]]],
                        ['texto' => 'Cerrar el cuaderno', 'destino' => 'c3_05b', 'efectos' => [['secretos', 'decrement', 1], ['confianza', 'increment', 1]]],
                    ]],
                    'c3_05a' => ['texto' => 'La última página muestra a dos magas bajo el mismo eclipse. Una sostiene una varita; la otra, un cálamo. Rin se queda sin aire.', 'siguiente' => 'c3_06'],
                    'c3_05b' => ['personaje' => 'mika', 'expresion' => 'triste', 'texto' => 'Todavía no. Hay hechizos que ni yo sé leer.', 'siguiente' => 'c3_06'],
                    'c3_06' => ['personaje' => 'rin', 'expresion' => 'enamorada', 'texto' => 'Está bien. Puedo esperar. La luna tarda exactamente lo que tiene que tardar.', 'siguiente' => 'c3_07'],
                    'c3_07' => ['texto' => 'Mika dibuja, sin darse cuenta, una nueva página. Esta vez el lazo de Rin también está en el cielo.', 'siguiente' => 'c3_08'],
                    'c3_08' => ['personaje' => 'kira', 'expresion' => 'neutro', 'texto' => 'El primer hechizo compartido ya está escrito. Solo falta que alguien se atreva a pronunciarlo.', 'siguiente' => 'c3_09'],
                    'c3_09' => ['texto' => 'Esa tarde, el club dejó de ser un sitio para practicar y empezó a ser un sitio donde quedarse.', 'siguiente' => 'c4_01'],
                ],
            ],
            [
                'nodos' => [
                    'c4_01' => ['personaje' => 'nao', 'expresion' => 'sorprendida', 'texto' => '¡No! ¡Se apagó la estrella de cristal del tejado! ¡Sin eso no hay portal!', 'siguiente' => 'c4_02'],
                    'c4_02' => ['personaje' => 'yuna', 'expresion' => 'neutro', 'texto' => 'La maga del sol la recogió antes. No fue un robo. Fue una costumbre muy mala.', 'siguiente' => 'c4_03'],
                    'c4_03' => ['personaje' => 'rin', 'expresion' => 'triste', 'texto' => 'Sin el cristal no podremos abrir el portal del eclipse. Y el eclipse no avisa dos veces.', 'siguiente' => 'c4_04'],
                    'c4_04' => ['personaje' => 'mika', 'expresion' => 'determinada', 'texto' => 'Puedo dibujar una copia. Con tinta y con tu luna. Si te parece bien…', 'opciones' => [
                        ['texto' => 'Hacerlo y decir que es por Rin', 'destino' => 'c4_05a', 'efectos' => [['coraje', 'increment', 3], ['confianza', 'increment', 2], ['magia', 'increment', 1]]],
                        ['texto' => 'Hacerlo en silencio', 'destino' => 'c4_05b', 'efectos' => [['coraje', 'decrement', 1], ['confianza', 'increment', 1], ['magia', 'increment', 1]]],
                    ]],
                    'c4_05a' => ['personaje' => 'rin', 'expresion' => 'sorprendida', 'texto' => '¿Por mí? Mika, eso es un hechizo más poderoso que cualquier cristal. No sabes lo que acabas de hacer.', 'siguiente' => 'c4_06'],
                    'c4_05b' => ['texto' => 'La copia aparece al día siguiente. No lleva firma, pero Rin reconoce el trazo de la estación al instante.', 'siguiente' => 'c4_06'],
                    'c4_06' => ['texto' => 'El portal se abre con treinta chispas. Entre ellas, una chica que no recuerda haber decidido entrar.', 'siguiente' => 'c4_07'],
                    'c4_07' => ['personaje' => 'rin', 'expresion' => 'feliz', 'texto' => 'Esta va para quien dibuja lunas que no existen. Que sepan que ya no están solas.', 'siguiente' => 'c4_08'],
                    'c4_08' => ['texto' => 'Mika esconde la cara en el grimorio. La tinta del nuevo hechizo todavía está húmeda.', 'siguiente' => 'c4_09'],
                    'c4_09' => ['personaje' => 'yuna', 'expresion' => 'determinada', 'texto' => 'Nadie toca la luz del sol sin pagarlo. Y tú, Mika, ya has pagado demasiado.', 'siguiente' => 'c5_01'],
                ],
            ],
            [
                'nodos' => [
                    'c5_01' => ['personaje' => 'yuna', 'expresion' => 'neutro', 'texto' => 'Hola, Mika. Soy Yuna. Rin y yo compartíamos hechizos antes de que alguien dibujara su luna.', 'siguiente' => 'c5_02'],
                    'c5_02' => ['texto' => 'Yuna sonríe como quien conoce todos los atajos del pasillo y ninguno de los caminos del corazón.', 'siguiente' => 'c5_03'],
                    'c5_03' => ['personaje' => 'mika', 'expresion' => 'neutro', 'texto' => 'Rin no habla mucho de antes. Yo… tampoco quería preguntar.', 'siguiente' => 'c5_04'],
                    'c5_04' => ['personaje' => 'yuna', 'expresion' => 'triste', 'texto' => 'Antes no hablaba de nada. Hasta que alguien le dibujó una luna que sí sabía mirarla.', 'opciones' => [
                        ['texto' => 'Contar la verdad sobre el grimorio', 'destino' => 'c5_05a', 'efectos' => [['secretos', 'increment', 2], ['confianza', 'increment', 2], ['recuerdos', 'increment', 1]]],
                        ['texto' => 'Proteger el secreto de Rin', 'destino' => 'c5_05b', 'efectos' => [['secretos', 'increment', 1], ['recuerdos', 'decrement', 1]]],
                    ]],
                    'c5_05a' => ['personaje' => 'yuna', 'expresion' => 'sorprendida', 'texto' => 'Ah. Entonces escribes sobre ella. Creí que solo dibujabas lunas, pero dibujas la forma en que alguien espera.', 'siguiente' => 'c5_06'],
                    'c5_05b' => ['texto' => 'Mika se encoge de hombros. Es la primera mentira que le sale fácil, y por eso mismo le duele.', 'siguiente' => 'c5_06'],
                    'c5_06' => ['personaje' => 'kira', 'expresion' => 'neutro', 'texto' => 'Cada hechizo guarda un recuerdo, Mika. Cuidado con cuál gastas y con cuál decides guardar.', 'siguiente' => 'c5_07'],
                    'c5_07' => ['personaje' => 'mika', 'expresion' => 'enamorada', 'texto' => 'Si se lo doy, dejará de ser un hechizo. Si no se lo doy, seguirá siendo una costumbre.', 'siguiente' => 'c5_08'],
                    'c5_08' => ['texto' => 'La carta duerme dentro del grimorio. Empieza con «Rin» y termina con una página en blanco.', 'siguiente' => 'c5_09'],
                    'c5_09' => ['texto' => 'Mañana habrá eclipse, y los eclipses no perdonan las palabras guardadas.', 'siguiente' => 'c6_01'],
                ],
            ],
            [
                'nodos' => [
                    'c6_01' => ['texto' => 'La azotea del instituto huele a verano adelantado. El cielo tiene ese naranja que parece pedir disculpas por irse.', 'siguiente' => 'c6_02'],
                    'c6_02' => ['personaje' => 'rin', 'expresion' => 'neutro', 'texto' => 'Sora dice que el eclipse necesita dos magas. Yo dije que sí antes de preguntar cuál era el precio. Es un don.', 'siguiente' => 'c6_03'],
                    'c6_03' => ['personaje' => 'mika', 'expresion' => 'neutro', 'texto' => 'El don de decir que sí y luego mirar al cielo buscando instrucciones.', 'siguiente' => 'c6_04'],
                    'c6_04' => ['personaje' => 'rin', 'expresion' => 'enamorada', 'texto' => 'A veces miro la luna y ya sé la respuesta. Solo que no sé si mi boca sabe pronunciarla.', 'siguiente' => 'c6_05'],
                    'c6_05' => ['texto' => 'Silencio. El tipo de silencio que no se rompe: se deja caer como una hora más.', 'siguiente' => 'c6_06'],
                    'c6_06' => ['personaje' => 'mika', 'expresion' => 'avergonzada', 'texto' => 'Rin… yo…', 'opciones' => [
                        ['texto' => 'Tomarle la mano', 'destino' => 'c6_07a', 'efectos' => [['coraje', 'increment', 4], ['confianza', 'increment', 3], ['secretos', 'increment', 1]]],
                        ['texto' => 'Dar un paso atrás', 'destino' => 'c6_07b', 'efectos' => [['coraje', 'decrement', 2], ['confianza', 'increment', 1]]],
                    ]],
                    'c6_07a' => ['texto' => 'Sus manos se encuentran. Mika espera un rayo, una campana, algo. Solo suena la campanilla del gato.', 'siguiente' => 'c6_08'],
                    'c6_07b' => ['personaje' => 'rin', 'expresion' => 'triste', 'texto' => 'Está bien. De verdad. La luna también espera.', 'siguiente' => 'c6_08'],
                    'c6_08' => ['texto' => 'El gato maulló una vez y el cielo se partió en dos, como una página que alguien por fin se atrevía a pasar.', 'siguiente' => 'c6_09'],
                    'c6_09' => ['personaje' => 'yuna', 'expresion' => 'feliz', 'texto' => '¡Ya encontré a las dos! El hechizo de la luna y la tinta necesita exactamente esa combinación.', 'siguiente' => 'c7_01'],
                ],
            ],
            [
                'nodos' => [
                    'c7_01' => ['texto' => 'El festival de las linternas mágicas amanece con estrellas de papel, té caliente y carteles hechos a mano. El de Rin brilla en la entrada.', 'siguiente' => 'c7_02'],
                    'c7_02' => ['personaje' => 'sora', 'expresion' => 'feliz', 'texto' => '¡Mika, tu runa del gato ya vale entrada! ¡Cobra! ¡Cobra!', 'siguiente' => 'c7_03'],
                    'c7_03' => ['personaje' => 'nao', 'expresion' => 'neutro', 'texto' => 'Los hechizos expuestos también son una forma de hablar, Mika. No todos necesitan varita.', 'siguiente' => 'c7_04'],
                    'c7_04' => ['personaje' => 'mika', 'expresion' => 'determinada', 'texto' => 'Hay una página que no puse en la pared. Creo que hoy va conmigo.', 'opciones' => [
                        ['texto' => 'Mostrar la página a Rin', 'destino' => 'c7_05a', 'efectos' => [['secretos', 'increment', 3], ['coraje', 'increment', 2], ['confianza', 'increment', 2]]],
                        ['texto' => 'Guardarla para la noche', 'destino' => 'c7_05b', 'efectos' => [['secretos', 'increment', 1], ['coraje', 'increment', 1]]],
                    ]],
                    'c7_05a' => ['texto' => 'Rin mira el dibujo: dos magas, un lazo lunar, una luna partida. Se le rompe la voz antes que la sonrisa.', 'siguiente' => 'c7_06'],
                    'c7_05b' => ['personaje' => 'rin', 'expresion' => 'enamorada', 'texto' => 'Entonces habrá que esperar a la noche. Mejor así: las linternas favorecen a quienes se atreven.', 'siguiente' => 'c7_06'],
                    'c7_06' => ['personaje' => 'yuna', 'expresion' => 'determinada', 'texto' => 'Mika. Solo quería decirte que Rin no ha tocado su varita desde el portal. La dejó a una chica que dibuja lunas.', 'siguiente' => 'c7_07'],
                    'c7_07' => ['texto' => 'Yuna se va antes de recibir respuesta. Mika aprieta la carta dentro del grimorio.', 'siguiente' => 'c7_08'],
                    'c7_08' => ['texto' => 'El sol cae. Las linternas se encienden una a una, como frases que por fin encuentran su lugar.', 'siguiente' => 'c7_09'],
                    'c7_09' => ['personaje' => 'kira', 'expresion' => 'neutro', 'texto' => 'El eclipse llega en minutos. Recordad: todo hechizo pide un recuerdo a cambio.', 'siguiente' => 'c8_01'],
                ],
            ],
            [
                'nodos' => [
                    'c8_01' => ['texto' => 'La noche del eclipse. El patio está en silencio; la música del festival llega apagada, como desde otro barrio.', 'siguiente' => 'c8_02'],
                    'c8_02' => ['personaje' => 'rin', 'expresion' => 'neutro', 'texto' => 'No lancé el hechizo. Creí que si sonaba, tú no podrías decir lo que viniste a decir.', 'siguiente' => 'c8_03'],
                    'c8_03' => ['personaje' => 'mika', 'expresion' => 'avergonzada', 'texto' => '¿Y si lo que vine a decir no sale bien?', 'siguiente' => 'c8_04'],
                    'c8_04' => ['personaje' => 'rin', 'expresion' => 'enamorada', 'texto' => 'Entonces lo dibujas. Yo sé leer tus lunas mejor que tus silencios.', 'siguiente' => 'c8_05'],
                    'c8_05' => ['texto' => 'Mika saca la carta. El papel está arrugado de tanto apretarlo por dentro. Lo lee en voz baja, sin saltarse ni una palabra.', 'siguiente' => 'c8_06'],
                    'c8_06' => ['personaje' => 'mika', 'expresion' => 'enamorada', 'texto' => 'No sé si ser valiente es decirlo o quedarse. Pero sé que quiero que lo sepas tú antes que nadie.', 'opciones' => [
                        ['texto' => 'Besar a Rin bajo el eclipse', 'destino' => 'c8_07a', 'efectos' => [['coraje', 'set', 10], ['confianza', 'increment', 5], ['secretos', 'increment', 3], ['magia', 'increment', 2]]],
                        ['texto' => 'Pedir un poco más de tiempo', 'destino' => 'c8_07b', 'efectos' => [['coraje', 'increment', 2], ['confianza', 'increment', 4]]],
                    ]],
                    'c8_07a' => ['texto' => 'El beso sabe a tinta y a luna. Las linternas siguen quietas, pero el barrio entero parece haber respirado a la vez.', 'siguiente' => 'c8_08'],
                    'c8_07b' => ['personaje' => 'rin', 'expresion' => 'feliz', 'texto' => 'Está bien. El tiempo es lo único que nunca se me ha dado mal esperar.', 'siguiente' => 'c8_08'],
                    'c8_08' => ['personaje' => 'rin', 'expresion' => 'feliz', 'texto' => 'Gracias por no reírte del grimorio. Ni de la chica que lo escribía.', 'siguiente' => 'c8_09'],
                    'c8_09' => ['personaje' => 'mika', 'expresion' => 'feliz', 'texto' => 'El precio del hechizo era un recuerdo. Pagué el de tener miedo. Me parece un buen trato.', 'siguiente' => 'c9_01'],
                ],
            ],
            [
                'nodos' => [
                    'c9_01' => ['texto' => 'Diciembre. La librería Kagurazaka vuelve a oler a papel y lluvia, pero Mika ya no esconde los libros en el estante más alto.', 'siguiente' => 'c9_02'],
                    'c9_02' => ['personaje' => 'sora', 'expresion' => 'feliz', 'texto' => '¡El grimorio se imprimió! ¡Y hay una maga de luna en la última página! ¡No es sutil, pero es vuestro!', 'siguiente' => 'c9_03'],
                    'c9_03' => ['personaje' => 'rin', 'expresion' => 'enamorada', 'texto' => 'Dibujaste la luna, la tinta y el gato. ¿También dibujaste el final?', 'siguiente' => 'c9_04'],
                    'c9_04' => ['personaje' => 'mika', 'expresion' => 'determinada', 'texto' => 'El final es lo único que no supe dibujar. Porque todavía estamos en él.', 'opciones' => [
                        ['texto' => 'Dedicarlo «A Rin, que esperó el hechizo»', 'destino' => 'c9_05a', 'efectos' => [['recuerdos', 'increment', 3], ['confianza', 'increment', 5]]],
                        ['texto' => 'Dejar la dedicatoria en blanco', 'destino' => 'c9_05b', 'efectos' => [['recuerdos', 'increment', 2], ['secretos', 'increment', 2]]],
                    ]],
                    'c9_05a' => ['texto' => 'Rin lee la dedicatoria dos veces. La tercera la lee en voz alta, para que la librería entera se entere.', 'siguiente' => 'c9_06'],
                    'c9_05b' => ['texto' => 'La página está en blanco. Mika sonríe: hay cosas que no necesitan nombre para ser ciertas.', 'siguiente' => 'c9_06'],
                    'c9_06' => ['personaje' => 'mika', 'expresion' => 'feliz', 'texto' => 'La estrella cae. Esta vez no miro el cielo: miro a quien espera en él.', 'siguiente' => 'c9_07'],
                    'c9_07' => ['texto' => 'Y entre el vapor del té y la tinta fresca, Mika dibuja una última línea: dos magas, un gato y un invierno que ya no duele.', 'siguiente' => null],
                ],
            ],
        ];
    }

    /**
     * Genera portada, fondo y retratos SVG en public/storage.
     */
    private function crearRecursosGraficos(): void
    {
        File::ensureDirectoryExists(public_path('storage/covers'));
        File::ensureDirectoryExists(public_path('storage/backgrounds'));
        File::ensureDirectoryExists(public_path('storage/expressions'));

        File::put(public_path('storage/covers/hechizos-de-luna.svg'), $this->svgPortada());
        File::put(public_path('storage/backgrounds/tejado-lunar.svg'), $this->svgFondo());

        $colores = [
            'mika' => '#8fb3ff', 'rin' => '#ff8ad1', 'sora' => '#9dffd6',
            'yuna' => '#ffd27f', 'kira' => '#c59cff', 'nao' => '#ff9c8a',
        ];
        $nombres = [
            'mika' => 'Mika', 'rin' => 'Rin', 'sora' => 'Sora',
            'yuna' => 'Yuna', 'kira' => 'Kira', 'nao' => 'Nao',
        ];

        foreach ($colores as $clave => $color) {
            foreach (['neutro', 'feliz', 'triste', 'sorprendida', 'enamorada', 'avergonzada', 'determinada'] as $expresion) {
                File::put(
                    public_path("storage/expressions/{$clave}-{$expresion}.svg"),
                    $this->svgRetrato($color, $nombres[$clave], $expresion)
                );
            }
        }
    }

    /**
     * Portada de la novela.
     */
    private function svgPortada(): string
    {
        return <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="1200" viewBox="0 0 800 1200">
  <defs>
    <linearGradient id="noche" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#10173d"/><stop offset="0.55" stop-color="#33235e"/><stop offset="1" stop-color="#9a5f92"/>
    </linearGradient>
    <linearGradient id="brillo" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#ffe8a8"/><stop offset="1" stop-color="#ff8ad1"/>
    </linearGradient>
  </defs>
  <rect width="800" height="1200" fill="url(#noche)"/>
  <g fill="#fff6fb" opacity="0.85">
    <circle cx="110" cy="160" r="3"/><circle cx="220" cy="260" r="2"/><circle cx="690" cy="180" r="3"/>
    <circle cx="610" cy="360" r="2"/><circle cx="140" cy="430" r="2"/><circle cx="730" cy="520" r="3"/>
  </g>
  <circle cx="570" cy="300" r="105" fill="url(#brillo)" opacity="0.9"/>
  <circle cx="615" cy="265" r="92" fill="#10173d"/>
  <path d="M0 800 Q200 720 400 790 T800 780 L800 1200 L0 1200Z" fill="#0b0c22" opacity="0.85"/>
  <g fill="#0b0c22">
    <circle cx="270" cy="780" r="68"/><path d="M202 858 Q270 950 338 858Z"/>
    <circle cx="500" cy="795" r="68"/><path d="M432 873 Q500 965 568 873Z"/>
  </g>
  <path d="M335 790 L335 690 M270 735 L335 690 L395 735" stroke="#ff8ad1" stroke-width="8" fill="none" stroke-linecap="round"/>
  <path d="M505 800 L520 700 M455 750 L520 700 L575 745" stroke="#8fb3ff" stroke-width="8" fill="none" stroke-linecap="round"/>
  <circle cx="270" cy="735" r="16" fill="#ffe8a8"/><circle cx="520" cy="700" r="16" fill="#ffe8a8"/>
  <text x="400" y="130" text-anchor="middle" fill="#fff6fb" font-family="Georgia, serif" font-size="56" font-weight="bold">HECHIZOS DE LUNA</text>
  <text x="400" y="190" text-anchor="middle" fill="#ffe8a8" font-family="Georgia, serif" font-size="30">el pacto de las magas</text>
  <text x="400" y="1120" text-anchor="middle" fill="#fff6fb" font-family="Georgia, serif" font-size="24" opacity="0.85"> una novela visual de tinta, luna y dos varitas </text>
</svg>
SVG;
    }

    /**
     * Fondo de la escena única.
     */
    private function svgFondo(): string
    {
        return <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="900" viewBox="0 0 1600 900">
  <defs>
    <linearGradient id="tejado" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#141a45"/><stop offset="0.6" stop-color="#4b2f6b"/><stop offset="1" stop-color="#d78ba3"/>
    </linearGradient>
  </defs>
  <rect width="1600" height="900" fill="url(#tejado)"/>
  <circle cx="1230" cy="230" r="105" fill="#ffe8a8" opacity="0.9"/>
  <circle cx="1280" cy="195" r="92" fill="#141a45"/>
  <g fill="#fff6fb" opacity="0.35">
    <circle cx="180" cy="130" r="3"/><circle cx="430" cy="90" r="2"/><circle cx="760" cy="160" r="3"/>
    <circle cx="1020" cy="110" r="2"/><circle cx="1400" cy="150" r="3"/><circle cx="1520" cy="320" r="2"/>
  </g>
  <g opacity="0.4" fill="#0b0c22">
    <rect x="60" y="430" width="130" height="290"/><rect x="230" y="350" width="95" height="370"/>
    <rect x="360" y="470" width="160" height="250"/><rect x="560" y="320" width="110" height="400"/>
    <rect x="710" y="450" width="170" height="270"/><rect x="920" y="390" width="100" height="330"/>
    <rect x="1060" y="490" width="140" height="230"/><rect x="1240" y="360" width="120" height="360"/>
    <rect x="1400" y="460" width="150" height="260"/>
  </g>
  <path d="M0 730 Q800 670 1600 730 L1600 900 L0 900Z" fill="#0b0c22" opacity="0.82"/>
  <g stroke="#ff8ad1" stroke-width="6" fill="none" opacity="0.7">
    <path d="M250 700 L250 620 M215 660 L250 620 L285 660"/>
    <path d="M1300 690 L1320 610 M1275 650 L1320 610 L1365 650"/>
  </g>
</svg>
SVG;
    }

    /**
     * Retrato simple de una maga con la expresión indicada.
     */
    private function svgRetrato(string $color, string $nombre, string $expresion): string
    {
        $ojos = match ($expresion) {
            'feliz' => '<path d="M180 250 Q210 220 240 250 M290 250 Q320 220 350 250" stroke="#3b2b4d" stroke-width="10" fill="none" stroke-linecap="round"/>',
            'triste' => '<path d="M180 240 Q210 265 240 240 M290 240 Q320 265 350 240" stroke="#3b2b4d" stroke-width="10" fill="none" stroke-linecap="round"/>',
            'sorprendida' => '<circle cx="210" cy="245" r="20" fill="#3b2b4d"/><circle cx="320" cy="245" r="20" fill="#3b2b4d"/>',
            'enamorada' => '<path d="M185 235 Q210 210 235 235 Q210 270 185 235Z M295 235 Q320 210 345 235 Q320 270 295 235Z" fill="#ff5fa2"/>',
            'avergonzada' => '<path d="M180 245 Q210 225 240 245 M290 245 Q320 225 350 245" stroke="#3b2b4d" stroke-width="9" fill="none"/><circle cx="175" cy="285" r="22" fill="#ff8fb0" opacity="0.5"/><circle cx="355" cy="285" r="22" fill="#ff8fb0" opacity="0.5"/>',
            'determinada' => '<path d="M178 240 L242 252 M292 252 L356 240" stroke="#3b2b4d" stroke-width="10" stroke-linecap="round"/>',
            default => '<circle cx="210" cy="245" r="12" fill="#3b2b4d"/><circle cx="320" cy="245" r="12" fill="#3b2b4d"/>',
        };

        $boca = match ($expresion) {
            'feliz' => '<path d="M225 330 Q265 370 305 330" stroke="#8c3a5a" stroke-width="8" fill="none" stroke-linecap="round"/>',
            'triste' => '<path d="M230 350 Q265 320 300 350" stroke="#8c3a5a" stroke-width="8" fill="none" stroke-linecap="round"/>',
            'sorprendida' => '<ellipse cx="265" cy="345" rx="18" ry="24" fill="#8c3a5a"/>',
            'enamorada' => '<path d="M235 335 Q265 365 295 335" stroke="#8c3a5a" stroke-width="8" fill="none" stroke-linecap="round"/>',
            'avergonzada' => '<path d="M245 342 Q265 330 285 342" stroke="#8c3a5a" stroke-width="8" fill="none" stroke-linecap="round"/>',
            'determinada' => '<path d="M235 342 L295 342" stroke="#8c3a5a" stroke-width="8" stroke-linecap="round"/>',
            default => '<path d="M245 342 Q265 355 285 342" stroke="#8c3a5a" stroke-width="8" fill="none" stroke-linecap="round"/>',
        };

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512">
  <defs><linearGradient id="f" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="$color"/><stop offset="1" stop-color="#10173d"/></linearGradient></defs>
  <rect width="512" height="512" rx="36" fill="url(#f)"/>
  <circle cx="265" cy="250" r="150" fill="#ffe0cd"/>
  <path d="M110 210 Q265 20 420 210 Q390 150 265 140 Q150 150 110 210Z" fill="$color"/>
  <path d="M110 200 Q80 300 130 380 L160 330 Q130 260 150 210Z" fill="$color"/>
  <path d="M420 200 Q450 300 400 380 L370 330 Q400 260 380 210Z" fill="$color"/>
  <path d="M300 120 L310 95 L320 120 L345 130 L320 140 L310 165 L300 140 L275 130Z" fill="#ffe8a8" opacity="0.9"/>
  $ojos
  $boca
  <text x="265" y="470" text-anchor="middle" fill="#fff6fb" font-family="Georgia, serif" font-size="34">$nombre · $expresion</text>
</svg>
SVG;
    }
}
