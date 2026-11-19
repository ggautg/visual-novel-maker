<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClampedText from '@/Components/ClampedText.vue';
import EmptyState from '@/Components/EmptyState.vue';
import LikeButton from '@/Components/LikeButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    novels: Array,
    allTags: Array,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const selectedTag = ref(props.filters.tag ?? '');

// Explorar es público, pero dar «Me gusta» exige sesión.
const esInvitado = computed(() => !usePage().props.auth?.user);

const hayFiltrosActivos = computed(() => Boolean(search.value.trim() || selectedTag.value));

// Estado local de cada tarjeta: el contador cambia al pulsar, antes de que llegue la respuesta.
const estadosLike = reactive({});

watch(
    () => props.novels,
    (novelas) => {
        novelas.forEach((novela) => {
            if (!estadosLike[novela.id]) {
                estadosLike[novela.id] = {
                    count: novela.likes_count ?? 0,
                    liked: Boolean(novela.is_liked_by_me),
                    disabled: false,
                };
            }
        });
    },
    { immediate: true }
);

const aplicarFiltros = () => {
    router.get(
        route('novels.public'),
        { search: search.value || undefined, tag: selectedTag.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const limpiarFiltros = () => {
    search.value = '';
    selectedTag.value = '';
    props.filters.search = '';
    props.filters.tag = '';
    router.get(
        route('novels.public'),
        {},
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const alternarLike = (novela) => {
    const estado = estadosLike[novela.id];
    if (!estado || estado.disabled) {
        return;
    }

    const meGustabaAntes = estado.liked;
    const contadorAntes = estado.count;

    estado.liked = !meGustabaAntes;
    estado.count = Math.max(0, contadorAntes + (estado.liked ? 1 : -1));
    estado.disabled = true;

    router.post(route('novels.like', novela.id), {}, {
        preserveScroll: true,
        onError: () => {
            estado.liked = meGustabaAntes;
            estado.count = contadorAntes;
        },
        onFinish: () => {
            estado.disabled = false;
        },
    });
};
</script>

<template>

    <Head title="Explorar novelas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="vn-page-title">Explorar novelas visuales</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap gap-3">
                    <input v-model="search" type="text" class="vn-input flex-1"
                        placeholder="Buscar por título o descripción…" @keydown.enter="aplicarFiltros" />
                    <select v-model="selectedTag" class="vn-input" style="width: auto" @change="aplicarFiltros">
                        <option value="">Todas las etiquetas</option>
                        <option v-for="tag in allTags" :key="tag" :value="tag">{{ tag }}</option>
                    </select>
                    <button type="button" @click="aplicarFiltros" class="vn-pill vn-pill--solid">Buscar</button>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="novel in novels" :key="novel.id" class="vn-tile">
                        <Link :href="route('play.show', novel.id)">
                            <img v-if="novel.cover_image_url" :src="novel.cover_image_url" class="vn-tile-cover" />
                            <div v-else class="vn-tile-cover vn-tile-cover--placeholder">✦</div>

                            <h3 class="vn-tile-title">{{ novel.title }}</h3>
                            <ClampedText :text="novel.description ?? ''" :lines="2" class="vn-tile-desc" />

                            <div v-if="novel.tags.length > 0" class="mt-2 flex flex-wrap gap-1.5">
                                <span v-for="tag in novel.tags" :key="tag.id" class="vn-tag-chip">
                                    {{ tag.name }}
                                </span>
                            </div>

                            <p class="vn-tile-author">por {{ novel.user.name }}</p>
                        </Link>

                        <Link v-if="esInvitado" :href="route('login')" class="vn-like">
                            🤍 Inicia sesión para dar Me gusta
                        </Link>
                        <LikeButton
                            v-else
                            class="vn-like"
                            :count="estadosLike[novel.id]?.count ?? novel.likes_count ?? 0"
                            :liked="estadosLike[novel.id]?.liked ?? Boolean(novel.is_liked_by_me)"
                            :disabled="estadosLike[novel.id]?.disabled ?? false"
                            @toggle="alternarLike(novel)"
                        />
                    </div>
                </div>

                <EmptyState
                    v-if="novels.length === 0 && hayFiltrosActivos"
                    title="No encontramos novelas con esos filtros"
                    description="Prueba con otros términos o quita los filtros aplicados."
                >
                    <button type="button" class="vn-pill vn-pill--solid" @click="limpiarFiltros">
                        Limpiar filtros
                    </button>
                </EmptyState>

                <EmptyState
                    v-else-if="novels.length === 0"
                    title="Todavía no hay novelas publicadas"
                    description="Publica la tuya para que otros lectores puedan descubrirla."
                    action-label="Crear novela"
                    :action-href="route('novels.create')"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
