<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

// List of active toasts: { id, type, title, message, duration }
const toasts = ref([]);
let toastIdCounter = 0;

const addToast = (type, message, title = '') => {
    if (!message) return;

    const id = ++toastIdCounter;
    let defaultTitle = '';
    switch (type) {
        case 'warning':
            defaultTitle = 'Peringatan';
            break;
        case 'error':
            defaultTitle = 'Terjadi Kesalahan';
            break;
        case 'success':
            defaultTitle = 'Berhasil';
            break;
        case 'info':
        default:
            defaultTitle = 'Informasi';
            break;
    }

    const toast = {
        id,
        type,
        title: title || defaultTitle,
        message,
        progress: 100,
    };

    toasts.value.push(toast);

    // Auto dismiss after 4.5 seconds
    setTimeout(() => {
        removeToast(id);
    }, 4500);
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
};

// Watch for Inertia flash props changes
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;

        if (flash.success || flash.status) {
            addToast('success', flash.success || flash.status);
        }
        if (flash.warning) {
            addToast('warning', flash.warning);
        }
        if (flash.error || flash.alert) {
            addToast('error', flash.error || flash.alert);
        }
        if (flash.info) {
            addToast('info', flash.info);
        }
    },
    { deep: true, immediate: true }
);

// Global window programmatic triggers
const handleCustomToastEvent = (e) => {
    if (e.detail) {
        addToast(e.detail.type || 'info', e.detail.message, e.detail.title);
    }
};

onMounted(() => {
    window.addEventListener('sigadis:toast', handleCustomToastEvent);

    // Provide global helper
    window.$toast = {
        success: (msg, title = 'Berhasil') => addToast('success', msg, title),
        warning: (msg, title = 'Peringatan') => addToast('warning', msg, title),
        error: (msg, title = 'Terjadi Kesalahan') => addToast('error', msg, title),
        info: (msg, title = 'Informasi') => addToast('info', msg, title),
    };
});

onUnmounted(() => {
    window.removeEventListener('sigadis:toast', handleCustomToastEvent);
});

const getToastConfig = (type) => {
    switch (type) {
        case 'warning':
            return {
                border: 'border-amber-300/80',
                bg: 'bg-amber-50/95',
                iconBg: 'bg-amber-500 text-white shadow-amber-500/20',
                icon: 'warning',
                titleColor: 'text-amber-950',
                messageColor: 'text-amber-900',
                progressBar: 'bg-amber-500',
            };
        case 'error':
            return {
                border: 'border-rose-300/80',
                bg: 'bg-rose-50/95',
                iconBg: 'bg-rose-500 text-white shadow-rose-500/20',
                icon: 'error',
                titleColor: 'text-rose-950',
                messageColor: 'text-rose-900',
                progressBar: 'bg-rose-500',
            };
        case 'success':
            return {
                border: 'border-emerald-300/80',
                bg: 'bg-emerald-50/95',
                iconBg: 'bg-emerald-600 text-white shadow-emerald-600/20',
                icon: 'check_circle',
                titleColor: 'text-emerald-950',
                messageColor: 'text-emerald-900',
                progressBar: 'bg-emerald-600',
            };
        case 'info':
        default:
            return {
                border: 'border-blue-300/80',
                bg: 'bg-blue-50/95',
                iconBg: 'bg-[#123356] text-white shadow-blue-900/20',
                icon: 'info',
                titleColor: 'text-[#123356]',
                messageColor: 'text-blue-950',
                progressBar: 'bg-[#123356]',
            };
    }
};
</script>

<template>
    <div
        class="fixed top-5 right-4 left-4 sm:left-auto sm:right-6 sm:w-96 z-50 flex flex-col gap-3 pointer-events-none antialiased select-none"
        aria-live="polite"
    >
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-4 sm:translate-y-0 sm:translate-x-8 scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
            leave-to-class="opacity-0 -translate-y-4 sm:translate-y-0 sm:translate-x-8 scale-95"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'pointer-events-auto rounded-2xl border shadow-xl p-4 flex items-start gap-3 relative overflow-hidden backdrop-blur-md transition-all',
                    getToastConfig(toast.type).bg,
                    getToastConfig(toast.type).border,
                ]"
            >
                <!-- Animated Progress Bar -->
                <div class="absolute top-0 left-0 right-0 h-1 bg-black/5 overflow-hidden">
                    <div
                        class="h-full w-full animate-[progress_4.5s_linear_forwards]"
                        :class="getToastConfig(toast.type).progressBar"
                    ></div>
                </div>

                <!-- Status Icon -->
                <div
                    :class="[
                        'w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-md mt-0.5',
                        getToastConfig(toast.type).iconBg,
                    ]"
                >
                    <span class="material-symbols-outlined text-xl leading-none" style="font-variation-settings: 'FILL' 1;">
                        {{ getToastConfig(toast.type).icon }}
                    </span>
                </div>

                <!-- Text Content -->
                <div class="flex-1 pr-2 space-y-0.5 min-w-0">
                    <h4 :class="['text-xs font-black tracking-tight leading-tight', getToastConfig(toast.type).titleColor]">
                        {{ toast.title }}
                    </h4>
                    <p :class="['text-[11px] font-medium leading-relaxed break-words', getToastConfig(toast.type).messageColor]">
                        {{ toast.message }}
                    </p>
                </div>

                <!-- Close Button -->
                <button
                    type="button"
                    @click="removeToast(toast.id)"
                    class="text-neutral-500 hover:text-neutral-800 p-1 -mr-1 -mt-1 rounded-lg hover:bg-black/5 transition-colors shrink-0 cursor-pointer"
                    aria-label="Tutup notifikasi"
                >
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
@keyframes progress {
    0% {
        width: 100%;
    }
    100% {
        width: 0%;
    }
}
</style>
