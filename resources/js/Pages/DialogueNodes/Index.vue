<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClampedText from '@/Components/ClampedText.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FormField from '@/Components/FormField.vue';
import SaveButton from '@/Components/SaveButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    scene: Object,
    nodes: Array,
    characters: Array,
});

const createForm = useForm({
    character_id: '',
    character_expression_id: '',
    text: '',
    next_node_id: '',
});

const createExpressions = computed(() => {
    const character = props.characters.find((c) => c.id === Number(createForm.character_id));
    return character ? character.expressions : [];
});

const submitCreate = () => {
    createForm.post(route('scenes.dialogue-nodes.store', props.scene.id), {
        onSuccess: () => createForm.reset(),
    });
};

// Edición en línea: solo un nodo se edita a la vez.
const editingId = ref(null);
const editForm = useForm({
    character_id: '',
    character_expression_id: '',
    text: '',
    next_node_id: '',
});

const editExpressions = computed(() => {
    const character = props.characters.find((c) => c.id === Number(editForm.character_id));
    return character ? character.expressions : [];
});

const startEditing = (node) => {
    editingId.value = node.id;
    editForm.character_id = node.character_id ?? '';
    editForm.character_expression_id = node.character_expression_id ?? '';
    editForm.text = node.text;
    editForm.next_node_id = node.next_node_id ?? '';
};

const submitEdit = (nodeId) => {
    editForm.put(route('dialogue-nodes.update', nodeId), {
        onSuccess: () => (editingId.value = null),
    });
};

const etiquetasElecciones = computed(() => {
    const etiquetas = {};
    props.nodes.forEach((node) => {
        const total = node.choices?.length ?? 0;
        etiquetas[node.id] = total === 1 ? 'elección' : 'elecciones';
    });
    return etiquetas;
});

const eliminandoNodoId = ref(null);

const eliminarNodo = (nodeId) => {
    eliminandoNodoId.value = nodeId;
    useForm({}).delete(route('dialogue-nodes.destroy', nodeId), {
        onFinish: () => {
            eliminandoNodoId.value = null;
        },
    });
};

const characterName = (id) => props.characters.find((c) => c.id === id)?.name ?? null;
</script>

