<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const destroyScene = (sceneId) => {
    if (confirm('¿Borrar esta escena y todos sus diálogos?')) {
        useForm({}).delete(route('scenes.destroy', sceneId));
    }
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
                    <div>
                        <label for="title" class="vn-label">Título (opcional, solo para referencia)</label>
                        <input
                            id="title"
                            v-model="createForm.title"
                            type="text"
                            class="vn-input"
                            placeholder="Ej: Capítulo 1 - La llegada"
                        />
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <div>
                            <label for="bg-image" class="vn-label">Fondo (imagen)</label>
                            <input
                                id="bg-image"
                                type="file"
                                accept="image/*"
                                class="text-sm text-[#D9CFFB]"
                                @change="(e) => (createForm.background_image = e.target.files[0])"
                            />
                        </div>
                        <div>
                            <label for="bg-music" class="vn-label">Música (mp3/ogg/wav)</label>
                            <input
                                id="bg-music"
                                type="file"
                                accept="audio/*"
                                class="text-sm text-[#D9CFFB]"
                                @change="(e) => (createForm.background_music = e.target.files[0])"
                            />
                        </div>
                    </div>

                    <button type="submit" class="vn-pill vn-pill--solid" :disabled="createForm.processing">
                        Crear escena
                    </button>
                </form>

                <!-- Listado de escenas -->
                <div class="space-y-3">
                    <div v-for="scene in scenes" :key="scene.id" class="vn-card">
                        <div v-if="editingId === scene.id" class="space-y-3">
                            <input v-model="editForm.title" type="text" class="vn-input" />
                            <div class="flex flex-wrap gap-4">
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="text-sm text-[#D9CFFB]"
                                    @change="(e) => (editForm.background_image = e.target.files[0])"
                                />
                                <input
                                    type="file"
                                    accept="audio/*"
                                    class="text-sm text-[#D9CFFB]"
                                    @change="(e) => (editForm.background_music = e.target.files[0])"
                                />
                            </div>
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    class="vn-pill vn-pill--solid"
                                    :disabled="editForm.processing"
                                    @click="submitEdit(scene.id)"
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
                                <span class="vn-scene-title">
                                    {{ scene.title ?? `Escena #${scene.order}` }}
                                </span>
                                <span class="ml-2 text-xs text-[#6E5FA3]">
                                    {{ scene.dialogue_nodes_count }} nodo(s) de diálogo
                                </span>

                                <div class="mt-2 flex items-center gap-3">
                                    <img
                                        v-if="scene.background_image"
                                        :src="`/storage/${scene.background_image}`"
                                        class="h-16 w-24 rounded-lg border border-white/10 object-cover"
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
                                <button @click="startEditing(scene)" class="vn-link">Editar</button>
                                <button @click="destroyScene(scene.id)" class="vn-link vn-link--danger">Borrar</button>
                            </div>
                        </div>
                    </div>

                    <p v-if="scenes.length === 0" class="text-[#B8ACDD]">
                        Todavía no tenés escenas. Creá la primera arriba.
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

.vn-scene-title {
    font-family: 'Fredoka', sans-serif;
    font-weight: 500;
    color: #fff6fb;
}
</style>