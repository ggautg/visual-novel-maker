<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    novels: Array,
});

const toggleLike = (novelId) => {
    router.post(route('novels.like', novelId), {}, { preserveScroll: true });
};
</script>

<template>

    <Head title="Explorar novelas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="vn-page-title">Explorar visual novels</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="novel in novels" :key="novel.id" class="vn-tile">
                        <Link :href="route('play.show', novel.id)">
                            <img v-if="novel.cover_image_url" :src="novel.cover_image_url" class="vn-tile-cover" />
                            <div v-else class="vn-tile-cover vn-tile-cover--placeholder">✦</div>

                            <h3 class="vn-tile-title">{{ novel.title }}</h3>
                            <p class="vn-tile-desc">{{ novel.description }}</p>
                            <p class="vn-tile-author">por {{ novel.user.name }}</p>
                        </Link>

                        <button @click="toggleLike(novel.id)" class="vn-like"
                            :class="{ 'vn-like--active': novel.is_liked_by_me }">
                            {{ novel.is_liked_by_me ? '💗' : '🤍' }} {{ novel.likes_count }}
                        </button>
                    </div>

                    <p v-if="novels.length === 0" class="text-[#B8ACDD]">
                        Todavía no hay novelas publicadas. ¡Publicá la tuya!
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

.vn-tile {
    display: block;
    border-radius: 1rem;
    border: 1px solid rgba(201, 184, 255, 0.15);
    background: rgba(255, 255, 255, 0.03);
    padding: 1rem;
    transition: transform 0.2s ease, border-color 0.2s ease;
}

.vn-tile:hover {
    transform: translateY(-3px);
    border-color: rgba(255, 95, 162, 0.4);
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
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.vn-tile-author {
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: #6e5fa3;
}

.vn-like {
    margin-top: 0.6rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.85rem;
    color: #b8acdd;
    background: none;
    border: none;
    cursor: pointer;
    transition: transform 0.15s ease;
}

.vn-like:hover {
    transform: scale(1.08);
}

.vn-like--active {
    color: #ff8fc0;
}
</style>