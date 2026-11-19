<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import DialogueBox from '../../Components/DialogueBox.vue';
import GameFrame from '../../Components/GameFrame.vue';
import GameTopBar from '../../Components/GameTopBar.vue';

const props = defineProps({
    playthrough: Object,
    novel: Object,
    scene: Object,
    node: Object,
    isFinished: Boolean,
});

const assetUrl = (path) => {
    if (!path) return '';
    return path.startsWith('http') || path.startsWith('/') ? path : `/storage/${path}`;
};

const characterName = computed(() => props.node?.character?.name ?? '');
const expressionImage = computed(() => assetUrl(props.node?.expression?.image_url));
const backgroundImage = computed(() => assetUrl(props.scene?.background_image));
const choices = computed(() => props.node?.choices ?? []);
const hasAudio = computed(() => Boolean(props.scene?.background_music));
const reduceMotion =
    typeof window !== 'undefined' &&
    window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

// --- Efecto de escritura ---
const displayedText = ref('');
const isTyping = ref(false);
const TYPING_SPEED_MS = 25;
let typingInterval = null;

const stopTyping = () => {
    clearInterval(typingInterval);
    typingInterval = null;
    isTyping.value = false;
};

const startTyping = (fullText) => {
    stopTyping();
    displayedText.value = '';

    if (!fullText || reduceMotion) {
        displayedText.value = fullText ?? '';
        return;
    }

    isTyping.value = true;
    let index = 0;

    typingInterval = setInterval(() => {
        displayedText.value += fullText[index];
        index += 1;

        if (index >= fullText.length) stopTyping();
    }, TYPING_SPEED_MS);
};

const skipTyping = () => {
    if (!isTyping.value || !props.node) return;
    stopTyping();
    displayedText.value = props.node.text ?? '';
};

watch(
    () => [props.node?.id, props.node?.text],
    () => startTyping(props.node?.text ?? ''),
    { immediate: true },
);

// --- Audio de la escena ---
const audioRef = ref(null);
const audioEnabled = ref(true);

const toggleAudio = () => {
    audioEnabled.value = !audioEnabled.value;
};

watch(audioEnabled, async (enabled) => {
    const audio = audioRef.value;
    if (!audio) return;

    try {
        if (enabled) await audio.play();
        else audio.pause();
    } catch {
        audioEnabled.value = false;
    }
});

onMounted(() => {
    audioRef.value?.play?.().catch(() => {
        audioEnabled.value = false;
    });
});

// --- Avance y acciones de la partida ---
const isAdvancing = ref(false);
const isRestarting = ref(false);

const advance = (choiceId = null) => {
    if (isAdvancing.value) return;

    if (isTyping.value) {
        skipTyping();
        return;
    }

    isAdvancing.value = true;

    router.post(
        route('play.advance', props.playthrough.id),
        { choice_id: choiceId },
        {
            preserveScroll: true,
            onFinish: () => { isAdvancing.value = false; },
        },
    );
};

const restart = () => {
    if (isRestarting.value) return;
    isRestarting.value = true;

    router.post(
        route('play.restart', props.novel.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => { isRestarting.value = false; },
        },
    );
};

const toggleLike = () => {
    router.post(
        route('novels.like', props.novel.id),
        {},
        {
            preserveScroll: true,
            onError: () => router.reload({ only: ['novel'] }),
        },
    );
};

const exit = () => router.visit(route('novels.index'));

onUnmounted(() => {
    stopTyping();
    audioRef.value?.pause?.();
});
</script>

<template>
    <Head :title="`Jugando: ${novel.title}`" />

    <GameFrame :background="backgroundImage">
        <audio
            v-if="hasAudio"
            ref="audioRef"
            :src="assetUrl(scene.background_music)"
            autoplay
            loop
        ></audio>

        <GameTopBar
            :likes-count="novel.likes_count ?? 0"
            :liked="Boolean(novel.is_liked_by_me)"
            :audio-enabled="audioEnabled"
            :audio-available="hasAudio"
            :restarting="isRestarting"
            @exit="exit"
            @restart="restart"
            @toggle-like="toggleLike"
            @toggle-audio="toggleAudio"
        />

        <img
            v-if="expressionImage"
            :src="expressionImage"
            class="vn-game__sprite"
            :alt="characterName ? `Expresión de ${characterName}` : 'Ilustración de la escena'"
        />

        <div v-if="isFinished" class="vn-game__end">
            <h2 class="vn-game__end-title">Fin de la historia</h2>
            <p class="vn-game__end-text">
                Has llegado al final de «{{ novel.title }}». ¿Te gustaría volver a empezar?
            </p>
            <button type="button" class="vn-pill vn-pill--solid" @click="exit">
                Volver a mis novelas
            </button>
        </div>

        <DialogueBox
            v-else
            :speaker="characterName"
            :text="displayedText"
            :choices="choices"
            :disabled="isAdvancing"
            :advancing="isAdvancing"
            :typing="isTyping"
            @advance="advance()"
            @select="advance($event)"
            @skip="skipTyping"
        />
    </GameFrame>
</template>
