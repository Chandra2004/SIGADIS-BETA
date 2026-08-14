<script setup>
import TheHeader from '@/Components/Landing/TheHeader.vue';
import TheFooter from '@/Components/Landing/TheFooter.vue';
</script>

<template>
    <div class="min-h-screen scroll-smooth bg-ink-100 text-ink-900">
        <TheHeader />
        <main>
            <slot />
        </main>
        <TheFooter />
    </div>
</template>
