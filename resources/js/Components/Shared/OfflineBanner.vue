<template>
  <div v-if="isOffline" class="offline-banner" role="status" aria-live="polite">
    Anda sedang offline. Beberapa fitur mungkin tidak tersedia.
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const online = ref(navigator.onLine);

const isOffline = computed(() => !online.value);

const updateStatus = () => {
    online.value = navigator.onLine;
};

onMounted(() => {
    window.addEventListener('online', updateStatus);
    window.addEventListener('offline', updateStatus);
});

onUnmounted(() => {
    window.removeEventListener('online', updateStatus);
    window.removeEventListener('offline', updateStatus);
});
</script>

<style scoped>
.offline-banner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    background: #ef4444;
    color: white;
    text-align: center;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
}
</style>
