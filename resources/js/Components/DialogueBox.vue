<script setup>
import ChoiceButton from './ChoiceButton.vue';

defineProps({
    speaker: { type: String, default: '' },
    text: { type: String, default: '' },
    choices: { type: Array, default: () => [] },
    disabled: { type: Boolean, default: false },
    advancing: { type: Boolean, default: false },
    typing: { type: Boolean, default: false },
});

defineEmits(['advance', 'select', 'skip']);
</script>

<template>
    <div class="vn-game__dialogue">
        <div class="vn-dialogue">
            <p v-if="speaker" class="vn-dialogue__speaker">{{ speaker }}</p>

            <p
                class="vn-dialogue__text vn-narrative max-w-prose"
                role="status"
                aria-live="polite"
                @click="$emit('skip')"
            >
                {{ text }}
            </p>

            <div v-if="choices.length" class="vn-dialogue__choices">
                <ChoiceButton
                    v-for="choice in choices"
                    :key="choice.id"
                    :label="choice.text"
                    :disabled="disabled"
                    :advancing="advancing"
                    @select="$emit('select', choice.id)"
                />
            </div>

            <div v-else class="vn-dialogue__actions">
                <button
                    type="button"
                    class="vn-pill vn-pill--solid"
                    :disabled="disabled"
                    :aria-busy="advancing"
                    @click="$emit('advance')"
                >
                    <span v-if="advancing" class="vn-spinner" aria-hidden="true"></span>
                    {{ advancing ? 'Avanzando…' : 'Continuar →' }}
                </button>
            </div>

            <p v-if="typing && !disabled" class="vn-dialogue__hint">
                Haz clic en el texto para mostrarlo completo.
            </p>
        </div>
    </div>
</template>
