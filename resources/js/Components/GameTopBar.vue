<script setup>
import ConfirmDeleteButton from './ConfirmDeleteButton.vue';
import GameAudioToggle from './GameAudioToggle.vue';
import LikeButton from './LikeButton.vue';
import ThemePicker from './ThemePicker.vue';

defineProps({
    likesCount: { type: Number, default: 0 },
    liked: { type: Boolean, default: false },
    audioEnabled: { type: Boolean, default: false },
    audioAvailable: { type: Boolean, default: false },
    restarting: { type: Boolean, default: false },
});

defineEmits(['exit', 'restart', 'toggle-like', 'toggle-audio']);
</script>

<template>
    <header class="vn-game__topbar">
        <div class="vn-game__tools">
            <button type="button" class="vn-pill vn-pill--ghost vn-pill--sm" @click="$emit('exit')">
                Salir
            </button>

            <ConfirmDeleteButton
                title="¿Reiniciar la partida?"
                message="Se perderá todo tu progreso y la historia empezará desde el principio."
                label="Reiniciar"
                :processing="restarting"
                @confirm="$emit('restart')"
            >
                <template #trigger>
                    <span
                        class="vn-pill vn-pill--ghost vn-pill--sm"
                        :class="{ 'opacity-50 pointer-events-none': restarting }"
                    >
                        ↺ Reiniciar
                    </span>
                </template>
            </ConfirmDeleteButton>

            <LikeButton
                :count="likesCount"
                :liked="liked"
                @toggle="$emit('toggle-like')"
            />
        </div>

        <div class="vn-game__tools">
            <GameAudioToggle
                :enabled="audioEnabled"
                :available="audioAvailable"
                @toggle="$emit('toggle-audio')"
            />
            <ThemePicker class="vn-game__theme" />
        </div>
    </header>
</template>
