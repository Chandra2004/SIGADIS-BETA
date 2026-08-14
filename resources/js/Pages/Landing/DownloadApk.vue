<script setup>
import { Head } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import DownloadApkSection from '@/Components/Landing/DownloadApkSection.vue';
</script>

<template>
    <Head title="Download APK SIGADIS" />

    <LandingLayout>
        <DownloadApkSection />
    </LandingLayout>
</template>
