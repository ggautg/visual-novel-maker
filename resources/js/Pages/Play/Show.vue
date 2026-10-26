<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch, onUnmounted } from 'vue';
import ThemePicker from '../../Components/ThemePicker.vue';

const props = defineProps({
    playthrough: Object,
    novel: Object,
    scene: Object,
    node: Object,
    isFinished: Boolean,
});

const characterName = computed(() => props.node?.character?.name ?? null);
const characterColor = computed(() => props.node?.character?.color ?? '#ffffff');
const expressionImage = computed(() => props.node?.expression?.image_url ?? null);

const backgroundStyle = computed(() => {
    if (!props.scene?.background_image) return {};
    return { backgroundImage: `url(/storage/${props.scene.background_image})` };
});

const hasChoices = computed(() => props.node?.choices?.length > 0);

// --- Lógica del efecto typewriter ---
const displayedText = ref('');
const isTyping = ref(false);
let typingInterval = null;

const TYPING_SPEED_MS = 25; // milisegundos entre cada letra

const startTyping = (fullText) => {
    clearInterval(typingInterval);
    displayedText.value = '';
    isTyping.value = true;

    let index = 0;
    typingInterval = setInterval(() => {
        displayedText.value += fullText[index];
        index++;

        if (index >= fullText.length) {
            clearInterval(typingInterval);
            isTyping.value = false;
        }
    }, TYPING_SPEED_MS);
};

const skipTyping = () => {
    if (isTyping.value && props.node) {
        clearInterval(typingInterval);
        displayedText.value = props.node.text;
        isTyping.value = false;
    }
};

// Cada vez que llega un nodo nuevo (avanzamos de diálogo), reinicia el efecto
watch(
    () => props.node?.id,
    () => {
        if (props.node?.text) {
            startTyping(props.node.text);
        }
    },
    { immediate: true }
);

onUnmounted(() => clearInterval(typingInterval));

// --- Avance del juego ---
const advance = (choiceId = null) => {
    // Si todavía se está escribiendo, el primer clic completa el texto en vez de avanzar
    if (isTyping.value) {
        skipTyping();
        return;
    }

    router.post(
        route('play.advance', props.playthrough.id),
        { choice_id: choiceId },
        { preserveScroll: true }
    );
};

const confirmRestart = () => {
    if (confirm('¿Reiniciar la partida desde el principio? Se perderá todo tu progreso.')) {
        router.post(route('play.restart', props.novel.id), {}, { preserveScroll: true });
    }
};
</script>

<template>

    <Head :title="`Jugando: ${novel.title}`" />

    <div class="relative h-screen w-full bg-black bg-cover bg-center text-white" :style="backgroundStyle">
        <ThemePicker class="absolute left-4 top-4 z-30" />
        <!-- Música de fondo -->
        <audio v-if="scene?.background_music" :src="`/storage/${scene.background_music}`" autoplay loop />

        <!-- Botón volver -->
        <Link :href="route('novels.index')"
            class="absolute right-4 top-4 rounded bg-black/50 px-3 py-1 text-sm hover:bg-black/70">
            Salir
        </Link>

        <!-- Pantalla de fin -->
        <div v-if="isFinished" class="flex h-full flex-col items-center justify-center gap-4 bg-black/70">
            <h2 class="text-2xl font-bold">Fin de la historia</h2>
            <Link :href="route('novels.index')" class="rounded bg-indigo-600 px-4 py-2 hover:bg-indigo-700">
                Volver a mis novelas
            </Link>

            <!-- Botón volver + reiniciar -->
            <div class="absolute right-4 top-4 flex gap-2">
                <button @click="confirmRestart" class="rounded bg-black/50 px-3 py-1 text-sm hover:bg-black/70">
                    ↺ Reiniciar
                </button>
                <Link :href="route('novels.index')" class="rounded bg-black/50 px-3 py-1 text-sm hover:bg-black/70">
                    Salir
                </Link>
                <button @click="router.post(route('novels.like', novel.id), {}, { preserveScroll: true })"
                    class="rounded bg-black/50 px-3 py-1 text-sm hover:bg-black/70">
                    {{ novel.is_liked_by_me ? '💗' : '🤍' }} {{ novel.likes_count }}
                </button>
            </div>
        </div>

        <!-- Cuadro de diálogo -->
        <div v-else class="absolute inset-x-0 bottom-0 bg-black/80 p-6">
            <!-- Sprite del personaje (si tiene expresión con imagen) -->
            <img v-if="expressionImage" :src="expressionImage" class="absolute bottom-full left-8 h-64 object-contain"
                :alt="characterName" />

            <div class="mx-auto max-w-3xl">
                <p v-if="characterName" class="mb-2 font-bold" :style="{ color: characterColor }">
                    {{ characterName }}
                </p>

                <p class="mb-4 min-h-[3rem] cursor-pointer text-lg leading-relaxed" @click="skipTyping">
                    {{ displayedText }}
                </p>

                <!-- Elecciones -->
                <div v-if="hasChoices" class="space-y-2">
                    <button v-for="choice in node.choices" :key="choice.id" @click="advance(choice.id)"
                        class="block w-full rounded border border-white/30 bg-white/10 px-4 py-2 text-left hover:bg-white/20">
                        {{ choice.text }}
                    </button>
                </div>

                <!-- Avance lineal (sin elecciones) -->
                <button v-else @click="advance()" class="rounded bg-indigo-600 px-4 py-2 hover:bg-indigo-700">
                    Continuar →
                </button>
            </div>
        </div>
    </div>
</template>