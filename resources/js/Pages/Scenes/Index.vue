<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FormField from '@/Components/FormField.vue';
import SaveButton from '@/Components/SaveButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    novel: Object,
    scenes: Array,
});

const createForm = useForm({
    title: '',
    background_image: null,
    background_music: null,
});

const submitCreate = () => {
    createForm.post(route('novels.scenes.store', props.novel.id), {
        forceFormData: true,
        onSuccess: () => createForm.reset(),
    });
};

// Edición en línea: solo una escena se edita a la vez.
const editingId = ref(null);
const editForm = useForm({
    title: '',
    background_image: null,
    background_music: null,
});

const startEditing = (scene) => {
    editingId.value = scene.id;
    editForm.title = scene.title ?? '';
    editForm.background_image = null;
    editForm.background_music = null;
};

const submitEdit = (sceneId) => {
    editForm
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(route('scenes.update', sceneId), {
            forceFormData: true,
            onSuccess: () => (editingId.value = null),
        });
};

const etiquetasNodos = computed(() => {
    const etiquetas = {};
    props.scenes.forEach((scene) => {
        const total = scene.dialogue_nodes_count ?? 0;
        etiquetas[scene.id] = total === 1 ? 'nodo' : 'nodos';
    });
    return etiquetas;
});

const eliminandoEscenaId = ref(null);

const eliminarEscena = (sceneId) => {
    eliminandoEscenaId.value = sceneId;
    useForm({}).delete(route('scenes.destroy', sceneId), {
        onFinish: () => {
            eliminandoEscenaId.value = null;
        },
    });
};
</script>

<template>
    <Head :title="`Escenas de ${novel.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="vn-page-title">Escenas de: {{ novel.title }}</h2>
                <Link :href="route('novels.index')" class="vn-link text-sm">Volver a mis novelas</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Formulario de creación -->
                <form @submit.prevent="submitCreate" class="vn-card space-y-4">
                    <FormField id="crear-escena-titulo" label="Título (opcional, solo para referencia)"
                        :error="createForm.errors.title">
                        <input id="crear-escena-titulo" v-model="createForm.title" type="text" class="vn-input"
                            placeholder="Ej.: Capítulo 1: La llegada" />
                        <template #hint>Sirve para identificar la escena en el editor.</template>
                    </FormField>

                    <div class="flex flex-wrap gap-4">
                        <FormField id="crear-escena-imagen" label="Fondo (imagen)"
                            :error="createForm.errors.background_image">
                            <input id="crear-escena-imagen" type="file" accept="image/*"
                                class="text-sm text-[#D9CFFB]"
                                @change="(evento) => (createForm.background_image = evento.target.files[0])" />
                            <template #hint>Imagen que se muestra detrás de los diálogos.</template>
                        </FormField>
                        <FormField id="crear-escena-musica" label="Música (mp3/ogg/wav)"
                            :error="createForm.errors.background_music">
                            <input id="crear-escena-musica" type="file" accept="audio/*"
                                class="text-sm text-[#D9CFFB]"
                                @change="(evento) => (createForm.background_music = evento.target.files[0])" />
                            <template #hint>Se reproduce mientras la escena esté activa.</template>
                        </FormField>
                    </div>

                    <SaveButton
                        type="button"
                        label="Crear escena"
                        processing-label="Guardando…"
                        success-label="Guardado"
                        :processing="createForm.processing"
                        :success="createForm.recentlySuccessful"
                        @click="submitCreate"
                    />
                </form>

                <!-- Listado de escenas -->
                <div class="space-y-3">
                    <div v-for="scene in scenes" :key="scene.id" class="vn-card">
                        <div v-if="editingId === scene.id" class="space-y-3">
                            <FormField id="editar-escena-titulo" label="Título" :error="editForm.errors.title">
                                <input id="editar-escena-titulo" v-model="editForm.title" type="text" class="vn-input" />
                            </FormField>
                            <div class="flex flex-wrap gap-4">
                                <FormField id="editar-escena-imagen" label="Fondo (imagen)"
                                    :error="editForm.errors.background_image">
                                    <input id="editar-escena-imagen" type="file" accept="image/*"
                                        class="text-sm text-[#D9CFFB]"
                                        @change="(evento) => (editForm.background_image = evento.target.files[0])" />
                                </FormField>
                                <FormField id="editar-escena-musica" label="Música (mp3/ogg/wav)"
                                    :error="editForm.errors.background_music">
                                    <input id="editar-escena-musica" type="file" accept="audio/*"
                                        class="text-sm text-[#D9CFFB]"
                                        @change="(evento) => (editForm.background_music = evento.target.files[0])" />
                                </FormField>
                            </div>
                            <div class="flex gap-3">
                                <SaveButton
                                    type="button"
                                    label="Guardar"
                                    processing-label="Guardando…"
                                    success-label="Guardado"
                                    :processing="editForm.processing"
                                    :success="editForm.recentlySuccessful"
                                    @click="submitEdit(scene.id)"
                                />
                                <button type="button" @click="editingId = null" class="vn-link text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        <div v-else class="flex items-center justify-between">
                            <div>
                                <span class="vn-scene-title">
                                    {{ scene.title ?? `Escena #${scene.order}` }}
                                </span>
                                <span class="ml-2 text-xs text-[#6E5FA3]">
                                    {{ scene.dialogue_nodes_count ?? 0 }} {{ etiquetasNodos[scene.id] }} de diálogo
                                </span>

                                <div class="mt-2 flex items-center gap-3">
                                    <img
                                        v-if="scene.background_image"
                                        :src="`/storage/${scene.background_image}`"
                                        class="h-16 w-24 rounded-lg border border-white/10 object-cover"
                                        alt="Fondo de la escena"
                                    />
                                    <audio
                                        v-if="scene.background_music"
                                        :src="`/storage/${scene.background_music}`"
                                        controls
                                        class="h-8"
                                    />
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-3 text-sm">
                                <Link :href="route('scenes.dialogue-nodes.index', scene.id)" class="vn-link">
                                    Diálogos
                                </Link>
                                <button type="button" @click="startEditing(scene)" class="vn-link">Editar</button>
                                <ConfirmDeleteButton
                                    title="Eliminar escena"
                                    message="¿Eliminar esta escena y todos sus diálogos?"
                                    label="Eliminar"
                                    :processing="eliminandoEscenaId === scene.id"
                                    @confirm="eliminarEscena(scene.id)"
                                >
                                    <span class="vn-link vn-link--danger" role="button" tabindex="0">Eliminar</span>
                                </ConfirmDeleteButton>
                            </div>
                        </div>
                    </div>

                    <EmptyState
                        v-if="scenes.length === 0"
                        title="Todavía no hay escenas"
                        description="Crea la primera con el formulario de arriba."
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
