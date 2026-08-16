<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

// List of active toasts: { id, type, title, message, timeout }
const toasts = ref([]);
let toastIdCounter = 0;

const addToast = (type, message, title = '') => {
    if (!message) return;

    const id = ++toastIdCounter;
    let defaultTitle = '';
    switch (type) {
        case 'success':
            defaultTitle = 'Berhasil';
            break;
        case 'warning':
            defaultTitle = 'Peringatan';
            break;
        case 'error':
            defaultTitle = 'Terjadi Kesalahan';
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
    };

    toasts.value.push(toast);

    // Auto dismiss after 5 seconds
    setTimeout(() => {
        removeToast(id);
    }, 5000);
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
        if (flash.error) {
            addToast('error', flash.error);
        }
        if (flash.warning) {
            addToast('warning', flash.warning);
        }
        if (flash.info) {
            addToast('info', flash.info);
        }
    },
    { deep: true, immediate: true }
);

// Expose global custom window event for programmatic triggers
onMounted(() => {
    window.addEventListener('sigadis:toast', (e) => {
        if (e.detail) {
            addToast(e.detail.type || 'info', e.detail.message, e.detail.title);
        }
    });
});

const getToastStyle = (type) => {
    switch (type) {
        case 'success':
            return {
                border: 'border-emerald-200',
                bg: 'bg-white',
                iconBg: 'bg-emerald-100 text-emerald-700',
                icon: 'check_circle',
                titleColor: 'text-emerald-950',
                accentColor: 'bg-emerald-500',
            };
        case 'warning':
            return {
                border: 'border-amber-200',
                bg: 'bg-white',
                iconBg: 'bg-amber-100 text-amber-800',
                icon: 'warning',
                titleColor: 'text-amber-950',
                accentColor: 'bg-amber-500',
            };
        case 'error':
            return {
                border: 'border-rose-200',
                bg: 'bg-white',
                iconBg: 'bg-rose-100 text-rose-700',
                icon: 'error',
                titleColor: 'text-rose-950',
                accentColor: 'bg-rose-500',
            };
        case 'info':
        default:
            return {
                border: 'border-blue-200',
                bg: 'bg-white',
                iconBg: 'bg-blue-100 text-blue-700',
                icon: 'info',
                titleColor: 'text-blue-950',
                accentColor: 'bg-blue-500',
            };
    }
};
</script>

<template>
    <div
        class="fixed top-20 right-4 sm:right-6 z-50 flex flex-col gap-3 max-w-sm w-[calc(100vw-2rem)] pointer-events-none antialiased"
        aria-live="polite"
    >
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-8 scale-95"
            enter-to-class="opacity-100 translate-x-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-x-0 scale-100"
            leave-to-class="opacity-0 translate-x-8 scale-95"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'pointer-events-auto rounded-2xl border shadow-xl p-4 flex items-start gap-3 relative overflow-hidden bg-white/95 backdrop-blur-md',
                    getToastStyle(toast.type).border
                ]"
            >
                <!-- Left Accent Line -->
                <div
                    :class="['absolute top-0 left-0 bottom-0 w-1.5', getToastStyle(toast.type).accentColor]"
                ></div>

                <!-- Icon -->
                <div
                    :class="[
                        'w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-2xs mt-0.5',
                        getToastStyle(toast.type).iconBg
                    ]"
                >
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">
                        {{ getToastStyle(toast.type).icon }}
                    </span>
                </div>

                <!-- Content -->
                <div class="flex-1 pr-6 space-y-0.5">
                    <h4 :class="['text-xs font-extrabold tracking-tight', getToastStyle(toast.type).titleColor]">
                        {{ toast.title }}
                    </h4>
                    <p class="text-[11px] text-[#43474E] leading-relaxed">
                        {{ toast.message }}
                    </p>
                </div>

                <!-- Close Button -->
                <button
                    type="button"
                    @click="removeToast(toast.id)"
                    class="text-neutral-400 hover:text-neutral-700 p-1 rounded-lg hover:bg-neutral-100 transition-colors shrink-0"
                    aria-label="Tutup notifikasi"
                >
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
