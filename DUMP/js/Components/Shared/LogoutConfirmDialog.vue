<script setup>
defineProps({
    show: { type: Boolean, required: true },
    message: { type: String, required: true },
});

defineEmits(['confirm', 'cancel']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-6">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 text-center">
            <img src="/assets/images/mascot/pose-08-melambai-ramah.png" alt="" class="mx-auto mb-4 h-24 w-24 object-contain" />
            <p class="mb-2 text-lg font-semibold text-neutral-900">Yakin ingin keluar?</p>
            <p class="mb-6 text-sm text-neutral-600">{{ message }}</p>
            <div class="space-y-3">
                <button type="button" class="btn w-full border-none bg-brand-navy-900 text-white" @click="$emit('confirm')">
                    Ya, Keluar
                </button>
                <button type="button" class="btn btn-ghost w-full" @click="$emit('cancel')">Batal</button>
            </div>
        </div>
    </div>
</template>