<template>
    <Head :title="`Diálogos: ${scene.title ?? 'Escena #' + scene.order}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="vn-page-title">Diálogos de: {{ scene.title ?? `Escena #${scene.order}` }}</h2>
                <Link :href="route('novels.scenes.index', scene.novel_id)" class="vn-link text-sm">
                    Volver a escenas
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Formulario de creación -->
                <form @submit.prevent="submitCreate" class="vn-card space-y-4">
                    <div class="flex gap-4">
                        <FormField id="crear-nodo-personaje" label="Personaje (opcional, vacío = narrador)"
                            :error="createForm.errors.character_id" class="flex-1">
                            <select id="crear-nodo-personaje" v-model="createForm.character_id" class="vn-input">
                                <option value="">— Narrador —</option>
                                <option v-for="c in characters" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </FormField>
                        <FormField id="crear-nodo-expresion" label="Expresión"
                            :error="createForm.errors.character_expression_id" class="flex-1">
                            <select id="crear-nodo-expresion"
                                v-model="createForm.character_expression_id"
                                class="vn-input"
                                :disabled="!createForm.character_id"
                            >
                                <option value="">— Ninguna —</option>
                                <option v-for="e in createExpressions" :key="e.id" :value="e.id">{{ e.name }}</option>
                            </select>
                            <template #hint>Disponible solo si elegiste un personaje.</template>
                        </FormField>
                    </div>

                    <FormField id="crear-nodo-texto" label="Texto" :error="createForm.errors.text">
                        <textarea id="crear-nodo-texto" v-model="createForm.text" class="vn-input" rows="2" required />
                        <template #hint>Escribe lo que dirá o narrará este nodo.</template>
                    </FormField>

                    <FormField id="crear-nodo-siguiente" label="Siguiente nodo"
                        :error="createForm.errors.next_node_id">
                        <select id="crear-nodo-siguiente" v-model="createForm.next_node_id" class="vn-input">
                            <option value="">— Ninguno / fin —</option>
                            <option v-for="n in nodes" :key="n.id" :value="n.id">
                                #{{ n.id }}: {{ n.text.substring(0, 40) }}
                            </option>
                        </select>
                        <template #hint>Déjalo vacío si vas a usar elecciones.</template>
                    </FormField>

                    <SaveButton
                        type="button"
                        label="Agregar nodo"
                        processing-label="Guardando…"
                        success-label="Guardado"
                        :processing="createForm.processing"
                        :success="createForm.recentlySuccessful"
                        @click="submitCreate"
                    />
                </form>

                <!-- Listado de nodos -->
                <div class="space-y-3">
                    <div v-for="node in nodes" :key="node.id" class="vn-card">
                        <div v-if="editingId === node.id" class="space-y-3">
                            <div class="flex gap-4">
                                <FormField :id="`editar-nodo-personaje-${node.id}`" label="Personaje"
                                    :error="editForm.errors.character_id" class="flex-1">
                                    <select :id="`editar-nodo-personaje-${node.id}`" v-model="editForm.character_id"
                                        class="vn-input">
                                        <option value="">— Narrador —</option>
                                        <option v-for="c in characters" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </FormField>
                                <FormField :id="`editar-nodo-expresion-${node.id}`" label="Expresión"
                                    :error="editForm.errors.character_expression_id" class="flex-1">
                                    <select :id="`editar-nodo-expresion-${node.id}`"
                                        v-model="editForm.character_expression_id"
                                        class="vn-input"
                                        :disabled="!editForm.character_id"
                                    >
                                        <option value="">— Ninguna —</option>
                                        <option v-for="e in editExpressions" :key="e.id" :value="e.id">{{ e.name }}</option>
                                    </select>
                                </FormField>
                            </div>
                            <FormField :id="`editar-nodo-texto-${node.id}`" label="Texto" :error="editForm.errors.text">
                                <textarea :id="`editar-nodo-texto-${node.id}`" v-model="editForm.text"
                                    class="vn-input" rows="2" />
                            </FormField>
                            <FormField :id="`editar-nodo-siguiente-${node.id}`" label="Siguiente nodo"
                                :error="editForm.errors.next_node_id">
                                <select :id="`editar-nodo-siguiente-${node.id}`" v-model="editForm.next_node_id"
                                    class="vn-input">
                                    <option value="">— Ninguno / fin —</option>
                                    <option v-for="n in nodes" :key="n.id" :value="n.id" :disabled="n.id === node.id">
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
                                    @click="submitEdit(node.id)"
                                />
                                <button type="button" @click="editingId = null" class="vn-link text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        <div v-else class="flex items-start justify-between">
                            <div>
                                <span class="text-xs text-[#6E5FA3]">#{{ node.id }}</span>
                                <div class="vn-node-text">
                                    <span v-if="characterName(node.character_id)" class="vn-node-speaker">
                                        {{ characterName(node.character_id) }}:
                                    </span>
                                    <ClampedText :text="node.text" :lines="2" />
                                </div>
                                <div class="mt-1 text-xs text-[#8A7EC0]">
                                    <template v-if="node.choices.length > 0">
                                        <span>{{ node.choices.length }} {{ etiquetasElecciones[node.id] }} →</span>
                                        <ClampedText :text="node.choices.map((c) => c.text).join(' / ')" :lines="1" />
                                    </template>
                                    <span v-else-if="node.next_node_id">Sigue a → #{{ node.next_node_id }}</span>
                                    <span v-else>Fin de rama</span>
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-3 text-sm">
                                <Link :href="route('dialogue-nodes.choices.index', node.id)" class="vn-link">
                                    Elecciones
                                </Link>
                                <button type="button" @click="startEditing(node)" class="vn-link">Editar</button>
                                <ConfirmDeleteButton
                                    title="Eliminar nodo"
                                    message="¿Eliminar este nodo de diálogo?"
                                    label="Eliminar"
                                    :processing="eliminandoNodoId === node.id"
                                    @confirm="eliminarNodo(node.id)"
                                >
                                    <span class="vn-link vn-link--danger" role="button" tabindex="0">Eliminar</span>
                                </ConfirmDeleteButton>
                            </div>
                        </div>
                    </div>

                    <EmptyState
                        v-if="nodes.length === 0"
                        title="Todavía no hay nodos de diálogo"
                        description="Agrega el primero con el formulario de arriba."
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
