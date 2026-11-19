<script setup>
import { cloneVNode, Comment, computed, useSlots } from 'vue';
import InputError from './InputError.vue';
import InputLabel from './InputLabel.vue';

const props = defineProps({
    label: { type: String, required: true },
    error: { type: String, default: '' },
    id: { type: String, required: true },
});

const slots = useSlots();

const describedBy = computed(() => {
    const ids = [];
    if (slots.hint) ids.push(`${props.id}-hint`);
    if (props.error) ids.push(`${props.id}-error`);
    return ids.join(' ') || undefined;
});

// Añade accesibilidad y la clase de error al control que se pase por el slot.
const decorateControls = () => {
    let alreadyDecorated = false;

    return (slots.default?.() ?? []).map((node) => {
        if (alreadyDecorated || node.type === Comment || typeof node.type === 'symbol') {
            return node;
        }

        alreadyDecorated = true;

        return cloneVNode(node, {
            id: props.id,
            'aria-invalid': props.error ? 'true' : undefined,
            'aria-describedby': describedBy.value,
            class: props.error ? 'vn-input--error' : undefined,
        });
    });
};

const Control = () => decorateControls();
</script>

<template>
    <div class="vn-form-field">
        <InputLabel :for="id">{{ label }}</InputLabel>

        <component :is="Control" />

        <div v-if="$slots.hint" :id="`${id}-hint`" class="vn-hint">
            <slot name="hint" />
        </div>

        <div :id="`${id}-error`">
            <InputError :message="error" />
        </div>
    </div>
</template>
