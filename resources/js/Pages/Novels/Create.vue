<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
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
                    <div>
                        <label for="title" class="vn-label">Título</label>
                        <input id="title" v-model="form.title" type="text" class="vn-input" required autofocus />
                        <InputError :message="form.errors.title" class="mt-1" />
                    </div>

                    <div>
                        <label for="description" class="vn-label">Descripción</label>
                        <textarea id="description" v-model="form.description" class="vn-input" rows="4" />
                        <InputError :message="form.errors.description" class="mt-1" />
                    </div>

                    <div>
                        <label for="cover_image" class="vn-label">Portada (opcional)</label>
                        <input id="cover_image" type="file" accept="image/*" class="vn-input"
                            @change="(e) => (form.cover_image = e.target.files[0])" />
                        <InputError :message="form.errors.cover_image" class="mt-1" />
                    </div>

                    <button type="submit" class="vn-pill vn-pill--solid" :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing">
                        Crear novela
                    </button>
                </form>
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
    padding: 1.75rem;
}

.vn-label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.85rem;
    color: #d9cffb;
}

.vn-input {
    width: 100%;
    border-radius: 0.65rem;
    border: 1px solid rgba(201, 184, 255, 0.25);
    background: rgba(255, 255, 255, 0.05);
    padding: 0.6rem 0.85rem;
    font-size: 0.95rem;
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
    padding: 0.7rem 1.75rem;
    font-size: 0.95rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg, #ff5fa2, #ff8fc0);
    color: #1a0f36;
    box-shadow: 0 8px 24px rgba(255, 95, 162, 0.35);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.vn-pill:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(255, 95, 162, 0.45);
}

.vn-pill:disabled {
    cursor: not-allowed;
}
</style>