<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Icon from '@/Components/Shared/Icon.vue';
import TaskHeader from '@/Components/Shared/TaskHeader.vue';

const props = defineProps({
    settings: { type: Object, required: true },
    profilePhotoUrl: { type: String, default: null },
});

const page = usePage();

const form = useForm({
    text_size: props.settings.text_size,
    tts_enabled: props.settings.tts_enabled,
    screening_reminder_enabled: props.settings.screening_reminder_enabled,
});

function save() {
    form.post(route('kehamilan.pengaturan.update'), { preserveScroll: true });
}

// Simpan otomatis tiap ganti nilai, tanpa tombol simpan terpisah.
watch(() => ({ ...form.data() }), save, { deep: true });

const photoInput = ref(null);
const uploadingPhoto = ref(false);

function pickPhoto() {
    photoInput.value?.click();
}

function onPhotoSelected(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    uploadingPhoto.value = true;
    router.post(route('kehamilan.pengaturan.foto.update'), { photo: file }, {
        preserveScroll: true,
        forceFormData: true,
        onFinish: () => {
            uploadingPhoto.value = false;
            event.target.value = '';
        },
    });
}

function removePhoto() {
    router.delete(route('kehamilan.pengaturan.foto.destroy'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Pengaturan Aplikasi" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TaskHeader title="Pengaturan Aplikasi" :back-href="route('kehamilan.beranda')" :show-close="false" />

        <div class="mx-auto w-full max-w-md flex-1 px-6 py-6">
            <p class="mb-6 text-sm text-brand-navy-700">
                Aplikasi ini selalu menggunakan Bahasa Indonesia yang sederhana, tanpa istilah medis yang berat.
            </p>

            <p v-if="page.props.flash?.success" class="mb-4 rounded-lg bg-risk-low-bg p-3 text-sm text-risk-low">
                {{ page.props.flash.success }}
            </p>

            <section class="mb-4 flex items-center gap-4 rounded-xl bg-white p-4 shadow-sm">
                <div class="relative h-16 w-16 shrink-0">
                    <img
                        v-if="profilePhotoUrl"
                        :src="profilePhotoUrl"
                        alt="Foto profil"
                        class="h-16 w-16 rounded-full object-cover"
                    />
                    <div v-else class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-navy-100">
                        <Icon name="user" size="h-8 w-8" class="text-brand-navy-700" />
                    </div>
                </div>
                <div class="flex-1">
                    <p class="mb-1 text-sm font-medium text-brand-navy-900">Foto Profil</p>
                    <div class="flex gap-3">
                        <button type="button" class="text-xs font-semibold text-brand-navy-900 underline" :disabled="uploadingPhoto" @click="pickPhoto">
                            {{ uploadingPhoto ? 'Mengunggah...' : (profilePhotoUrl ? 'Ganti Foto' : 'Unggah Foto') }}
                        </button>
                        <button v-if="profilePhotoUrl" type="button" class="text-xs font-semibold text-risk-high underline" @click="removePhoto">
                            Hapus
                        </button>
                    </div>
                    <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="onPhotoSelected" />
                </div>
            </section>

            <section class="mb-4 rounded-xl bg-white p-4 shadow-sm">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-bold text-brand-navy-900">
                    <Icon name="document" size="h-5 w-5" class="text-brand-navy-700" /> Ukuran Teks
                </h2>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="btn flex-1"
                        :class="form.text_size === 'normal' ? 'border-none bg-brand-navy-900 text-white' : 'btn-outline border-brand-navy-100 text-brand-navy-700'"
                        @click="form.text_size = 'normal'"
                    >
                        Normal
                    </button>
                    <button
                        type="button"
                        class="btn flex-1 text-lg"
                        :class="form.text_size === 'besar' ? 'border-none bg-brand-navy-900 text-white' : 'btn-outline border-brand-navy-100 text-brand-navy-700'"
                        @click="form.text_size = 'besar'"
                    >
                        Besar
                    </button>
                </div>
            </section>

            <section class="rounded-xl bg-white p-2 shadow-sm">
                <div class="flex items-center justify-between p-3">
                    <div class="flex items-center gap-3">
                        <Icon name="speaker" size="h-5 w-5" class="text-brand-navy-700" />
                        <div>
                            <p class="text-sm font-medium text-brand-navy-900">Pembacaan Suara Soal (TTS)</p>
                            <p class="text-xs text-neutral-500">Pertanyaan skrining dibacakan otomatis.</p>
                        </div>
                    </div>
                    <input v-model="form.tts_enabled" type="checkbox" class="toggle shrink-0" />
                </div>

                <div class="flex items-center justify-between border-t border-neutral-100 p-3">
                    <div class="flex items-center gap-3">
                        <Icon name="clock" size="h-5 w-5" class="text-brand-navy-700" />
                        <div>
                            <p class="text-sm font-medium text-brand-navy-900">Pengingat Skrining Berkala</p>
                            <p class="text-xs text-neutral-500">Notifikasi saat waktunya skrining ulang.</p>
                        </div>
                    </div>
                    <input v-model="form.screening_reminder_enabled" type="checkbox" class="toggle shrink-0" />
                </div>
            </section>
        </div>
    </div>
</template>
