<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const showingUserMenu = ref(false);
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
                            Novelas
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

                <!-- Menú de usuario (desktop) -->
                <div class="relative hidden sm:block">
                    <button
                        type="button"
                        class="vn-navlink flex items-center gap-1"
                        @click="showingUserMenu = !showingUserMenu"
                    >
                        {{ $page.props.auth.user.name }}
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div v-if="showingUserMenu" class="vn-dropdown" @click="showingUserMenu = false">
                        <Link :href="route('profile.edit')" class="vn-dropdown-link">Mi perfil</Link>
                        <Link :href="route('logout')" method="post" as="button" class="vn-dropdown-link w-full text-left">
                            Cerrar sesión
                        </Link>
                    </div>
                </div>

                <!-- Hamburguesa (mobile) -->
                <button
                    type="button"
                    class="text-[#D9CFFB] sm:hidden"
                    @click="showingNavigationDropdown = !showingNavigationDropdown"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
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

            <!-- Menú responsive (mobile) -->
            <div v-if="showingNavigationDropdown" class="border-t border-white/10 sm:hidden">
                <div class="space-y-1 px-4 pb-3 pt-2">
                    <Link :href="route('dashboard')" class="vn-navlink block">Dashboard</Link>
                    <Link :href="route('novels.index')" class="vn-navlink block">Novelas</Link>
                    <Link :href="route('novels.public')" class="vn-navlink block">Explorar</Link>
                </div>
                <div class="border-t border-white/10 px-4 py-3">
                    <div class="text-sm font-medium text-[#FFF6FB]">{{ $page.props.auth.user.name }}</div>
                    <div class="text-xs text-[#9D8FD6]">{{ $page.props.auth.user.email }}</div>
                    <div class="mt-3 space-y-1">
                        <Link :href="route('profile.edit')" class="vn-navlink block">Mi perfil</Link>
                        <Link :href="route('logout')" method="post" as="button" class="vn-navlink block w-full text-left">
                            Cerrar sesión
                        </Link>
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

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Rubik:wght@400;500&display=swap');

.vn-shell {
    position: relative;
    min-height: 100vh;
    background: radial-gradient(140% 60% at 50% 0%, #2a1b54 0%, #120b26 55%);
    font-family: 'Rubik', system-ui, sans-serif;
    color: #fff6fb;
}

.vn-scanlines {
    position: fixed;
    inset: 0;
    pointer-events: none;
    background: repeating-linear-gradient(
        to bottom,
        rgba(255, 255, 255, 0.02) 0px,
        rgba(255, 255, 255, 0.02) 1px,
        transparent 1px,
        transparent 3px
    );
    mix-blend-mode: overlay;
    z-index: 0;
}

.vn-nav {
    position: relative;
    z-index: 20;
    border-bottom: 1px solid rgba(201, 184, 255, 0.15);
    background: rgba(18, 11, 38, 0.6);
    backdrop-filter: blur(10px);
}

.vn-wordmark {
    font-family: 'Fredoka', sans-serif;
    font-weight: 600;
    font-size: 1.1rem;
    color: #fff6fb;
    text-decoration: none;
}

.vn-navlink {
    border-radius: 0.5rem;
    padding: 0.5rem 0.85rem;
    font-size: 0.9rem;
    color: #d9cffb;
    transition: color 0.2s ease, background 0.2s ease;
}
.vn-navlink:hover {
    color: #fff6fb;
    background: rgba(255, 255, 255, 0.05);
}
.vn-navlink--active {
    color: #ff5fa2;
    background: rgba(255, 95, 162, 0.1);
}

.vn-dropdown {
    position: absolute;
    right: 0;
    top: calc(100% + 0.5rem);
    z-index: 30;
    min-width: 12rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(201, 184, 255, 0.2);
    background: #1a1140;
    padding: 0.4rem;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
}

.vn-dropdown-link {
    display: block;
    border-radius: 0.5rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
    color: #d9cffb;
    background: none;
    border: none;
    cursor: pointer;
}
.vn-dropdown-link:hover {
    background: rgba(255, 255, 255, 0.06);
    color: #fff6fb;
}

.vn-header {
    position: relative;
    z-index: 10;
    border-bottom: 1px solid rgba(201, 184, 255, 0.1);
}
</style>