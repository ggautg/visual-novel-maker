<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const destroyNode = (nodeId) => {
    if (confirm('¿Borrar este nodo de diálogo?')) {
        useForm({}).delete(route('dialogue-nodes.destroy', nodeId));
    }
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
                        <div class="flex-1">
                            <label class="vn-label">Personaje (opcional, vacío = narrador)</label>
                            <select v-model="createForm.character_id" class="vn-input">
                                <option value="">— Narrador —</option>
                                <option v-for="c in characters" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="vn-label">Expresión</label>
                            <select
                                v-model="createForm.character_expression_id"
                                class="vn-input"
                                :disabled="!createForm.character_id"
                            >
                                <option value="">— Ninguna —</option>
                                <option v-for="e in createExpressions" :key="e.id" :value="e.id">{{ e.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="vn-label">Texto</label>
                        <textarea v-model="createForm.text" class="vn-input" rows="2" required />
                        <InputError :message="createForm.errors.text" class="mt-1" />
                    </div>

                    <div>
                        <label class="vn-label">Siguiente nodo (dejalo vacío si vas a usar elecciones)</label>
                        <select v-model="createForm.next_node_id" class="vn-input">
                            <option value="">— Ninguno / fin —</option>
                            <option v-for="n in nodes" :key="n.id" :value="n.id">
                                #{{ n.id }}: {{ n.text.substring(0, 40) }}
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="vn-pill vn-pill--solid" :disabled="createForm.processing">
                        Agregar nodo
                    </button>
                </form>

                <!-- Listado de nodos -->
                <div class="space-y-3">
                    <div v-for="node in nodes" :key="node.id" class="vn-card">
                        <div v-if="editingId === node.id" class="space-y-3">
                            <div class="flex gap-4">
                                <select v-model="editForm.character_id" class="vn-input flex-1">
                                    <option value="">— Narrador —</option>
                                    <option v-for="c in characters" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <select
                                    v-model="editForm.character_expression_id"
                                    class="vn-input flex-1"
                                    :disabled="!editForm.character_id"
                                >
                                    <option value="">— Ninguna —</option>
                                    <option v-for="e in editExpressions" :key="e.id" :value="e.id">{{ e.name }}</option>
                                </select>
                            </div>
                            <textarea v-model="editForm.text" class="vn-input" rows="2" />
                            <select v-model="editForm.next_node_id" class="vn-input">
                                <option value="">— Ninguno / fin —</option>
                                <option v-for="n in nodes" :key="n.id" :value="n.id" :disabled="n.id === node.id">
                                    #{{ n.id }}: {{ n.text.substring(0, 40) }}
                                </option>
                            </select>
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    class="vn-pill vn-pill--solid"
                                    :disabled="editForm.processing"
                                    @click="submitEdit(node.id)"
                                >
                                    Guardar
                                </button>
                                <button type="button" @click="editingId = null" class="vn-link text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        <div v-else class="flex items-start justify-between">
                            <div>
                                <span class="text-xs text-[#6E5FA3]">#{{ node.id }}</span>
                                <p class="vn-node-text">
                                    <span v-if="characterName(node.character_id)" class="vn-node-speaker">
                                        {{ characterName(node.character_id) }}:
                                    </span>
                                    {{ node.text }}
                                </p>
                                <p class="mt-1 text-xs text-[#8A7EC0]">
                                    <span v-if="node.choices.length > 0">
                                        {{ node.choices.length }} elección(es) →
                                        {{ node.choices.map((c) => c.text).join(' / ') }}
                                    </span>
                                    <span v-else-if="node.next_node_id">Sigue a → #{{ node.next_node_id }}</span>
                                    <span v-else>Fin de rama</span>
                                </p>
                            </div>
                            <div class="flex shrink-0 gap-3 text-sm">
                                <Link :href="route('dialogue-nodes.choices.index', node.id)" class="vn-link">
                                    Elecciones
                                </Link>
                                <button @click="startEditing(node)" class="vn-link">Editar</button>
                                <button @click="destroyNode(node.id)" class="vn-link vn-link--danger">Borrar</button>
                            </div>
                        </div>
                    </div>

                    <p v-if="nodes.length === 0" class="text-[#B8ACDD]">
                        Todavía no hay nodos de diálogo. Agregá el primero arriba.
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
.vn-input:disabled {
    opacity: 0.5;
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

.vn-node-text {
    font-size: 0.95rem;
    color: #fff6fb;
}
.vn-node-speaker {
    color: #ff8fc0;
    font-weight: 600;
}
</style>