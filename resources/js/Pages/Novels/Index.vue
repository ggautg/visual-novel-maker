<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';

defineProps({
    novels: Array,
});

const destroyNovel = (novelId) => {
    if (confirm('¿Borrar esta novela? Se van a borrar también sus personajes, escenas y todo su contenido.')) {
        useForm({}).delete(route('novels.destroy', novelId));
    }
};
</script>

<template>

    <Head title="Mis novelas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="vn-page-title">Mis visual novels</h2>
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
                            <p class="vn-tile-desc">{{ novel.description }}</p>

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
                            <button @click="destroyNovel(novel.id)" class="vn-tile-action vn-tile-action--danger">
                                Borrar
                            </button>

                        </div>
                    </div>

                    <p v-if="novels.length === 0" class="text-[#B8ACDD]">
                        Todavía no tenés ninguna novela. ¡Creá la primera!
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.vn-tile-action--danger {
    color: #ff6b8a;
}

.vn-tile-action--danger:hover {
    background: rgba(255, 95, 128, 0.12);
    color: #ff9db3;
}

.vn-page-title {
    font-family: 'Fredoka', sans-serif;
    font-weight: 600;
    font-size: 1.35rem;
    color: #fff6fb;
}

.vn-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.6rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.vn-pill--solid {
    background: linear-gradient(135deg, #ff5fa2, #ff8fc0);
    color: #1a0f36;
    box-shadow: 0 8px 24px rgba(255, 95, 162, 0.35);
}

.vn-pill--solid:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(255, 95, 162, 0.45);
}

.vn-tile {
    border-radius: 1rem;
    border: 1px solid rgba(201, 184, 255, 0.15);
    background: rgba(255, 255, 255, 0.03);
    padding: 1rem;
    transition: border-color 0.2s ease;
}

.vn-tile:hover {
    border-color: rgba(255, 95, 162, 0.3);
}

.vn-tile-cover {
    margin-bottom: 0.75rem;
    height: 10rem;
    width: 100%;
    border-radius: 0.65rem;
    object-fit: cover;
}

.vn-tile-cover--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2a1b54, #1a1140);
    color: #c9b8ff;
    font-size: 2rem;
}

.vn-tile-title {
    font-family: 'Fredoka', sans-serif;
    font-weight: 500;
    font-size: 1.05rem;
    color: #fff6fb;
}

.vn-tile-desc {
    margin-top: 0.35rem;
    font-size: 0.85rem;
    color: #b8acdd;
}

.vn-badge {
    flex-shrink: 0;
    border-radius: 999px;
    padding: 0.2rem 0.6rem;
    font-size: 0.7rem;
    font-weight: 500;
}

.vn-badge--live {
    background: rgba(94, 241, 230, 0.15);
    color: #5ef1e6;
}

.vn-badge--draft {
    background: rgba(201, 184, 255, 0.12);
    color: #c9b8ff;
}

.vn-tile-actions {
    margin-top: 0.85rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    border-top: 1px solid rgba(201, 184, 255, 0.1);
    padding-top: 0.75rem;
}

.vn-tile-action {
    border-radius: 0.5rem;
    padding: 0.3rem 0.65rem;
    font-size: 0.78rem;
    color: #d9cffb;
    background: rgba(255, 255, 255, 0.04);
    transition: background 0.2s ease, color 0.2s ease;
}

.vn-tile-action:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #fff6fb;
}

.vn-tile-action--play {
    color: #5ef1e6;
    font-weight: 600;
}

.vn-tag-chip {
    display: inline-flex;
    border-radius: 999px;
    background: rgba(94, 241, 230, 0.12);
    color: #5ef1e6;
    padding: 0.15rem 0.55rem;
    font-size: 0.7rem;
}
</style>