<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClampedText from '@/Components/ClampedText.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    novels: Array,
});

// Un solo id a la vez evita borrados dobles mientras la petición está en curso.
const eliminandoId = ref(null);

const eliminarNovela = (novelId) => {
    eliminandoId.value = novelId;
    useForm({}).delete(route('novels.destroy', novelId), {
        onFinish: () => {
            eliminandoId.value = null;
        },
    });
};
</script>

<template>

    <Head title="Mis novelas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="vn-page-title">Mis novelas visuales</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-end">
                    <Link :href="route('novels.create')" class="vn-pill vn-pill--solid">
                        Nueva novela
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="novel in novels" :key="novel.id" class="vn-tile">
                        <Link :href="route('novels.edit', novel.id)">
                            <img v-if="novel.cover_image_url" :src="novel.cover_image_url" class="vn-tile-cover" />
                            <div v-else class="vn-tile-cover vn-tile-cover--placeholder">✦</div>

                            <div class="flex items-start justify-between gap-2">
                                <h3 class="vn-tile-title">{{ novel.title }}</h3>
                                <span class="vn-badge"
                                    :class="novel.status === 'published' ? 'vn-badge--live' : 'vn-badge--draft'">
                                    {{ novel.status === 'published' ? 'Publicada' : 'Borrador' }}
                                </span>
                            </div>
                            <ClampedText :text="novel.description ?? ''" :lines="2" class="vn-tile-desc" />

                            <div v-if="novel.tags.length > 0" class="mt-2 flex flex-wrap gap-1.5">
                                <span v-for="tag in novel.tags" :key="tag.id" class="vn-tag-chip">
                                    {{ tag.name }}
                                </span>
                            </div>
                        </Link>

                        <div class="vn-tile-actions">
                            <Link :href="route('novels.characters.index', novel.id)" class="vn-tile-action">
                                Personajes
                            </Link>
                            <Link :href="route('novels.scenes.index', novel.id)" class="vn-tile-action">
                                Escenas
                            </Link>
                            <Link :href="route('novels.variables.index', novel.id)" class="vn-tile-action">
                                Variables
                            </Link>
                            <Link :href="route('play.show', novel.id)" class="vn-tile-action vn-tile-action--play">
                                ▶ Jugar
                            </Link>
                            <ConfirmDeleteButton
                                title="Eliminar novela"
                                message="¿Eliminar esta novela? También se eliminarán sus personajes, escenas y todo su contenido."
                                label="Eliminar"
                                :processing="eliminandoId === novel.id"
                                @confirm="eliminarNovela(novel.id)"
                            >
                                <span class="vn-tile-action vn-tile-action--danger" role="button" tabindex="0">Eliminar</span>
                            </ConfirmDeleteButton>
                        </div>
                    </div>
                </div>

                <EmptyState
                    v-if="novels.length === 0"
                    title="Todavía no hay novelas"
                    description="Crea la primera novela para empezar a escribir tu historia."
                    action-label="Crear novela"
                    :action-href="route('novels.create')"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
