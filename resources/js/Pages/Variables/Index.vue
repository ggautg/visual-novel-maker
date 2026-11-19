<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FormField from '@/Components/FormField.vue';
import SaveButton from '@/Components/SaveButton.vue';
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

// Edición en línea: solo una variable se edita a la vez.
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

const eliminandoVariableId = ref(null);

const eliminarVariable = (variableId) => {
    eliminandoVariableId.value = variableId;
    useForm({}).delete(route('variables.destroy', variableId), {
        onFinish: () => {
            eliminandoVariableId.value = null;
        },
    });
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
                    Las variables guardan valores o indicadores de tu historia (por ejemplo, el afecto de un
                    personaje o si el jugador tiene un objeto). Se usan para condicionar el avance y para que
                    las elecciones las modifiquen.
                </p>

                <!-- Formulario de creación -->
                <form @submit.prevent="submitCreate" class="vn-card space-y-4">
                    <FormField id="crear-variable-clave" label="Identificador" :error="createForm.errors.key">
                        <input id="crear-variable-clave" v-model="createForm.key" type="text" class="vn-input"
                            placeholder="afecto_maria" required />
                        <template #hint>Solo letras minúsculas, números y guion bajo; sin espacios.</template>
                    </FormField>

                    <FormField id="crear-variable-etiqueta" label="Etiqueta" :error="createForm.errors.label">
                        <input id="crear-variable-etiqueta" v-model="createForm.label" type="text" class="vn-input"
                            placeholder="Afecto de María" />
                        <template #hint>Nombre que se muestra en el editor.</template>
                    </FormField>

                    <FormField id="crear-variable-valor" label="Valor inicial" :error="createForm.errors.default_value">
                        <input id="crear-variable-valor" v-model.number="createForm.default_value" type="number"
                            class="vn-input w-32" />
                        <template #hint>Valor con el que empieza la variable en cada partida.</template>
                    </FormField>

                    <SaveButton
                        type="button"
                        label="Crear variable"
                        processing-label="Guardando…"
                        success-label="Guardado"
                        :processing="createForm.processing"
                        :success="createForm.recentlySuccessful"
                        @click="submitCreate"
                    />
                </form>

                <!-- Listado -->
                <div class="space-y-3">
                    <div v-for="variable in variables" :key="variable.id" class="vn-card">
                        <div v-if="editingId === variable.id" class="space-y-3">
                            <FormField id="editar-variable-clave" label="Identificador" :error="editForm.errors.key">
                                <input id="editar-variable-clave" v-model="editForm.key" type="text" class="vn-input" />
                                <template #hint>Solo letras minúsculas, números y guion bajo; sin espacios.</template>
                            </FormField>
                            <FormField id="editar-variable-etiqueta" label="Etiqueta" :error="editForm.errors.label">
                                <input id="editar-variable-etiqueta" v-model="editForm.label" type="text" class="vn-input" />
                            </FormField>
                            <FormField id="editar-variable-valor" label="Valor inicial"
                                :error="editForm.errors.default_value">
                                <input id="editar-variable-valor" v-model.number="editForm.default_value" type="number"
                                    class="vn-input w-32" />
                            </FormField>
                            <div class="flex gap-3">
                                <SaveButton
                                    type="button"
                                    label="Guardar"
                                    processing-label="Guardando…"
                                    success-label="Guardado"
                                    :processing="editForm.processing"
                                    :success="editForm.recentlySuccessful"
                                    @click="submitEdit(variable.id)"
                                />
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
                                <button type="button" @click="startEditing(variable)" class="vn-link">Editar</button>
                                <ConfirmDeleteButton
                                    title="Eliminar variable"
                                    message="¿Eliminar esta variable? Puede afectar a elecciones que ya la usan."
                                    label="Eliminar"
                                    :processing="eliminandoVariableId === variable.id"
                                    @confirm="eliminarVariable(variable.id)"
                                >
                                    <span class="vn-link vn-link--danger" role="button" tabindex="0">Eliminar</span>
                                </ConfirmDeleteButton>
                            </div>
                        </div>
                    </div>

                    <EmptyState
                        v-if="variables.length === 0"
                        title="Todavía no hay variables"
                        description="Crea la primera con el formulario de arriba."
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
