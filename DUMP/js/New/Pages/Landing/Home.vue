<script setup>
import { Head } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import HeroSection from '@/Components/Landing/HeroSection.vue';
import EmergencySection from '@/Components/Landing/EmergencySection.vue';
import FinalCtaSection from '@/Components/Landing/FinalCtaSection.vue';
</script>

<template>
    <Head title="SIGADIS | Pendampingan Kesehatan Ibu" />

    <LandingLayout>
        <HeroSection />
        <EmergencySection />
        <FinalCtaSection />
    </LandingLayout>
</template>
