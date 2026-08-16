<script setup>
import { computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Konfirmasi Aksi',
    },
    message: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'primary', // 'primary' | 'success' | 'warning' | 'danger' | 'info'
        validator: (value) => ['primary', 'success', 'warning', 'danger', 'info'].includes(value),
    },
    icon: {
        type: String,
        default: '',
    },
    confirmText: {
        type: String,
        default: 'Konfirmasi',
    },
    cancelText: {
        type: String,
        default: 'Batal',
    },
    confirmDisabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    closeOnClickOutside: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close', 'confirm', 'cancel']);

const handleBackdropClick = () => {
    if (props.closeOnClickOutside && !props.loading) {
        emit('close');
        emit('cancel');
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Escape' && props.show && !props.loading) {
        emit('close');
        emit('cancel');
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

// Style mappings based on type
const typeConfig = computed(() => {
    switch (props.type) {
        case 'success':
            return {
                iconBg: 'bg-emerald-100 text-emerald-700 border-emerald-200',
                defaultIcon: 'check_circle',
                buttonBg: 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-700/20',
                titleColor: 'text-emerald-950',
            };
        case 'danger':
            return {
                iconBg: 'bg-rose-100 text-rose-700 border-rose-200',
                defaultIcon: 'error',
                buttonBg: 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-700/20',
                titleColor: 'text-rose-950',
            };
        case 'warning':
            return {
                iconBg: 'bg-amber-100 text-amber-800 border-amber-200',
                defaultIcon: 'warning',
                buttonBg: 'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-700/20',
                titleColor: 'text-amber-950',
            };
        case 'info':
            return {
                iconBg: 'bg-sky-100 text-sky-800 border-sky-200',
                defaultIcon: 'info',
                buttonBg: 'bg-[#123356] hover:bg-[#2C4A6E] text-white shadow-sky-900/20',
                titleColor: 'text-[#123356]',
            };
        case 'primary':
        default:
            return {
                iconBg: 'bg-[#123356]/10 text-[#123356] border-[#123356]/20',
                defaultIcon: 'help',
                buttonBg: 'bg-[#123356] hover:bg-[#2C4A6E] text-white shadow-blue-900/20',
                titleColor: 'text-[#123356]',
            };
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs antialiased"
                @click="handleBackdropClick"
            >
                <!-- Modal Card Container -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                >
                    <div
                        v-if="show"
                        class="bg-white w-full max-w-md rounded-3xl p-6 sm:p-7 border border-[#E3E2E5] shadow-2xl space-y-5 text-[#26292E] relative overflow-hidden"
                        @click.stop
                    >
                        <!-- Header / Icon + Title -->
                        <div class="flex items-start gap-4">
                            <div
                                :class="[
                                    'w-12 h-12 rounded-2xl flex items-center justify-center border shrink-0 shadow-xs',
                                    typeConfig.iconBg
                                ]"
                            >
                                <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">
                                    {{ icon || typeConfig.defaultIcon }}
                                </span>
                            </div>

                            <div class="space-y-1 flex-1 pr-6">
                                <h3 :class="['text-lg font-extrabold tracking-tight', typeConfig.titleColor]">
                                    {{ title }}
                                </h3>
                                <p v-if="message" class="text-xs text-[#43474E] leading-relaxed">
                                    {{ message }}
                                </p>
                            </div>

                            <!-- Close Button (X) -->
                            <button
                                type="button"
                                @click="emit('close'); emit('cancel')"
                                class="absolute top-5 right-5 text-neutral-400 hover:text-neutral-700 p-1.5 rounded-xl hover:bg-neutral-100 transition-colors"
                            >
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>

                        <!-- Content / Form Slot -->
                        <div v-if="$slots.default" class="text-xs text-[#43474E] space-y-3">
                            <slot />
                        </div>

                        <!-- Actions / Footer Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#F2F3F5]">
                            <button
                                type="button"
                                :disabled="loading"
                                @click="emit('cancel'); emit('close')"
                                class="px-4 py-2.5 rounded-xl text-xs font-bold text-[#43474E] hover:bg-neutral-100 transition-all cursor-pointer disabled:opacity-50"
                            >
                                {{ cancelText }}
                            </button>

                            <button
                                type="button"
                                :disabled="confirmDisabled || loading"
                                @click="emit('confirm')"
                                :class="[
                                    'px-5 py-2.5 rounded-xl text-xs font-extrabold shadow-md transition-all active:scale-95 cursor-pointer flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed',
                                    typeConfig.buttonBg
                                ]"
                            >
                                <span v-if="loading" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                                <span>{{ confirmText }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
