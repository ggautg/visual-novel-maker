<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    novel: Object,
    variables: Array,
});

const createForm = useForm({
    key: '',
    label: '',
    default_value: 0,
});

const submitCreate = () => {
    createForm.post(route('novels.variables.store', props.novel.id), {
        onSuccess: () => createForm.reset(),
    });
};

const editingId = ref(null);
const editForm = useForm({
    key: '',
    label: '',
    default_value: 0,
});

const startEditing = (variable) => {
    editingId.value = variable.id;
    editForm.key = variable.key;
    editForm.label = variable.label ?? '';
    editForm.default_value = variable.default_value;
};

const submitEdit = (variableId) => {
    editForm.put(route('variables.update', variableId), {
        onSuccess: () => (editingId.value = null),
    });
};

const destroyVariable = (variableId) => {
    if (confirm('¿Borrar esta variable? Esto puede afectar elecciones que ya la usan.')) {
        useForm({}).delete(route('variables.destroy', variableId));
    }
};
</script>

<template>
    <Head :title="`Variables de ${novel.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="vn-page-title">Variables de: {{ novel.title }}</h2>
                <Link :href="route('novels.index')" class="vn-link text-sm">Volver a mis novelas</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6 lg:px-8">
                <p class="text-sm text-[#B8ACDD]">
                    Las variables representan stats o flags de tu historia (ej: afecto de un
                    personaje, si el jugador tiene un objeto). Se usan para condicionar el
                    avance y para que las elecciones las modifiquen.
                </p>

                <!-- Formulario de creación -->
                <form @submit.prevent="submitCreate" class="vn-card space-y-4">
                    <div>
                        <label class="vn-label">Key (identificador técnico, sin espacios)</label>
                        <input v-model="createForm.key" type="text" class="vn-input" placeholder="afecto_maria" required />
                        <InputError :message="createForm.errors.key" class="mt-1" />
                    </div>

                    <div>
                        <label class="vn-label">Etiqueta (nombre para mostrar en el editor)</label>
                        <input v-model="createForm.label" type="text" class="vn-input" placeholder="Afecto de María" />
                    </div>

                    <div>
                        <label class="vn-label">Valor inicial</label>
                        <input v-model.number="createForm.default_value" type="number" class="vn-input w-32" />
                        <InputError :message="createForm.errors.default_value" class="mt-1" />
                    </div>

                    <button type="submit" class="vn-pill vn-pill--solid" :disabled="createForm.processing">
                        Crear variable
                    </button>
                </form>

                <!-- Listado -->
                <div class="space-y-3">
                    <div v-for="variable in variables" :key="variable.id" class="vn-card">
                        <div v-if="editingId === variable.id" class="space-y-3">
                            <input v-model="editForm.key" type="text" class="vn-input" />
                            <InputError :message="editForm.errors.key" class="mt-1" />
                            <input v-model="editForm.label" type="text" class="vn-input" />
                            <input v-model.number="editForm.default_value" type="number" class="vn-input w-32" />
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    class="vn-pill vn-pill--solid"
                                    :disabled="editForm.processing"
                                    @click="submitEdit(variable.id)"
                                >
                                    Guardar
                                </button>
                                <button type="button" @click="editingId = null" class="vn-link text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        <div v-else class="flex items-center justify-between">
                            <div>
                                <span class="vn-variable-name">{{ variable.label || variable.key }}</span>
                                <span class="ml-2 text-xs text-[#6E5FA3]">
                                    ({{ variable.key }}, inicia en {{ variable.default_value }})
                                </span>
                            </div>
                            <div class="flex gap-3 text-sm">
                                <button @click="startEditing(variable)" class="vn-link">Editar</button>
                                <button @click="destroyVariable(variable.id)" class="vn-link vn-link--danger">
                                    Borrar
                                </button>
                            </div>
                        </div>
                    </div>

                    <p v-if="variables.length === 0" class="text-[#B8ACDD]">
                        Todavía no tenés variables. Creá la primera arriba.
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

.vn-variable-name {
    font-family: 'Fredoka', sans-serif;
    font-weight: 500;
    color: #fff6fb;
}
</style>