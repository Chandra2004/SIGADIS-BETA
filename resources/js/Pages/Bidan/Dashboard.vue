<script setup>
import Dashboard from '@/Pages/Desktop/Dashboard.vue';

defineProps({
    worker: Object,
    summary: Object,
    filter: String,
    patients: Array,
    pendingAlerts: Array,
});
</script>

<template>
    <Dashboard
        :worker="worker"
        :summary="summary"
        :filter="filter"
        :patients="patients"
        :pendingAlerts="pendingAlerts"
    />
</template>
