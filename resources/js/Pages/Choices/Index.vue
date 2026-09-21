<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    node: Object,
    choices: Array,
    allNodes: Array,
    variables: Array,
});

const createForm = useForm({
    text: '',
    next_node_id: '',
});

const submitCreate = () => {
    createForm.post(route('dialogue-nodes.choices.store', props.node.id), {
        onSuccess: () => createForm.reset(),
    });
};

const editingId = ref(null);
const editForm = useForm({
    text: '',
    next_node_id: '',
});

const startEditing = (choice) => {
    editingId.value = choice.id;
    editForm.text = choice.text;
    editForm.next_node_id = choice.next_node_id ?? '';
};

const submitEdit = (choiceId) => {
    editForm.put(route('choices.update', choiceId), {
        onSuccess: () => (editingId.value = null),
    });
};

const destroyChoice = (choiceId) => {
    if (confirm('¿Borrar esta elección?')) {
        useForm({}).delete(route('choices.destroy', choiceId));
    }
};

const nodePreview = (id) => {
    const n = props.allNodes.find((n) => n.id === id);
    return n ? `#${n.id}: ${n.text.substring(0, 40)}` : null;
};

// --- Formularios de "efecto" independientes por choice ---
// effectForms guarda un useForm() distinto para cada choice.id.
// Se crean acá, en un watch que corre ANTES del render (flush por defecto
// es 'pre'), en vez de crearlos "sobre la marcha" dentro del template.
// Crear un useForm() durante el render es lo que causaba el error anterior:
// el template podía intentar leer un formulario que todavía no existía.
const effectForms = reactive({});

watch(
    () => props.choices.map((c) => c.id),
    (ids) => {
        ids.forEach((id) => {
            if (!effectForms[id]) {
                effectForms[id] = useForm({
                    variable_id: '',
                    operation: 'increment',
                    value: 1,
                });
            }
        });
    },
    { immediate: true }
);

const submitEffect = (choiceId) => {
    effectForms[choiceId].post(route('choices.variable-effects.store', choiceId), {
        onSuccess: () => {
            effectForms[choiceId].value = 1;
        },
    });
};

const destroyEffect = (effectId) => {
    useForm({}).delete(route('variable-effects.destroy', effectId));
};

const variableName = (id) =>
    props.variables.find((v) => v.id === id)?.label ?? props.variables.find((v) => v.id === id)?.key ?? '?';

const operationLabel = (op) => ({ set: 'Fijar en', increment: 'Sumar', decrement: 'Restar' }[op]);
</script>

