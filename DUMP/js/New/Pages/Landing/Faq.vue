<script setup>
import { Head } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import FaqSection from '@/Components/Landing/FaqSection.vue';
</script>

<template>
    <Head title="FAQ SIGADIS" />

    <LandingLayout>
        <FaqSection />
    </LandingLayout>
</template>
