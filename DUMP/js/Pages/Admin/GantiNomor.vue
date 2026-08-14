<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from '@/Components/Shared/Icon.vue';

const props = defineProps({
    query: { type: String, default: '' },
    results: { type: Array, required: true },
    recentOverrides: { type: Array, required: true },
});

const page = usePage();
const searchTerm = ref(props.query);
const selectedUser = ref(null);

const form = useForm({
    new_phone_number: '',
    reason: '',
});

function search() {
    router.get(route('admin.ganti-nomor.index'), { q: searchTerm.value }, { preserveState: true });
}

function selectUser(user) {
    selectedUser.value = user;
    form.reset();
}

function submit() {
    form.post(route('admin.ganti-nomor.store', selectedUser.value.id), {
        onSuccess: () => (selectedUser.value = null),
    });
}
</script>

<template>
    <Head title="Ganti Nomor HP — Pemulihan Akses" />

    <div class="min-h-screen bg-neutral-100">
        <header class="flex items-center justify-between border-b border-neutral-200 bg-white px-6 py-4">
            <h1 class="text-lg font-bold text-brand-navy-900">Ganti Nomor HP (Pemulihan Akses)</h1>
            <a :href="route('admin.verifikasi.index')" class="text-sm text-brand-navy-700 underline">Kembali</a>
        </header>

        <main class="mx-auto max-w-2xl space-y-8 px-6 py-8">
            <p v-if="page.props.flash?.success" class="rounded-lg bg-risk-low-bg p-3 text-sm text-risk-low">
                {{ page.props.flash.success }}
            </p>
            <p class="text-sm text-neutral-600">
                Dipakai hanya setelah verifikasi identitas manual (tatap muka/dokumen) di luar sistem, mis. lewat
                bidan pendamping atau puskesmas. Semua override tercatat untuk audit.
            </p>

            <section class="space-y-3">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <Icon name="user" size="h-4 w-4" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-neutral-400" />
                        <input
                            v-model="searchTerm"
                            type="text"
                            placeholder="Cari nama atau nomor HP lama..."
                            class="input input-bordered w-full pl-9"
                            @keyup.enter="search"
                        />
                    </div>
                    <button type="button" class="btn cursor-pointer border-none bg-brand-navy-900 text-white" @click="search">Cari</button>
                </div>

                <div v-if="results.length" class="space-y-2">
                    <button
                        v-for="user in results"
                        :key="user.id"
                        type="button"
                        class="flex w-full cursor-pointer items-center justify-between rounded-lg border border-neutral-200 bg-white p-3 text-left text-sm transition-colors hover:border-brand-navy-100 hover:bg-neutral-50"
                        @click="selectUser(user)"
                    >
                        <span class="flex items-center gap-2 font-medium text-neutral-900">
                            <Icon name="user" size="h-4 w-4" class="text-neutral-400" /> {{ user.full_name }}
                        </span>
                        <span class="text-neutral-500">{{ user.phone_number }}</span>
                    </button>
                </div>
                <p v-else-if="query" class="text-sm text-neutral-500">Tidak ada hasil untuk "{{ query }}".</p>
            </section>

            <section v-if="selectedUser" class="space-y-3 rounded-lg border border-neutral-200 bg-white p-4">
                <p class="text-sm text-neutral-700">
                    Ubah nomor HP untuk <span class="font-semibold">{{ selectedUser.full_name }}</span>
                    (nomor lama: {{ selectedUser.phone_number }})
                </p>

                <div>
                    <label class="text-sm text-neutral-700">Nomor HP Baru</label>
                    <input v-model="form.new_phone_number" type="text" class="input input-bordered w-full" />
                    <p v-if="form.errors.new_phone_number" class="mt-1 text-xs text-risk-high">{{ form.errors.new_phone_number }}</p>
                </div>

                <div>
                    <label class="text-sm text-neutral-700">Alasan & Bukti Verifikasi</label>
                    <textarea v-model="form.reason" rows="3" class="textarea textarea-bordered w-full" placeholder="Mis. verifikasi tatap muka via bidan pendamping, KTP dicocokkan, tanggal..."></textarea>
                    <p v-if="form.errors.reason" class="mt-1 text-xs text-risk-high">{{ form.errors.reason }}</p>
                </div>

                <div class="flex gap-2">
                    <button type="button" class="btn btn-ghost flex-1" @click="selectedUser = null">Batal</button>
                    <button
                        type="button"
                        class="btn flex-1 border-none bg-brand-navy-900 text-white"
                        :disabled="!form.new_phone_number || form.reason.length < 10 || form.processing"
                        @click="submit"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </section>

            <section class="space-y-3">
                <h2 class="text-sm font-semibold text-neutral-700 uppercase">Override Terakhir</h2>
                <p v-if="recentOverrides.length === 0" class="text-sm text-neutral-500">Belum ada.</p>
                <div v-for="log in recentOverrides" :key="log.id" class="rounded-lg border border-neutral-200 bg-white p-3 text-sm">
                    <p class="font-medium text-neutral-900">{{ log.mother_name }}</p>
                    <p class="text-neutral-600">{{ log.old_phone_number }} &rarr; {{ log.new_phone_number }}</p>
                    <p class="mt-1 text-xs text-neutral-500">{{ log.reason }}</p>
                </div>
            </section>
        </main>
    </div>
</template>
