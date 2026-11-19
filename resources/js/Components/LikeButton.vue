<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    count: { type: Number, required: true },
    liked: { type: Boolean, required: true },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle']);

// El cambio es inmediato y los props del servidor lo confirman o corrigen después.
const localLiked = ref(props.liked);
const localCount = ref(props.count);

watch(
    () => props.liked,
    (value) => { localLiked.value = value; },
);

watch(
    () => props.count,
    (value) => { localCount.value = value; },
);

const toggle = () => {
    if (props.disabled) return;

    localLiked.value = !localLiked.value;
    localCount.value += localLiked.value ? 1 : -1;
    emit('toggle');
};
</script>

<template>
    <button
        type="button"
        class="vn-like"
        :class="{ 'vn-like--active': localLiked }"
        :aria-pressed="localLiked"
        :aria-label="localLiked ? 'Quitar me gusta' : 'Me gusta'"
        :disabled="disabled"
        @click="toggle"
    >
        <span aria-hidden="true">{{ localLiked ? '💗' : '🤍' }}</span>
        <span>{{ localCount }}</span>
    </button>
</template>
