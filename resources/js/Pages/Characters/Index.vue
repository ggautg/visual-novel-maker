<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FormField from '@/Components/FormField.vue';
import SaveButton from '@/Components/SaveButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    novel: Object,
    characters: Array,
});

const createForm = useForm({
    name: '',
    color: '#ff5fa2',
});

const submitCreate = () => {
    createForm.post(route('novels.characters.store', props.novel.id), {
        onSuccess: () => createForm.reset(),
    });
};

// Edición en línea: solo un personaje se edita a la vez.
const editingId = ref(null);
const editForm = useForm({
    name: '',
    color: '#ff5fa2',
});

const startEditing = (character) => {
    editingId.value = character.id;
    editForm.name = character.name;
    editForm.color = character.color ?? '#ff5fa2';
};

const submitEdit = (characterId) => {
    editForm.put(route('characters.update', characterId), {
        onSuccess: () => (editingId.value = null),
    });
};

const eliminandoPersonajeId = ref(null);

const eliminarPersonaje = (characterId) => {
    eliminandoPersonajeId.value = characterId;
    useForm({}).delete(route('characters.destroy', characterId), {
        onFinish: () => {
            eliminandoPersonajeId.value = null;
        },
    });
};

// Un useForm() por personaje: se crean antes del render para no compartir estado.
const expressionForms = reactive({});

watch(
    () => props.characters.map((character) => character.id),
    (ids) => {
        ids.forEach((id) => {
            if (!expressionForms[id]) {
                expressionForms[id] = useForm({
                    name: '',
                    image: null,
                });
            }
        });
    },
    { immediate: true }
);

const submitExpression = (characterId) => {
    expressionForms[characterId].post(route('characters.expressions.store', characterId), {
        forceFormData: true,
        onSuccess: () => expressionForms[characterId].reset(),
    });
};

const eliminandoExpresionId = ref(null);

const eliminarExpresion = (expressionId) => {
    eliminandoExpresionId.value = expressionId;
    useForm({}).delete(route('expressions.destroy', expressionId), {
        onFinish: () => {
            eliminandoExpresionId.value = null;
        },
    });
};
</script>

