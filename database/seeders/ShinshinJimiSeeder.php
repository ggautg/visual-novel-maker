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
 * Crea la novela larga «Shinshin Jimi: el color de lo que no se dice»
 * en la cuenta de Gabriel. Es idempotente: si la novela ya existe, no la duplica.
 *
 * También genera los recursos gráficos (portada, fondo y retratos) como SVG,
 * para que la partida se vea completa sin depender de imágenes externas.
 */
class ShinshinJimiSeeder extends Seeder
{
    /**
     * Punto de entrada del seeder.
     */
    public function run(): void
    {
        $gabriel = User::where('email', 'admin@ionys.com.py')->firstOrFail();

        $this->crearRecursosGraficos();

        $titulo = 'Shinshin Jimi: el color de lo que no se dice';

        if (Novel::where('title', $titulo)->exists()) {
            $this->command?->info('La novela ya existe; no se duplicó.');

            return;
        }

        $novel = Novel::create([
            'user_id' => $gabriel->id,
            'title' => $titulo,
            'description' => <<<'TEXTO'
Shin Aoyagi dibuja trenes para no mirar a los demás. Jimi Kurosawa toca la guitarra para que alguien, por fin, la escuche. Cuando sus cuadernos se cruzan en una estación lluviosa de Tokio, empieza una historia de amistad, vergüenza y amor que ninguna de las dos supo escribir a tiempo.

Una novela visual yuri de ambiente anime: nueve capítulos, decisiones que cambian la confianza y el coraje de Shin, y una pregunta que flota en el aire como el vapor del té: ¿se atreverá a decir en voz alta lo que ya dibujó mil veces?
TEXTO,
            'cover_image' => 'covers/shinshin-jimi.svg',
            'status' => 'published',
        ]);

        $this->asignarEtiquetas($novel);
        $personajes = $this->crearPersonajes($novel);
        $variables = $this->crearVariables($novel);
        $escena = Scene::create([
            'novel_id' => $novel->id,
            'title' => 'Tokio, de la lluvia a las linternas',
            'background_image' => 'backgrounds/tokyo-atardecer.svg',
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
        $nombres = ['yuri', 'anime', 'romance', 'instituto', 'identidad', 'slice of life'];

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
            'shin' => ['Shin Aoyagi', '#b98cff'],
            'jimi' => ['Jimi Kurosawa', '#ff7fb0'],
            'nana' => ['Nana Shirai', '#7fe3ff'],
            'rina' => ['Rina Tachibana', '#9dffc4'],
            'yui' => ['Yui Mori', '#ffd479'],
            'aya' => ['Aya Fujimoto', '#ff9c7a'],
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
     * Variables que registran el tono de la relación.
     *
     * @return array<string, Variable>
     */
    private function crearVariables(Novel $novel): array
    {
        $datos = [
            'confianza' => ['Confianza compartida', 0],
            'coraje' => ['Coraje de Shin', 1],
            'secretos' => ['Secretos compartidos', 0],
            'celos' => ['Celos de Aya', 0],
            'borradores' => ['Borradores publicados', 0],
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
     * Guion completo: nueve capítulos, 70 nodos y decisiones que convergen.
     *
     * @return array<int, array{nodos: array<string, array<string, mixed>>}>
     */
    private function guion(): array
    {
        return [
            [
                'nodos' => [
                    'c1_01' => ['texto' => "Tokio, 06:40. La lluvia dibuja rayas diagonales en la ventana del tren. Shin aprieta su cuaderno contra el pecho como si escondiera un corazón de papel.", 'siguiente' => 'c1_02'],
                    'c1_02' => ['personaje' => 'shin', 'expresion' => 'neutro', 'texto' => 'Otro día, otro paraguas que olvidé. Al menos los trenes llegan siempre a tiempo, aunque yo no.', 'siguiente' => 'c1_03'],
                    'c1_03' => ['texto' => 'El tren se detiene. La puerta se abre y una chica con el pelo recogido entra corriendo, con una guitarra colgada al hombro y una sonrisa que parece no conocer el invierno.', 'siguiente' => 'c1_04'],
                    'c1_04' => ['personaje' => 'jimi', 'expresion' => 'feliz', 'texto' => '¡Ey! ¡Se te cayó esto! Espera, espera… ¡uau, dibujas trenes! Dibujas trenes como si fueran personas.', 'siguiente' => 'c1_05'],
                    'c1_05' => ['texto' => 'Jimi Kurosawa sostiene el cuaderno con las dos manos. En la portada, un vagón de la línea Yamanote parece respirar.', 'siguiente' => 'c1_06'],
                    'c1_06' => ['personaje' => 'shin', 'expresion' => 'sorprendida', 'texto' => 'Ah… es… es solo un boceto. No es nada serio. ¿Me lo puedes dar?', 'opciones' => [
                        ['texto' => 'Tomar el cuaderno y sonreír', 'destino' => 'c1_07a', 'efectos' => [['confianza', 'increment', 2], ['coraje', 'increment', 1]]],
                        ['texto' => 'Murmurar un «gracias» y salir corriendo', 'destino' => 'c1_07b', 'efectos' => [['coraje', 'decrement', 1]]],
                    ]],
                    'c1_07a' => ['personaje' => 'shin', 'expresion' => 'avergonzada', 'texto' => 'Gracias. Me llamo Shin. Dibujo… bueno, intento dibujar. Gracias por no reírte.', 'siguiente' => 'c1_08'],
                    'c1_07b' => ['texto' => 'Shin desaparece entre la multitud. Detrás, Jimi se queda mirando el cuaderno con una expresión que no es decepción, sino curiosidad.', 'siguiente' => 'c1_08'],
                    'c1_08' => ['texto' => 'Esa mañana empezó el capítulo que ninguna de las dos había escrito. Todavía no lo sabían.', 'siguiente' => 'c2_01'],
                ],
            ],
            [
                'nodos' => [
                    'c2_01' => ['personaje' => 'nana', 'expresion' => 'feliz', 'texto' => '¡Shin! ¡El club de arte necesita sangre nueva! ¡Y no, la tinta no cuenta como sangre!', 'siguiente' => 'c2_02'],
                    'c2_02' => ['personaje' => 'shin', 'expresion' => 'neutro', 'texto' => 'Nana, mi sangre es solo tinta y un poco de café frío. ¿Por qué insistes?', 'siguiente' => 'c2_03'],
                    'c2_03' => ['texto' => 'El aula 304 huele a barniz, a lápices viejos y a alguien afinando una guitarra con paciencia de santo.', 'siguiente' => 'c2_04'],
                    'c2_04' => ['personaje' => 'jimi', 'expresion' => 'feliz', 'texto' => '¿Otra vez estás dibujando trenes? Te advierto: esa estación tiene mejor iluminación que mi vida amorosa.', 'siguiente' => 'c2_05'],
                    'c2_05' => ['personaje' => 'shin', 'expresion' => 'neutro', 'texto' => 'Los trenes no te juzgan. Y siempre saben dónde van.', 'siguiente' => 'c2_06'],
                    'c2_06' => ['personaje' => 'jimi', 'expresion' => 'neutro', 'texto' => 'Entonces qué… ¿te sientas conmigo? Prometo no cantar. Prometo intentarlo.', 'opciones' => [
                        ['texto' => 'Sentarse al lado de Jimi', 'destino' => 'c2_07a', 'efectos' => [['confianza', 'increment', 3], ['secretos', 'increment', 1]]],
                        ['texto' => 'Esconderse detrás de Nana', 'destino' => 'c2_07b', 'efectos' => [['coraje', 'decrement', 1], ['confianza', 'increment', 1]]],
                    ]],
                    'c2_07a' => ['texto' => 'Shin se sienta. Sus rodillas casi se tocan. Jimi afina una cuerda y el sonido le recorre la espalda como un escalofrío amable.', 'siguiente' => 'c2_08'],
                    'c2_07b' => ['personaje' => 'nana', 'expresion' => 'sorprendida', 'texto' => 'Oye, soy un escudo muy bonito, pero mi turno de ser tu excusa terminó hace tres minutos.', 'siguiente' => 'c2_08'],
                    'c2_08' => ['personaje' => 'yui', 'expresion' => 'feliz', 'texto' => 'Bienvenida al club, Shin. Aquí nadie se ríe de los bocetos… todavía.', 'siguiente' => 'c3_01'],
                ],
            ],
            [
                'nodos' => [
                    'c3_01' => ['texto' => 'Las cuerdas de Jimi y el lápiz de Shin encuentran el mismo ritmo. Una acorde, una línea. Otro acorde, otra línea.', 'siguiente' => 'c3_02'],
                    'c3_02' => ['personaje' => 'jimi', 'expresion' => 'neutro', 'texto' => 'Cuando dibujas esa estación, ¿por qué siempre sale alguien esperando en el andén?', 'siguiente' => 'c3_03'],
                    'c3_03' => ['personaje' => 'shin', 'expresion' => 'avergonzada', 'texto' => 'Porque… siempre hay alguien esperando. Aunque sea la última en enterarse.', 'siguiente' => 'c3_04'],
                    'c3_04' => ['personaje' => 'jimi', 'expresion' => 'determinada', 'texto' => 'Enséñame lo que dibujas cuando nadie mira. En serio. Lo que escondes en la última página.', 'opciones' => [
                        ['texto' => 'Mostrar el manga secreto', 'destino' => 'c3_05a', 'efectos' => [['secretos', 'increment', 3], ['confianza', 'increment', 2]]],
                        ['texto' => 'Cerrar el cuaderno', 'destino' => 'c3_05b', 'efectos' => [['secretos', 'decrement', 1], ['confianza', 'increment', 1]]],
                    ]],
                    'c3_05a' => ['texto' => 'La última página muestra a dos chicas bajo una lluvia idéntica. Una sostiene un paraguas; la otra, un cuaderno. Jimi se queda sin aire.', 'siguiente' => 'c3_06'],
                    'c3_05b' => ['personaje' => 'shin', 'expresion' => 'triste', 'texto' => 'Todavía no. Hay páginas que ni yo sé mirar.', 'siguiente' => 'c3_06'],
                    'c3_06' => ['personaje' => 'jimi', 'expresion' => 'enamorada', 'texto' => 'Está bien. Puedo esperar. Toco guitarra desde los doce años: sé esperar el acorde correcto.', 'siguiente' => 'c3_07'],
                    'c3_07' => ['texto' => 'Shin dibuja, sin darse cuenta, una nueva última página. Esta vez la guitarra también está en el andén.', 'siguiente' => 'c3_08'],
                    'c3_08' => ['texto' => 'Esa tarde, el club de arte dejó de ser un lugar para dibujar y empezó a ser un lugar donde quedarse.', 'siguiente' => 'c4_01'],
                ],
            ],
            [
                'nodos' => [
                    'c4_01' => ['personaje' => 'nana', 'expresion' => 'sorprendida', 'texto' => '¡No! ¡El concierto del sábado se canceló! ¡Mi camiseta de merchandising no tiene sentido ahora!', 'siguiente' => 'c4_02'],
                    'c4_02' => ['personaje' => 'jimi', 'expresion' => 'triste', 'texto' => 'El local se inundó. La batería, el escenario, todo. Supongo que tocaremos en la estación, para nadie.', 'siguiente' => 'c4_03'],
                    'c4_03' => ['texto' => 'Jimi dice «para nadie» con la voz de quien espera que alguien la contradiga.', 'siguiente' => 'c4_04'],
                    'c4_04' => ['personaje' => 'shin', 'expresion' => 'determinada', 'texto' => 'Puedo hacer el cartel. Y… puedo estar en primera fila. Si quieres.', 'opciones' => [
                        ['texto' => 'Hacer el cartel y decirlo en voz alta', 'destino' => 'c4_05a', 'efectos' => [['coraje', 'increment', 3], ['confianza', 'increment', 2], ['borradores', 'increment', 1]]],
                        ['texto' => 'Dibujarlo en silencio y dejarlo en su casillero', 'destino' => 'c4_05b', 'efectos' => [['coraje', 'decrement', 1], ['confianza', 'increment', 1], ['borradores', 'increment', 1]]],
                    ]],
                    'c4_05a' => ['personaje' => 'jimi', 'expresion' => 'sorprendida', 'texto' => '¿En serio? Shin, eso es lo más bonito que me han dicho sin usar una sola palabra bonita.', 'siguiente' => 'c4_06'],
                    'c4_05b' => ['texto' => 'El cartel aparece al día siguiente. No lleva firma, pero Jimi reconoce el trazo de la estación al instante.', 'siguiente' => 'c4_06'],
                    'c4_06' => ['texto' => 'El concierto improvisado reúne a treinta personas. Entre ellas, una chica que no recuerda haber decidido ir.', 'siguiente' => 'c4_07'],
                    'c4_07' => ['personaje' => 'jimi', 'expresion' => 'feliz', 'texto' => 'Esta va para quien dibuja andenes vacíos. Que sepan que ya no están vacíos.', 'siguiente' => 'c4_08'],
                    'c4_08' => ['texto' => 'Shin esconde la cara en el cuaderno. La tinta del borrador nuevo todavía está húmeda.', 'siguiente' => 'c5_01'],
                ],
            ],
            [
                'nodos' => [
                    'c5_01' => ['personaje' => 'aya', 'expresion' => 'neutro', 'texto' => 'Hola, Shin. Soy Aya, amiga de Jimi desde primaria. ¿Sabías que antes compartíamos auriculares?', 'siguiente' => 'c5_02'],
                    'c5_02' => ['texto' => 'Aya sonríe como quien conoce todos los atajos del pasillo y ninguno de los caminos del corazón.', 'siguiente' => 'c5_03'],
                    'c5_03' => ['personaje' => 'shin', 'expresion' => 'neutro', 'texto' => 'No lo sabía. Jimi no habla mucho de antes.', 'siguiente' => 'c5_04'],
                    'c5_04' => ['personaje' => 'aya', 'expresion' => 'triste', 'texto' => 'Antes tampoco hablaba mucho de nada. Hasta que alguien empezó a dibujarla.', 'opciones' => [
                        ['texto' => 'Contar la verdad sobre el manga', 'destino' => 'c5_05a', 'efectos' => [['secretos', 'increment', 2], ['confianza', 'increment', 2], ['celos', 'decrement', 1]]],
                        ['texto' => 'Proteger el secreto de Jimi', 'destino' => 'c5_05b', 'efectos' => [['secretos', 'increment', 1], ['celos', 'increment', 2]]],
                    ]],
                    'c5_05a' => ['personaje' => 'aya', 'expresion' => 'sorprendida', 'texto' => 'Ah. Entonces escribes sobre ella. Creí que solo dibujabas trenes, pero dibujas la forma en que alguien espera.', 'siguiente' => 'c5_06'],
                    'c5_05b' => ['texto' => 'Shin se encoge de hombros. Es la primera mentira que le sale fácil, y por eso mismo le duele.', 'siguiente' => 'c5_06'],
                    'c5_06' => ['texto' => 'Esa noche, Shin escribe una carta que no piensa enviar. Empieza con «Jimi» y termina con una página en blanco.', 'siguiente' => 'c5_07'],
                    'c5_07' => ['personaje' => 'shin', 'expresion' => 'enamorada', 'texto' => 'Si se la doy, dejará de ser un dibujo. Si no se la doy, seguirá siendo una costumbre.', 'siguiente' => 'c5_08'],
                    'c5_08' => ['texto' => 'La carta duerme dentro del cuaderno. Mañana habrá azotea, y las azoteas no perdonan las palabras guardadas.', 'siguiente' => 'c6_01'],
                ],
            ],
            [
                'nodos' => [
                    'c6_01' => ['texto' => 'La azotea del instituto huele a verano adelantado. El cielo tiene ese naranja que parece pedir disculpas por irse.', 'siguiente' => 'c6_02'],
                    'c6_02' => ['personaje' => 'jimi', 'expresion' => 'neutro', 'texto' => 'Rina dice que el festival necesita voluntarios. Yo dije que sí antes de preguntar qué implica. Es un don.', 'siguiente' => 'c6_03'],
                    'c6_03' => ['personaje' => 'shin', 'expresion' => 'neutro', 'texto' => 'El don de decir que sí y luego mirar al cielo buscando instrucciones.', 'siguiente' => 'c6_04'],
                    'c6_04' => ['personaje' => 'jimi', 'expresion' => 'enamorada', 'texto' => 'A veces miro al cielo y ya sé la respuesta. Solo que no sé si mi boca sabe decirla.', 'siguiente' => 'c6_05'],
                    'c6_05' => ['texto' => 'Silencio. El tipo de silencio que no se rompe: se deja caer como una hora más.', 'siguiente' => 'c6_06'],
                    'c6_06' => ['personaje' => 'shin', 'expresion' => 'avergonzada', 'texto' => 'Jimi… yo…', 'opciones' => [
                        ['texto' => 'Tomarle la mano', 'destino' => 'c6_07a', 'efectos' => [['coraje', 'increment', 4], ['confianza', 'increment', 3], ['secretos', 'increment', 1]]],
                        ['texto' => 'Dar un paso atrás', 'destino' => 'c6_07b', 'efectos' => [['coraje', 'decrement', 2], ['confianza', 'increment', 1]]],
                    ]],
                    'c6_07a' => ['texto' => 'Sus manos se encuentran. Shin espera un rayo, una campana, algo. Solo llega el sonido de Nana corriendo escaleras arriba.', 'siguiente' => 'c6_08'],
                    'c6_07b' => ['personaje' => 'jimi', 'expresion' => 'triste', 'texto' => 'Está bien. De verdad. El cielo también espera.', 'siguiente' => 'c6_08'],
                    'c6_08' => ['personaje' => 'rina', 'expresion' => 'feliz', 'texto' => '¡Ya encontré voluntarias! Las dos. El puesto de dibujo y té necesita exactamente esa combinación.', 'siguiente' => 'c7_01'],
                ],
            ],
            [
                'nodos' => [
                    'c7_01' => ['texto' => 'El festival amanece con banderines, vapor de té y carteles hechos a mano. El de Jimi brilla en la entrada.', 'siguiente' => 'c7_02'],
                    'c7_02' => ['personaje' => 'nana', 'expresion' => 'feliz', 'texto' => '¡Shin, tu dibujo del tren ya vale entrada! ¡Cobra! ¡Cobra!', 'siguiente' => 'c7_03'],
                    'c7_03' => ['personaje' => 'yui', 'expresion' => 'neutro', 'texto' => 'Los bocetos expuestos también son una forma de hablar, Shin. No todos necesitan micrófono.', 'siguiente' => 'c7_04'],
                    'c7_04' => ['personaje' => 'shin', 'expresion' => 'determinada', 'texto' => 'Hay una página que no puse en la pared. Creo que hoy va conmigo.', 'opciones' => [
                        ['texto' => 'Mostrar la página a Jimi', 'destino' => 'c7_05a', 'efectos' => [['secretos', 'increment', 3], ['coraje', 'increment', 2], ['confianza', 'increment', 2]]],
                        ['texto' => 'Guardarla para la noche', 'destino' => 'c7_05b', 'efectos' => [['secretos', 'increment', 1], ['coraje', 'increment', 1]]],
                    ]],
                    'c7_05a' => ['texto' => 'Jimi mira el dibujo: dos chicas, un andén, un paraguas compartido. Se le rompe la voz antes que la sonrisa.', 'siguiente' => 'c7_06'],
                    'c7_05b' => ['personaje' => 'jimi', 'expresion' => 'enamorada', 'texto' => 'Entonces habrá que esperar a la noche. Mejor así: las linternas favorecen a quienes se atreven.', 'siguiente' => 'c7_06'],
                    'c7_06' => ['personaje' => 'aya', 'expresion' => 'determinada', 'texto' => 'Shin. Solo quería decirte que Jimi no ha tocado su guitarra desde el concierto. Se la dejó a una chica que dibuja trenes.', 'siguiente' => 'c7_07'],
                    'c7_07' => ['texto' => 'Aya se va antes de recibir respuesta. Shin aprieta la carta dentro del cuaderno.', 'siguiente' => 'c7_08'],
                    'c7_08' => ['texto' => 'El sol cae. Las linternas se encienden una a una, como frases que por fin encuentran su lugar.', 'siguiente' => 'c8_01'],
                ],
            ],
            [
                'nodos' => [
                    'c8_01' => ['texto' => 'La noche de las linternas. El patio está en silencio; la música del festival llega apagada, como desde otro barrio.', 'siguiente' => 'c8_02'],
                    'c8_02' => ['personaje' => 'jimi', 'expresion' => 'neutro', 'texto' => 'No toqué hoy. Creí que si sonaba, tú no podrías decir lo que viniste a decir.', 'siguiente' => 'c8_03'],
                    'c8_03' => ['personaje' => 'shin', 'expresion' => 'avergonzada', 'texto' => '¿Y si lo que vine a decir no sale bien?', 'siguiente' => 'c8_04'],
                    'c8_04' => ['personaje' => 'jimi', 'expresion' => 'enamorada', 'texto' => 'Entonces lo dibujas. Yo sé leer tus dibujos mejor que tus silencios.', 'siguiente' => 'c8_05'],
                    'c8_05' => ['texto' => 'Shin saca la carta. El papel está arrugado de tanto apretarlo por dentro. Lo lee en voz baja, sin saltarse ni una palabra.', 'siguiente' => 'c8_06'],
                    'c8_06' => ['personaje' => 'shin', 'expresion' => 'enamorada', 'texto' => 'No sé si ser valiente es decirlo o quedarse. Pero sé que quiero que lo sepas tú antes que nadie.', 'opciones' => [
                        ['texto' => 'Besar a Jimi bajo las linternas', 'destino' => 'c8_07a', 'efectos' => [['coraje', 'set', 10], ['confianza', 'increment', 5], ['secretos', 'increment', 3]]],
                        ['texto' => 'Pedir un poco más de tiempo', 'destino' => 'c8_07b', 'efectos' => [['coraje', 'increment', 2], ['confianza', 'increment', 4]]],
                    ]],
                    'c8_07a' => ['texto' => 'El beso sabe a té frío y a tinta. Las linternas siguen quietas, pero el barrio entero parece haber respirado a la vez.', 'siguiente' => 'c8_08'],
                    'c8_07b' => ['personaje' => 'jimi', 'expresion' => 'feliz', 'texto' => 'Está bien. El tiempo es lo único que nunca se me ha dado mal esperar.', 'siguiente' => 'c8_08'],
                    'c8_08' => ['personaje' => 'shin', 'expresion' => 'feliz', 'texto' => 'Gracias por no reírte del cuaderno. Ni de la chica que lo escribía.', 'siguiente' => 'c9_01'],
                ],
            ],
            [
                'nodos' => [
                    'c9_01' => ['texto' => 'Diciembre. La estación de la línea Yamanote vuelve a estar llena de paraguas, pero Shin ya no olvida el suyo.', 'siguiente' => 'c9_02'],
                    'c9_02' => ['personaje' => 'nana', 'expresion' => 'feliz', 'texto' => '¡El manga se imprimió! ¡Y hay una chica con guitarra en la última página! ¡No es sutil, pero es vuestro!', 'siguiente' => 'c9_03'],
                    'c9_03' => ['personaje' => 'jimi', 'expresion' => 'enamorada', 'texto' => 'Dibujaste el andén, el paraguas y la guitarra. ¿También dibujaste el final?', 'siguiente' => 'c9_04'],
                    'c9_04' => ['personaje' => 'shin', 'expresion' => 'determinada', 'texto' => 'El final es lo único que no supe dibujar. Porque todavía estamos en él.', 'opciones' => [
                        ['texto' => 'Dedicarlo «A Jimi, que esperó el acorde»', 'destino' => 'c9_05a', 'efectos' => [['borradores', 'increment', 3], ['confianza', 'increment', 5]]],
                        ['texto' => 'Dejar la dedicatoria en blanco', 'destino' => 'c9_05b', 'efectos' => [['borradores', 'increment', 2], ['secretos', 'increment', 2]]],
                    ]],
                    'c9_05a' => ['texto' => 'Jimi lee la dedicatoria dos veces. La tercera la lee en voz alta, para que la estación entera se entere.', 'siguiente' => 'c9_06'],
                    'c9_05b' => ['texto' => 'La página está en blanco. Shin sonríe: hay cosas que no necesitan nombre para ser ciertas.', 'siguiente' => 'c9_06'],
                    'c9_06' => ['personaje' => 'shin', 'expresion' => 'feliz', 'texto' => 'El tren llega. Esta vez no miro el andén: miro a quien espera en él.', 'siguiente' => 'c9_07'],
                    'c9_07' => ['texto' => 'Y en el vagón, entre el vapor y la tinta fresca, Shin dibuja una última línea: dos manos, un paraguas, un invierno que ya no duele.', 'siguiente' => null],
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

        File::put(public_path('storage/covers/shinshin-jimi.svg'), $this->svgPortada());
        File::put(public_path('storage/backgrounds/tokyo-atardecer.svg'), $this->svgFondo());

        $colores = [
            'shin' => '#b98cff', 'jimi' => '#ff7fb0', 'nana' => '#7fe3ff',
            'rina' => '#9dffc4', 'yui' => '#ffd479', 'aya' => '#ff9c7a',
        ];
        $nombres = [
            'shin' => 'Shin', 'jimi' => 'Jimi', 'nana' => 'Nana',
            'rina' => 'Rina', 'yui' => 'Yui', 'aya' => 'Aya',
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
    <linearGradient id="cielo" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#2a1b54"/><stop offset="0.55" stop-color="#6b3a86"/><stop offset="1" stop-color="#ff9bb8"/>
    </linearGradient>
  </defs>
  <rect width="800" height="1200" fill="url(#cielo)"/>
  <circle cx="610" cy="250" r="120" fill="#ffe9b0" opacity="0.85"/>
  <path d="M0 760 Q200 690 400 760 T800 750 L800 1200 L0 1200Z" fill="#1a1030" opacity="0.75"/>
  <g fill="#120b26">
    <circle cx="285" cy="760" r="70"/><path d="M215 840 Q285 930 355 840Z"/>
    <circle cx="500" cy="780" r="70"/><path d="M430 860 Q500 950 570 860Z"/>
  </g>
  <path d="M330 830 Q400 790 470 830" stroke="#ff9bb8" stroke-width="10" fill="none" stroke-linecap="round"/>
  <text x="400" y="140" text-anchor="middle" fill="#fff6fb" font-family="Georgia, serif" font-size="62" font-weight="bold">SHINSHIN JIMI</text>
  <text x="400" y="200" text-anchor="middle" fill="#ffd479" font-family="Georgia, serif" font-size="30">el color de lo que no se dice</text>
  <text x="400" y="1120" text-anchor="middle" fill="#fff6fb" font-family="Georgia, serif" font-size="24" opacity="0.85"> una novela visual de lluvia, tinta y guitarra </text>
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
    <linearGradient id="atardecer" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#3b225f"/><stop offset="0.6" stop-color="#b65f8f"/><stop offset="1" stop-color="#ffd3a6"/>
    </linearGradient>
  </defs>
  <rect width="1600" height="900" fill="url(#atardecer)"/>
  <circle cx="1230" cy="250" r="110" fill="#ffe9b0" opacity="0.9"/>
  <g opacity="0.35" fill="#120b26">
    <rect x="80" y="420" width="120" height="300"/><rect x="240" y="340" width="90" height="380"/>
    <rect x="370" y="470" width="150" height="250"/><rect x="560" y="300" width="110" height="420"/>
    <rect x="700" y="440" width="170" height="280"/><rect x="910" y="380" width="100" height="340"/>
    <rect x="1050" y="480" width="140" height="240"/><rect x="1230" y="350" width="120" height="370"/>
    <rect x="1390" y="450" width="150" height="270"/>
  </g>
  <path d="M0 720 Q800 660 1600 720 L1600 900 L0 900Z" fill="#160d2b" opacity="0.8"/>
  <g fill="#fff6fb" opacity="0.25">
    <circle cx="200" cy="150" r="3"/><circle cx="520" cy="110" r="2"/><circle cx="900" cy="180" r="3"/><circle cx="1450" cy="130" r="2"/>
  </g>
</svg>
SVG;
    }

    /**
     * Retrato simple de un personaje con la expresión indicada.
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
  <defs><linearGradient id="f" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="$color"/><stop offset="1" stop-color="#120b26"/></linearGradient></defs>
  <rect width="512" height="512" rx="36" fill="url(#f)"/>
  <circle cx="265" cy="250" r="150" fill="#ffe0cd"/>
  <path d="M110 210 Q265 20 420 210 Q390 150 265 140 Q150 150 110 210Z" fill="$color"/>
  <path d="M110 200 Q80 300 130 380 L160 330 Q130 260 150 210Z" fill="$color"/>
  <path d="M420 200 Q450 300 400 380 L370 330 Q400 260 380 210Z" fill="$color"/>
  $ojos
  $boca
  <text x="265" y="470" text-anchor="middle" fill="#fff6fb" font-family="Georgia, serif" font-size="34">$nombre · $expresion</text>
</svg>
SVG;
    }
}
