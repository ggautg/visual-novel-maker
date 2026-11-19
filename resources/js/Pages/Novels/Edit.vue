<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormField from '@/Components/FormField.vue';
import SaveButton from '@/Components/SaveButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    novel: Object,
});

const form = useForm({
    title: props.novel.title,
    description: props.novel.description,
    status: props.novel.status,
    cover_image: null,
    tags: props.novel.tags.map((tag) => tag.name),
});

// Texto libre para agregar una etiqueta nueva.
const newTag = ref('');

const addTag = () => {
    const value = newTag.value.trim().toLowerCase();
    if (value && !form.tags.includes(value)) {
        form.tags.push(value);
    }
    newTag.value = '';
};

const removeTag = (tag) => {
    form.tags = form.tags.filter((t) => t !== tag);
};

const submit = () => {
    form
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(route('novels.update', props.novel.id), {
            forceFormData: true,
        });
};
</script>

<template>

    <Head :title="novel.title" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="vn-page-title">Editando: {{ novel.title }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="vn-card space-y-5">
                    <FormField id="editar-titulo" label="Título" :error="form.errors.title">
                        <input id="editar-titulo" v-model="form.title" type="text" class="vn-input" required />
                    </FormField>

                    <FormField id="editar-descripcion" label="Descripción" :error="form.errors.description">
                        <textarea id="editar-descripcion" v-model="form.description" class="vn-input" rows="4" />
                    </FormField>

                    <FormField id="editar-estado" label="Estado" :error="form.errors.status">
                        <select id="editar-estado" v-model="form.status" class="vn-input">
                            <option value="draft">Borrador</option>
                            <option value="published">Publicada</option>
                        </select>
                        <template #hint>Publica la novela para que aparezca en el catálogo.</template>
                    </FormField>

                    <FormField id="editar-portada" label="Portada" :error="form.errors.cover_image">
                        <img v-if="novel.cover_image_url" :src="novel.cover_image_url"
                            class="mb-2 h-32 w-24 rounded-lg border border-white/15 object-cover" />
                        <input id="editar-portada" type="file" accept="image/*" class="vn-input"
                            @change="(evento) => (form.cover_image = evento.target.files[0])" />
                        <template #hint>Deja el archivo vacío para conservar la portada actual.</template>
                    </FormField>

                    <FormField id="editar-etiquetas" label="Etiquetas" :error="form.errors.tags">
                        <div class="mb-2 flex flex-wrap gap-2">
                            <span v-for="tag in form.tags" :key="tag" class="vn-tag">
                                {{ tag }}
                                <button type="button" @click="removeTag(tag)" class="vn-tag-remove"
                                    :aria-label="`Quitar etiqueta ${tag}`">×</button>
                            </span>
                        </div>
                        <input id="editar-etiquetas" v-model="newTag" type="text" class="vn-input"
                            placeholder="Escribe una etiqueta y pulsa Enter" @keydown.enter.prevent="addTag" />
                        <template #hint>Pulsa Enter para agregar cada etiqueta.</template>
                    </FormField>

                    <SaveButton
                        type="button"
                        label="Guardar cambios"
                        processing-label="Guardando…"
                        success-label="Guardado"
                        :processing="form.processing"
                        :success="form.recentlySuccessful"
                        @click="submit"
                    />
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
