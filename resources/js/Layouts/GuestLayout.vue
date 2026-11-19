<script setup>
import { Link } from '@inertiajs/vue3';
import ThemePicker from '@/Components/ThemePicker.vue';
</script>

<template>
    <div class="vn-page">
        <ThemePicker class="absolute right-4 top-4 z-20" />
        <div class="vn-scanlines"></div>
        <div class="vn-star vn-star--1">✦</div>
        <div class="vn-star vn-star--2">✧</div>
        <div class="vn-star vn-star--3">✦</div>

        <div class="relative mx-auto flex min-h-screen max-w-md flex-col items-center justify-center px-6 py-16">
            <Link href="/" class="vn-wordmark mb-8">VN Maker</Link>

            <div class="vn-card w-full">
                <slot />
            </div>
        </div>
    </div>
</template>
