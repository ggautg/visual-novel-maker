<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormField from '@/Components/FormField.vue';
import SaveButton from '@/Components/SaveButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    description: '',
    cover_image: null,
});

const submit = () => {
    form.post(route('novels.store'), {
        forceFormData: true,
    });
};
</script>

<template>

    <Head title="Nueva novela" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="vn-page-title">Nueva novela</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="vn-card space-y-5">
                    <FormField id="crear-titulo" label="Título" :error="form.errors.title">
                        <input id="crear-titulo" v-model="form.title" type="text" class="vn-input" required autofocus />
                        <template #hint>El nombre que aparecerá en la portada de la novela.</template>
                    </FormField>

                    <FormField id="crear-descripcion" label="Descripción" :error="form.errors.description">
                        <textarea id="crear-descripcion" v-model="form.description" class="vn-input" rows="4" />
                        <template #hint>Una breve sinopsis para mostrar en el catálogo.</template>
                    </FormField>

                    <FormField id="crear-portada" label="Portada (opcional)" :error="form.errors.cover_image">
                        <input id="crear-portada" type="file" accept="image/*" class="vn-input"
                            @change="(evento) => (form.cover_image = evento.target.files[0])" />
                        <template #hint>Elige una imagen para representar tu novela.</template>
                    </FormField>

                    <SaveButton
                        type="button"
                        label="Crear novela"
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
