<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ThemePicker from '@/Components/ThemePicker.vue';

const showingNavigationDropdown = ref(false);

// Explorar es público: el usuario puede no estar autenticado y auth.user es null.
const usuario = computed(() => usePage().props.auth?.user ?? null);

const closeNavigation = () => {
    showingNavigationDropdown.value = false;
};
</script>

<template>
    <div class="vn-shell">
        <div class="vn-scanlines"></div>

        <nav class="vn-nav">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-8">
                    <Link :href="route('novels.index')" class="vn-wordmark">VN Maker</Link>

                    <div class="hidden gap-1 sm:flex">
                        <Link
                            :href="route('novels.index')"
                            class="vn-navlink"
                            :class="{ 'vn-navlink--active': route().current('novels.index') }"
                        >
                            Mis novelas
                        </Link>
                        <Link
                            :href="route('novels.public')"
                            class="vn-navlink"
                            :class="{ 'vn-navlink--active': route().current('novels.public') }"
                        >
                            Explorar
                        </Link>
                    </div>
                </div>

                <div class="ml-auto mr-3 hidden sm:block">
                    <ThemePicker />
                </div>

                <Dropdown align="right" width="48" class="hidden sm:block">
                    <template #trigger>
                        <button type="button" class="vn-navlink flex items-center gap-1">
                            {{ usuario?.name ?? 'Invitado' }}
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <template v-if="usuario">
                            <DropdownLink :href="route('profile.edit')">Mi perfil</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Cerrar sesión
                            </DropdownLink>
                        </template>
                        <template v-else>
                            <DropdownLink :href="route('login')">Inicia sesión</DropdownLink>
                            <DropdownLink :href="route('register')">Regístrate</DropdownLink>
                        </template>
                    </template>
                </Dropdown>

                <button
                    type="button"
                    class="vn-navlink sm:hidden"
                    :aria-expanded="showingNavigationDropdown"
                    aria-label="Abrir menú de navegación"
                    @click="showingNavigationDropdown = !showingNavigationDropdown"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            :class="showingNavigationDropdown ? 'hidden' : 'inline-flex'"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            :class="showingNavigationDropdown ? 'inline-flex' : 'hidden'"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div v-if="showingNavigationDropdown" class="border-t border-[var(--vn-border)] sm:hidden">
                <div class="space-y-1 px-4 pb-3 pt-2">
                    <Link :href="route('novels.index')" class="vn-navlink block" @click="closeNavigation">
                        Mis novelas
                    </Link>
                    <Link :href="route('novels.public')" class="vn-navlink block" @click="closeNavigation">
                        Explorar
                    </Link>
                </div>
                <div class="px-4 pb-3">
                    <ThemePicker />
                </div>
                <div class="border-t border-[var(--vn-border)] px-4 py-3">
                    <template v-if="usuario">
                        <div class="text-sm font-medium">{{ usuario.name }}</div>
                        <div class="text-xs opacity-80">{{ usuario.email }}</div>
                    </template>
                    <div v-else class="text-sm font-medium">Invitado</div>
                    <div class="mt-3 space-y-1">
                        <template v-if="usuario">
                            <Link :href="route('profile.edit')" class="vn-navlink block" @click="closeNavigation">
                                Mi perfil
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="vn-navlink block w-full text-left"
                                @click="closeNavigation"
                            >
                                Cerrar sesión
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="vn-navlink block" @click="closeNavigation">
                                Inicia sesión
                            </Link>
                            <Link :href="route('register')" class="vn-navlink block" @click="closeNavigation">
                                Regístrate
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="vn-header">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="relative">
            <slot />
        </main>
    </div>
</template>
