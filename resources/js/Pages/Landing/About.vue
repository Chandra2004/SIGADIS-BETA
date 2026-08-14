<script setup>
import { Head } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import ProblemSection from '@/Components/Landing/ProblemSection.vue';
import SolutionSection from '@/Components/Landing/SolutionSection.vue';
import RolesSection from '@/Components/Landing/RolesSection.vue';
import AdvantagesSection from '@/Components/Landing/AdvantagesSection.vue';
</script>

<template>
    <Head title="Tentang SIGADIS" />

    <LandingLayout>
        <ProblemSection />
        <SolutionSection />
        <RolesSection />
        <AdvantagesSection />
    </LandingLayout>
</template>
