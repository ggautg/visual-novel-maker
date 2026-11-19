<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClampedText from '@/Components/ClampedText.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FormField from '@/Components/FormField.vue';
import SaveButton from '@/Components/SaveButton.vue';
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

// Edición en línea: solo una elección se edita a la vez.
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

const eliminandoEleccionId = ref(null);
const eliminandoEfectoId = ref(null);

const eliminarEleccion = (choiceId) => {
    eliminandoEleccionId.value = choiceId;
    useForm({}).delete(route('choices.destroy', choiceId), {
        onFinish: () => {
            eliminandoEleccionId.value = null;
        },
    });
};

const eliminarEfecto = (effectId) => {
    eliminandoEfectoId.value = effectId;
    useForm({}).delete(route('variable-effects.destroy', effectId), {
        onFinish: () => {
            eliminandoEfectoId.value = null;
        },
    });
};

const vistaPreviaNodo = (id) => {
    const nodo = props.allNodes.find((n) => n.id === id);
    return nodo ? `#${nodo.id}: ${nodo.text.substring(0, 40)}` : null;
};

// Un useForm() por elección, creado antes del render para no compartir estado.
const effectForms = reactive({});

watch(
    () => props.choices.map((choice) => choice.id),
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

const nombreVariable = (id) =>
    props.variables.find((variable) => variable.id === id)?.label ??
    props.variables.find((variable) => variable.id === id)?.key ??
    '?';

const descripcionEfecto = (effect) => {
    const nombre = nombreVariable(effect.variable_id);
    if (effect.operation === 'set') {
        return `Fijar ${nombre} en ${effect.value}`;
    }
    if (effect.operation === 'decrement') {
        return `Restar ${effect.value} a ${nombre}`;
    }
    return `Sumar ${effect.value} a ${nombre}`;
};
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
                    <strong>Texto del nodo:</strong>
                    <ClampedText :text="node.text" :lines="3" />
                </div>

                <!-- Formulario de creación -->
                <form @submit.prevent="submitCreate" class="vn-card space-y-4">
                    <FormField id="crear-eleccion-texto" label="Texto de la elección" :error="createForm.errors.text">
                        <input id="crear-eleccion-texto" v-model="createForm.text" type="text" class="vn-input" required />
                        <template #hint>Lo que verá el jugador al elegir esta opción.</template>
                    </FormField>

                    <FormField id="crear-eleccion-destino" label="A qué nodo lleva"
                        :error="createForm.errors.next_node_id">
                        <select id="crear-eleccion-destino" v-model="createForm.next_node_id" class="vn-input">
                            <option value="">— Ninguno / fin de rama —</option>
                            <option v-for="n in allNodes" :key="n.id" :value="n.id">
                                #{{ n.id }}: {{ n.text.substring(0, 40) }}
                            </option>
                        </select>
                        <template #hint>Deja esta opción vacía para terminar la rama.</template>
                    </FormField>

                    <SaveButton
                        type="button"
                        label="Agregar elección"
                        processing-label="Guardando…"
                        success-label="Guardado"
                        :processing="createForm.processing"
                        :success="createForm.recentlySuccessful"
                        @click="submitCreate"
                    />
                </form>

                <!-- Listado -->
                <div class="space-y-3">
                    <div v-for="choice in choices" :key="choice.id" class="vn-card">
                        <div v-if="editingId === choice.id" class="space-y-3">
                            <FormField id="editar-eleccion-texto" label="Texto de la elección"
                                :error="editForm.errors.text">
                                <input id="editar-eleccion-texto" v-model="editForm.text" type="text" class="vn-input" />
                            </FormField>
                            <FormField id="editar-eleccion-destino" label="A qué nodo lleva"
                                :error="editForm.errors.next_node_id">
                                <select id="editar-eleccion-destino" v-model="editForm.next_node_id" class="vn-input">
                                    <option value="">— Ninguno / fin de rama —</option>
                                    <option v-for="n in allNodes" :key="n.id" :value="n.id">
                                        #{{ n.id }}: {{ n.text.substring(0, 40) }}
                                    </option>
                                </select>
                            </FormField>
                            <div class="flex gap-3">
                                <SaveButton
                                    type="button"
                                    label="Guardar"
                                    processing-label="Guardando…"
                                    success-label="Guardado"
                                    :processing="editForm.processing"
                                    :success="editForm.recentlySuccessful"
                                    @click="submitEdit(choice.id)"
                                />
                                <button type="button" @click="editingId = null" class="vn-link text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        <!-- Solo se muestra cuando su formulario de efectos ya existe -->
                        <div v-else-if="effectForms[choice.id]">
                            <div class="flex items-start justify-between">
                                <div>
                                    <ClampedText :text="choice.text" :lines="2" class="vn-choice-text" />
                                    <p class="mt-1 text-xs text-[#6E5FA3]">
                                        {{ vistaPreviaNodo(choice.next_node_id) ?? 'Fin de rama' }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 gap-3 text-sm">
                                    <button type="button" @click="startEditing(choice)" class="vn-link">Editar</button>
                                    <ConfirmDeleteButton
                                        title="Eliminar elección"
                                        message="¿Eliminar esta elección?"
                                        label="Eliminar"
                                        :processing="eliminandoEleccionId === choice.id"
                                        @confirm="eliminarEleccion(choice.id)"
                                    >
                                        <span class="vn-link vn-link--danger" role="button" tabindex="0">Eliminar</span>
                                    </ConfirmDeleteButton>
                                </div>
                            </div>

                            <!-- Efectos sobre variables -->
                            <div class="vn-effects">
                                <div
                                    v-for="effect in choice.variable_effects"
                                    :key="effect.id"
                                    class="mb-2 flex items-center gap-2 text-sm"
                                >
                                    <span class="text-[#D9CFFB]">{{ descripcionEfecto(effect) }}</span>
                                    <ConfirmDeleteButton
                                        title="Eliminar efecto"
                                        message="¿Quitar este efecto de la elección?"
                                        label="Eliminar"
                                        :processing="eliminandoEfectoId === effect.id"
                                        @confirm="eliminarEfecto(effect.id)"
                                    >
                                        <span class="vn-link vn-link--danger text-xs" role="button" tabindex="0">Eliminar</span>
                                    </ConfirmDeleteButton>
                                </div>

                                <form @submit.prevent="submitEffect(choice.id)" class="flex flex-wrap items-end gap-2">
                                    <FormField :id="`efecto-variable-${choice.id}`" label="Variable"
                                        :error="effectForms[choice.id].errors.variable_id">
                                        <select :id="`efecto-variable-${choice.id}`"
                                            v-model="effectForms[choice.id].variable_id"
                                            class="vn-input vn-input--sm"
                                        >
                                            <option value="" disabled>Elige una variable…</option>
                                            <option v-for="variable in variables" :key="variable.id"
                                                :value="variable.id">
                                                {{ variable.label || variable.key }}
                                            </option>
                                        </select>
                                    </FormField>
                                    <FormField :id="`efecto-operacion-${choice.id}`" label="Operación"
                                        :error="effectForms[choice.id].errors.operation">
                                        <select :id="`efecto-operacion-${choice.id}`"
                                            v-model="effectForms[choice.id].operation"
                                            class="vn-input vn-input--sm"
                                        >
                                            <option value="increment">Sumar</option>
                                            <option value="decrement">Restar</option>
                                            <option value="set">Fijar</option>
                                        </select>
                                    </FormField>
                                    <FormField :id="`efecto-valor-${choice.id}`" label="Valor"
                                        :error="effectForms[choice.id].errors.value">
                                        <input :id="`efecto-valor-${choice.id}`"
                                            v-model.number="effectForms[choice.id].value"
                                            type="number"
                                            class="vn-input vn-input--sm w-20"
                                        />
                                    </FormField>
                                    <SaveButton
                                        type="button"
                                        label="Agregar efecto"
                                        processing-label="Guardando…"
                                        success-label="Guardado"
                                        :processing="effectForms[choice.id].processing"
                                        :success="effectForms[choice.id].recentlySuccessful"
                                        @click="submitEffect(choice.id)"
                                    />
                                </form>
                            </div>
                        </div>
                    </div>

                    <EmptyState
                        v-if="choices.length === 0"
                        title="Todavía no hay elecciones"
                        description="Agrega una para que el juego muestre opciones en vez de avanzar automáticamente."
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