<template>
    <Head :title="`Personajes de ${novel.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="vn-page-title">Personajes de: {{ novel.title }}</h2>
                <Link :href="route('novels.index')" class="vn-link text-sm">Volver a mis novelas</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Formulario de creación -->
                <form @submit.prevent="submitCreate" class="vn-card flex items-end gap-4">
                    <FormField id="crear-personaje-nombre" label="Nombre del personaje" class="flex-1"
                        :error="createForm.errors.name">
                        <input id="crear-personaje-nombre" v-model="createForm.name" type="text" class="vn-input" required />
                    </FormField>
                    <FormField id="crear-personaje-color" label="Color" :error="createForm.errors.color">
                        <input id="crear-personaje-color" v-model="createForm.color" type="color" class="vn-color" />
                    </FormField>
                    <SaveButton
                        type="button"
                        label="Agregar"
                        processing-label="Guardando…"
                        success-label="Guardado"
                        :processing="createForm.processing"
                        :success="createForm.recentlySuccessful"
                        @click="submitCreate"
                    />
                </form>

                <!-- Listado de personajes -->
                <div class="space-y-4">
                    <div v-for="character in characters" :key="character.id" class="vn-card">
                        <!-- Modo edición -->
                        <form
                            v-if="editingId === character.id"
                            @submit.prevent="submitEdit(character.id)"
                            class="flex items-end gap-4"
                        >
                            <FormField :id="`editar-personaje-nombre-${character.id}`" label="Nombre"
                                :error="editForm.errors.name" class="flex-1">
                                <input :id="`editar-personaje-nombre-${character.id}`" v-model="editForm.name"
                                    type="text" class="vn-input" required />
                            </FormField>
                            <FormField :id="`editar-personaje-color-${character.id}`" label="Color"
                                :error="editForm.errors.color">
                                <input :id="`editar-personaje-color-${character.id}`" v-model="editForm.color"
                                    type="color" class="vn-color" />
                            </FormField>
                            <SaveButton
                                type="button"
                                label="Guardar"
                                processing-label="Guardando…"
                                success-label="Guardado"
                                :processing="editForm.processing"
                                :success="editForm.recentlySuccessful"
                                @click="submitEdit(character.id)"
                            />
                            <button type="button" @click="editingId = null" class="vn-link text-sm">
                                Cancelar
                            </button>
                        </form>

                        <!-- Modo visualización -->
                        <div v-else-if="expressionForms[character.id]">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="vn-dot" :style="{ backgroundColor: character.color ?? '#c9b8ff' }" />
                                    <span class="vn-character-name" :style="{ color: character.color ?? '#fff6fb' }">
                                        {{ character.name }}
                                    </span>
                                </div>
                                <div class="flex gap-3 text-sm">
                                    <button type="button" @click="startEditing(character)" class="vn-link">Editar</button>
                                    <ConfirmDeleteButton
                                        title="Eliminar personaje"
                                        message="¿Eliminar este personaje? También se eliminarán sus expresiones."
                                        label="Eliminar"
                                        :processing="eliminandoPersonajeId === character.id"
                                        @confirm="eliminarPersonaje(character.id)"
                                    >
                                        <span class="vn-link vn-link--danger" role="button" tabindex="0">Eliminar</span>
                                    </ConfirmDeleteButton>
                                </div>
                            </div>

                            <!-- Expresiones del personaje -->
                            <div class="vn-expressions">
                                <div class="mb-3 flex flex-wrap gap-3">
                                    <div v-for="expression in character.expressions" :key="expression.id" class="relative">
                                        <img :src="expression.image_url" :alt="expression.name" class="vn-expr-img" />
                                        <span class="mt-1 block text-center text-xs text-[#B8ACDD]">
                                            {{ expression.name }}
                                        </span>
                                        <ConfirmDeleteButton
                                            title="Eliminar expresión"
                                            message="¿Eliminar esta expresión del personaje?"
                                            label="Eliminar"
                                            :processing="eliminandoExpresionId === expression.id"
                                            @confirm="eliminarExpresion(expression.id)"
                                        >
                                            <span class="vn-expr-remove" role="button" tabindex="0"
                                                :aria-label="`Eliminar expresión ${expression.name}`">×</span>
                                        </ConfirmDeleteButton>
                                    </div>
                                </div>

                                <form @submit.prevent="submitExpression(character.id)" class="flex flex-wrap items-end gap-3">
                                    <FormField :id="`expresion-nombre-${character.id}`" label="Nombre"
                                        :error="expressionForms[character.id].errors.name">
                                        <input :id="`expresion-nombre-${character.id}`"
                                            v-model="expressionForms[character.id].name"
                                            type="text"
                                            class="vn-input w-32"
                                            placeholder="feliz, triste…"
                                        />
                                        <template #hint>Separa los nombres con comas.</template>
                                    </FormField>
                                    <FormField :id="`expresion-imagen-${character.id}`" label="Imagen"
                                        :error="expressionForms[character.id].errors.image">
                                        <input :id="`expresion-imagen-${character.id}`"
                                            type="file"
                                            accept="image/*"
                                            class="text-sm text-[#D9CFFB]"
                                            @change="(evento) => (expressionForms[character.id].image = evento.target.files[0])"
                                        />
                                    </FormField>
                                    <SaveButton
                                        type="button"
                                        label="Subir"
                                        processing-label="Guardando…"
                                        success-label="Guardado"
                                        :processing="expressionForms[character.id].processing"
                                        :success="expressionForms[character.id].recentlySuccessful"
                                        @click="submitExpression(character.id)"
                                    />
                                </form>
                            </div>
                        </div>
                    </div>

                    <EmptyState
                        v-if="characters.length === 0"
                        title="Todavía no hay personajes"
                        description="Agrega el primero con el formulario de arriba."
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
