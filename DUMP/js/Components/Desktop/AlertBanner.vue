<script setup>
defineProps({
    alert: { type: Object, required: true },
});

defineEmits(['open']);
</script>

<template>
    <button
        type="button"
        class="flex w-full items-center justify-between rounded-lg border-2 border-emergency-alert bg-emergency-pending-bg p-4 text-left"
        @click="$emit('open', alert)"
    >
        <div>
            <p class="font-bold text-emergency-alert">Peringatan darurat: {{ alert.mother_name }}</p>
            <p class="text-xs text-neutral-700">{{ new Date(alert.triggered_at).toLocaleString('id-ID') }}</p>
        </div>
        <span class="rounded-full bg-emergency-alert px-3 py-1 text-xs font-semibold text-white capitalize">
            {{ alert.status }}
        </span>
    </button>
</template>
