<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalBox from '@/Components/ModalBox.vue';

const props = defineProps({
    query: {
        type: String,
        default: '',
    },
    results: {
        type: Array,
        default: () => [],
    },
    recentOverrides: {
        type: Array,
        default: () => [],
    },
    metrics: {
        type: Object,
        default: () => ({ total_mothers: 0, total_overrides: 0 }),
    },
});

const searchQuery = ref(props.query || '');

const searchMothers = () => {
    router.get(
        route('admin.ganti-nomor.index'),
        { q: searchQuery.value },
        { preserveState: true, replace: true }
    );
};

// Modal State: Override Phone Form
const isModalOpen = ref(false);
const selectedMother = ref(null);

const form = useForm({
    new_phone_number: '',
    reason: '',
});

const openOverrideModal = (mother) => {
    selectedMother.value = mother;
    form.reset();
    form.new_phone_number = '';
    form.reason = '';
    isModalOpen.value = true;
};

const submitOverride = () => {
    if (!selectedMother.value) return;

    form.post(route('admin.ganti-nomor.store', selectedMother.value.id), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            selectedMother.value = null;
        },
    });
};
</script>

<template>
    <Head title="Pemulihan Akun & Override Nomor HP — Admin SIGADIS" />

    <AdminLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#123356] text-xs font-bold border border-blue-200">
                        <span class="material-symbols-outlined text-sm">manage_accounts</span>
                        <span>Dukungan Pasien & Kepatuhan UU PDP</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Pemulihan Akun & Override Nomor HP
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Bantu pemulihan akses bagi Ibu Hamil yang kehilangan nomor HP/perangkat melalui validasi identitas fisik (Buku KIA / KTP).
                    </p>
                </div>
            </div>

            <!-- Metrik Ringkas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Total Ibu Hamil Terdaftar</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-[#123356]">{{ metrics.total_mothers }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Akun</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Total Override Dilakukan</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-blue-800">{{ metrics.total_overrides }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Kasus</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Kepatuhan Rekam Audit</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-emerald-700">100%</span>
                        <span class="text-xs font-semibold text-emerald-700 font-bold">UU PDP Compliant</span>
                    </div>
                </div>
            </div>

            <!-- 1. Form Pencarian & Daftar Akun Ibu Hamil -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#F2F3F5] flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-base font-extrabold text-[#123356]">Pencarian Akun Ibu Hamil</h2>
                        <p class="text-xs text-[#73777F]">Cari nama atau nomor HP lama untuk melakukan pemulihan akses</p>
                    </div>

                    <div class="flex items-center gap-2 max-w-md w-full">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 text-lg">search</span>
                            <input
                                v-model="searchQuery"
                                @keyup.enter="searchMothers"
                                type="text"
                                placeholder="Ketik nama atau nomor HP..."
                                class="w-full pl-10 pr-4 py-2 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                            />
                        </div>
                        <button
                            type="button"
                            @click="searchMothers"
                            class="px-4 py-2 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all cursor-pointer shadow-xs"
                        >
                            Cari
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Nama Ibu Hamil</th>
                                <th class="py-3.5 px-4">Nomor HP Terdaftar</th>
                                <th class="py-3.5 px-4">Wilayah</th>
                                <th class="py-3.5 px-4 text-center">Status Kehamilan</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr v-if="results.length === 0">
                                <td colspan="5" class="py-8 text-center text-xs text-[#73777F]">
                                    <span class="material-symbols-outlined text-3xl text-neutral-400 block mb-1">person_search</span>
                                    Tidak ada data ibu hamil yang ditemukan. Gunakan kolom pencarian di atas.
                                </td>
                            </tr>

                            <tr
                                v-for="mother in results"
                                :key="mother.id"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6 font-bold text-[#123356]">
                                    {{ mother.full_name }}
                                </td>
                                <td class="py-4 px-4 font-mono text-[#26292E] font-bold">
                                    {{ mother.phone_number }}
                                </td>
                                <td class="py-4 px-4 text-[#73777F] font-mono text-[11px]">
                                    {{ mother.current_pregnancy?.region_code || '-' }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase',
                                            mother.current_pregnancy?.status === 'hamil' ? 'bg-blue-100 text-blue-800' : (mother.current_pregnancy?.status === 'nifas' ? 'bg-purple-100 text-purple-800' : 'bg-neutral-100 text-neutral-700')
                                        ]"
                                    >
                                        {{ mother.current_pregnancy?.status || 'Belum Registrasi' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button
                                        type="button"
                                        @click="openOverrideModal(mother)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition-all shadow-xs cursor-pointer active:scale-95"
                                    >
                                        <span class="material-symbols-outlined text-sm">phone_iphone</span>
                                        <span>Ganti Nomor HP</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. Tabel Log Audit Override (Kepatuhan UU PDP) -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#F2F3F5] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
                            <span class="material-symbols-outlined text-xl">history_edu</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Rekam Jejak Audit Override (Log UU PDP)</h2>
                            <p class="text-xs text-[#73777F]">Catatan permanen pengubahan data medis untuk pencegahan penyalahgunaan akun</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Waktu Eksekusi</th>
                                <th class="py-3.5 px-4">Nama Ibu</th>
                                <th class="py-3.5 px-4">Nomor Lama &rarr; Nomor Baru</th>
                                <th class="py-3.5 px-4">Alasan & Bukti Verifikasi</th>
                                <th class="py-3.5 px-6 text-right">Admin Pelaksana</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr v-if="recentOverrides.length === 0">
                                <td colspan="5" class="py-8 text-center text-xs text-[#73777F]">
                                    Belum ada rekam jejak override nomor HP.
                                </td>
                            </tr>

                            <tr
                                v-for="log in recentOverrides"
                                :key="log.id"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6 font-mono text-[#73777F] text-[11px]">
                                    <div>{{ log.performed_date }}</div>
                                    <span class="text-[10px] text-neutral-400">({{ log.performed_at }})</span>
                                </td>
                                <td class="py-4 px-4 font-bold text-[#123356]">
                                    {{ log.mother_name }}
                                </td>
                                <td class="py-4 px-4 font-mono text-xs">
                                    <span class="text-rose-600 line-through mr-1">{{ log.old_phone_number }}</span>
                                    <span class="text-neutral-400">&rarr;</span>
                                    <span class="text-emerald-700 font-bold ml-1">{{ log.new_phone_number }}</span>
                                </td>
                                <td class="py-4 px-4 max-w-xs text-[#43474E] italic">
                                    "{{ log.reason }}"
                                </td>
                                <td class="py-4 px-6 text-right font-bold text-[#123356]">
                                    {{ log.admin_name }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ModalBox Form Override Nomor HP -->
        <ModalBox
            :show="isModalOpen"
            type="primary"
            title="Override Nomor HP Ibu Hamil"
            :message="`Masukkan nomor telepon baru untuk ${selectedMother?.full_name}. Penggantian nomor akan dicatat permanen dalam audit log.`"
            confirm-text="Simpan Perubahan Nomor"
            :confirm-disabled="!form.new_phone_number || form.reason.length < 10 || form.processing"
            :loading="form.processing"
            @close="isModalOpen = false"
            @cancel="isModalOpen = false"
            @confirm="submitOverride"
        >
            <form @submit.prevent="submitOverride" class="space-y-4 pt-1">
                <!-- Info Nomor Lama -->
                <div class="p-3 rounded-2xl bg-neutral-100 border border-neutral-200 text-xs flex items-center justify-between">
                    <span class="text-[#73777F]">Nomor HP Terdaftar Saat Ini:</span>
                    <span class="font-mono font-bold text-[#123356]">{{ selectedMother?.phone_number }}</span>
                </div>

                <!-- Input Nomor Baru -->
                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Nomor HP Baru (Wajib)</label>
                    <input
                        v-model="form.new_phone_number"
                        type="text"
                        required
                        placeholder="Contoh: 081299998888"
                        class="w-full p-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-mono font-bold focus:bg-white focus:border-blue-600 focus:outline-none"
                    />
                    <span v-if="form.errors.new_phone_number" class="text-[11px] text-rose-600 block">
                        {{ form.errors.new_phone_number }}
                    </span>
                </div>

                <!-- Input Alasan / Catatan Fisik (Wajib UU PDP) -->
                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">
                        Alasan & Bukti Verifikasi Identitas Fisik (Wajib, min 10 karakter)
                    </label>
                    <textarea
                        v-model="form.reason"
                        rows="3"
                        required
                        placeholder="Contoh: Ibu hadir tatap muka di Puskesmas, identitas diverifikasi sesuai KTP dan Buku KIA asli."
                        class="w-full p-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-blue-600 focus:outline-none"
                    ></textarea>
                    <span v-if="form.errors.reason" class="text-[11px] text-rose-600 block">
                        {{ form.errors.reason }}
                    </span>
                </div>

                <!-- Peringatan Kepatuhan Regulasi -->
                <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] flex items-start gap-2">
                    <span class="material-symbols-outlined text-sm text-amber-700 mt-0.5">verified_user</span>
                    <span>
                        Data rekam audit ini bersifat permanen dan tidak dapat dihapus demi memenuhi ketentuan perlindungan data pasien.
                    </span>
                </div>
            </form>
        </ModalBox>
    </AdminLayout>
</template>
