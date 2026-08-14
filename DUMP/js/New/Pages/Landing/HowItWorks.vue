<script setup>
import { Head } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import HowItWorksSection from '@/Components/Landing/HowItWorksSection.vue';
</script>

<template>
    <Head title="Cara Kerja SIGADIS" />

    <LandingLayout>
        <HowItWorksSection />
    </LandingLayout>
</template>
