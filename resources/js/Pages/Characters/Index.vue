<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
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

const destroyCharacter = (characterId) => {
    if (confirm('¿Seguro que querés borrar este personaje? También se borran sus expresiones.')) {
        useForm({}).delete(route('characters.destroy', characterId));
    }
};

// --- Formularios de "subir expresión" independientes por personaje ---
// Mismo patrón que en Choices/Index.vue: un useForm() por character.id,
// creado en un watch (corre antes del render) en vez de uno solo
// compartido por todos los personajes del v-for.
const expressionForms = reactive({});

watch(
    () => props.characters.map((c) => c.id),
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

const destroyExpression = (expressionId) => {
    if (confirm('¿Borrar esta expresión?')) {
        useForm({}).delete(route('expressions.destroy', expressionId));
    }
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
                    <div class="flex-1">
                        <label for="name" class="vn-label">Nombre del personaje</label>
                        <input id="name" v-model="createForm.name" type="text" class="vn-input" required />
                        <InputError :message="createForm.errors.name" class="mt-1" />
                    </div>
                    <div>
                        <label for="color" class="vn-label">Color</label>
                        <input id="color" v-model="createForm.color" type="color" class="vn-color" />
                    </div>
                    <button type="submit" class="vn-pill vn-pill--solid" :disabled="createForm.processing">
                        Agregar
                    </button>
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
                            <div class="flex-1">
                                <input v-model="editForm.name" type="text" class="vn-input" required />
                                <InputError :message="editForm.errors.name" class="mt-1" />
                            </div>
                            <input v-model="editForm.color" type="color" class="vn-color" />
                            <button type="submit" class="vn-pill vn-pill--solid" :disabled="editForm.processing">
                                Guardar
                            </button>
                            <button type="button" @click="editingId = null" class="vn-link text-sm">
                                Cancelar
                            </button>
                        </form>

                        <!-- Modo visualización: solo si su formulario de expresión ya existe -->
                        <div v-else-if="expressionForms[character.id]">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="vn-dot" :style="{ backgroundColor: character.color ?? '#c9b8ff' }" />
                                    <span class="vn-character-name" :style="{ color: character.color ?? '#fff6fb' }">
                                        {{ character.name }}
                                    </span>
                                </div>
                                <div class="flex gap-3 text-sm">
                                    <button @click="startEditing(character)" class="vn-link">Editar</button>
                                    <button @click="destroyCharacter(character.id)" class="vn-link vn-link--danger">
                                        Borrar
                                    </button>
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
                                        <button @click="destroyExpression(expression.id)" class="vn-expr-remove">×</button>
                                    </div>
                                </div>

                                <form @submit.prevent="submitExpression(character.id)" class="flex flex-wrap items-end gap-3">
                                    <div>
                                        <label class="vn-label">Nombre</label>
                                        <input
                                            v-model="expressionForms[character.id].name"
                                            type="text"
                                            class="vn-input w-32"
                                            placeholder="happy, sad..."
                                        />
                                    </div>
                                    <div>
                                        <label class="vn-label">Imagen</label>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="text-sm text-[#D9CFFB]"
                                            @change="(e) => (expressionForms[character.id].image = e.target.files[0])"
                                        />
                                    </div>
                                    <button
                                        type="submit"
                                        class="vn-pill vn-pill--ghost"
                                        :disabled="expressionForms[character.id].processing"
                                    >
                                        Subir
                                    </button>
                                </form>
                                <InputError :message="expressionForms[character.id].errors.image" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <p v-if="characters.length === 0" class="text-[#B8ACDD]">
                        Todavía no tenés personajes. Agregá el primero arriba.
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

.vn-color {
    height: 2.4rem;
    width: 3.5rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(201, 184, 255, 0.25);
    background: transparent;
    cursor: pointer;
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
    white-space: nowrap;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.vn-pill--solid {
    background: linear-gradient(135deg, #ff5fa2, #ff8fc0);
    color: #1a0f36;
    box-shadow: 0 6px 18px rgba(255, 95, 162, 0.3);
}
.vn-pill--solid:hover:not(:disabled) {
    transform: translateY(-2px);
}
.vn-pill--ghost {
    border: 1px solid rgba(201, 184, 255, 0.3);
    background: transparent;
    color: #d9cffb;
}
.vn-pill--ghost:hover:not(:disabled) {
    border-color: #5ef1e6;
    color: #5ef1e6;
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

.vn-dot {
    height: 1rem;
    width: 1rem;
    border-radius: 999px;
}

.vn-character-name {
    font-family: 'Fredoka', sans-serif;
    font-weight: 500;
    font-size: 1rem;
}

.vn-expressions {
    margin-top: 1rem;
    border-top: 1px solid rgba(201, 184, 255, 0.1);
    padding-top: 1rem;
}

.vn-expr-img {
    height: 5rem;
    width: 5rem;
    border-radius: 0.6rem;
    border: 1px solid rgba(201, 184, 255, 0.2);
    object-fit: cover;
}

.vn-expr-remove {
    position: absolute;
    right: -0.4rem;
    top: -0.4rem;
    height: 1.25rem;
    width: 1.25rem;
    border-radius: 999px;
    background: #ff5f7a;
    color: #1a0f36;
    font-size: 0.7rem;
    line-height: 1;
    border: none;
    cursor: pointer;
}
</style>