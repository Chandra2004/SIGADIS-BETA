<script setup>
import TheHeader from '@/Components/Landing/TheHeader.vue';
import TheFooter from '@/Components/Landing/TheFooter.vue';
</script>

<template>
    <div class="min-h-screen scroll-smooth bg-brand-pink-50 text-neutral-900 antialiased">
        <TheHeader />
        <main>
            <slot />
        </main>
        <TheFooter />
    </div>
</template>