<template>
    <Head title="Elecciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="vn-page-title">Elecciones del nodo #{{ node.id }}</h2>
                <Link :href="route('scenes.dialogue-nodes.index', node.scene_id)" class="vn-link text-sm">
                    Volver a diálogos
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="vn-node-preview">
                    <strong>Texto del nodo:</strong> {{ node.text }}
                </div>

                <!-- Formulario de creación -->
                <form @submit.prevent="submitCreate" class="vn-card space-y-4">
                    <div>
                        <label class="vn-label">Texto de la elección</label>
                        <input v-model="createForm.text" type="text" class="vn-input" required />
                        <InputError :message="createForm.errors.text" class="mt-1" />
                    </div>

                    <div>
                        <label class="vn-label">A qué nodo lleva</label>
                        <select v-model="createForm.next_node_id" class="vn-input">
                            <option value="">— Ninguno / fin de rama —</option>
                            <option v-for="n in allNodes" :key="n.id" :value="n.id">
                                #{{ n.id }}: {{ n.text.substring(0, 40) }}
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="vn-pill vn-pill--solid" :disabled="createForm.processing">
                        Agregar elección
                    </button>
                </form>

                <!-- Listado -->
                <div class="space-y-3">
                    <div v-for="choice in choices" :key="choice.id" class="vn-card">
                        <div v-if="editingId === choice.id" class="space-y-3">
                            <input v-model="editForm.text" type="text" class="vn-input" />
                            <select v-model="editForm.next_node_id" class="vn-input">
                                <option value="">— Ninguno / fin de rama —</option>
                                <option v-for="n in allNodes" :key="n.id" :value="n.id">
                                    #{{ n.id }}: {{ n.text.substring(0, 40) }}
                                </option>
                            </select>
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    class="vn-pill vn-pill--solid"
                                    :disabled="editForm.processing"
                                    @click="submitEdit(choice.id)"
                                >
                                    Guardar
                                </button>
                                <button type="button" @click="editingId = null" class="vn-link text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        <!-- Solo renderizamos el bloque de efectos cuando su formulario ya existe -->
                        <div v-else-if="effectForms[choice.id]">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="vn-choice-text">{{ choice.text }}</p>
                                    <p class="mt-1 text-xs text-[#6E5FA3]">
                                        {{ nodePreview(choice.next_node_id) ?? 'Fin de rama' }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 gap-3 text-sm">
                                    <button @click="startEditing(choice)" class="vn-link">Editar</button>
                                    <button @click="destroyChoice(choice.id)" class="vn-link vn-link--danger">
                                        Borrar
                                    </button>
                                </div>
                            </div>

                            <!-- Efectos sobre variables -->
                            <div class="vn-effects">
                                <div
                                    v-for="effect in choice.variable_effects"
                                    :key="effect.id"
                                    class="mb-2 flex items-center gap-2 text-sm"
                                >
                                    <span class="text-[#D9CFFB]">
                                        {{ operationLabel(effect.operation) }} {{ effect.value }} a
                                        {{ variableName(effect.variable_id) }}
                                    </span>
                                    <button @click="destroyEffect(effect.id)" class="vn-link vn-link--danger text-xs">
                                        quitar
                                    </button>
                                </div>

                                <form @submit.prevent="submitEffect(choice.id)" class="flex flex-wrap items-end gap-2">
                                    <select v-model="effectForms[choice.id].variable_id" class="vn-input vn-input--sm">
                                        <option value="" disabled>Variable...</option>
                                        <option v-for="v in variables" :key="v.id" :value="v.id">
                                            {{ v.label || v.key }}
                                        </option>
                                    </select>
                                    <select v-model="effectForms[choice.id].operation" class="vn-input vn-input--sm">
                                        <option value="increment">Sumar</option>
                                        <option value="decrement">Restar</option>
                                        <option value="set">Fijar</option>
                                    </select>
                                    <input
                                        v-model.number="effectForms[choice.id].value"
                                        type="number"
                                        class="vn-input vn-input--sm w-20"
                                    />
                                    <button
                                        type="submit"
                                        class="vn-pill vn-pill--ghost text-xs"
                                        :disabled="effectForms[choice.id].processing"
                                    >
                                        Agregar efecto
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <p v-if="choices.length === 0" class="text-[#B8ACDD]">
                        Este nodo todavía no tiene elecciones. Si agregás una, el juego mostrará
                        estas opciones en vez de avanzar automáticamente.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.vn-page-title {
    font-family: 'Fredoka', sans-serif;
    font-weight: 600;
    font-size: 1.35rem;
    color: #fff6fb;
}

.vn-node-preview {
    border-radius: 0.75rem;
    background: rgba(94, 241, 230, 0.08);
    border: 1px solid rgba(94, 241, 230, 0.2);
    padding: 0.85rem 1rem;
    font-size: 0.9rem;
    color: #d9cffb;
}

.vn-card {
    border-radius: 1.25rem;
    border: 1px solid rgba(201, 184, 255, 0.15);
    background: rgba(255, 255, 255, 0.03);
    padding: 1.25rem;
}

.vn-label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.8rem;
    color: #d9cffb;
}

.vn-input {
    width: 100%;
    border-radius: 0.6rem;
    border: 1px solid rgba(201, 184, 255, 0.25);
    background: rgba(255, 255, 255, 0.05);
    padding: 0.55rem 0.8rem;
    font-size: 0.9rem;
    color: #fff6fb;
    outline: none;
    transition: border-color 0.2s ease;
}
.vn-input:focus {
    border-color: #ff5fa2;
}
.vn-input option {
    background: #1a1140;
    color: #fff6fb;
}
.vn-input--sm {
    width: auto;
    padding: 0.4rem 0.6rem;
    font-size: 0.8rem;
}

.vn-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.55rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.vn-pill--solid {
    background: linear-gradient(135deg, #ff5fa2, #ff8fc0);
    color: #1a0f36;
    box-shadow: 0 6px 18px rgba(255, 95, 162, 0.3);
}
.vn-pill--solid:hover:not(:disabled) {
    transform: translateY(-2px);
}
.vn-pill--ghost {
    border: 1px solid rgba(201, 184, 255, 0.3);
    background: transparent;
    color: #d9cffb;
    padding: 0.4rem 1rem;
}
.vn-pill--ghost:hover:not(:disabled) {
    border-color: #5ef1e6;
    color: #5ef1e6;
}

.vn-link {
    color: #5ef1e6;
    font-size: 0.85rem;
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.2s ease;
}
.vn-link:hover {
    color: #ffffff;
}
.vn-link--danger {
    color: #ff6b8a;
}
.vn-link--danger:hover {
    color: #ff9db3;
}

.vn-choice-text {
    font-family: 'Fredoka', sans-serif;
    font-weight: 500;
    color: #fff6fb;
}

.vn-effects {
    margin-top: 0.85rem;
    border-top: 1px solid rgba(201, 184, 255, 0.1);
    padding-top: 0.85rem;
}
</style>