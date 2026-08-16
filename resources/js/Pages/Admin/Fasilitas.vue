<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalBox from '@/Components/ModalBox.vue';

const props = defineProps({
    facilities: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ type: 'semua', search: '' }),
    },
    metrics: {
        type: Object,
        default: () => ({ total: 0, hospitals: 0, puskesmas: 0, nicu_beds: 0, ambulance_ready: 0 }),
    },
});

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || 'semua');

const applyFilter = () => {
    router.get(
        route('admin.fasilitas.index'),
        {
            search: search.value,
            type: typeFilter.value,
        },
        { preserveState: true, replace: true }
    );
};

// Modal Form State (Create / Edit)
const isFormModalOpen = ref(false);
const isEditing = ref(false);
const selectedFacilityId = ref(null);

const form = useForm({
    name: '',
    type: 'puskesmas',
    region_code: '',
    address: '',
    phone_number: '',
    latitude: '',
    longitude: '',
    hospital_class: '',
    has_icu: false,
    has_nicu: false,
    nicu_bed_count: 0,
    ambulance_status: 'tersedia',
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedFacilityId.value = null;
    form.reset();
    form.type = 'puskesmas';
    form.ambulance_status = 'tersedia';
    isFormModalOpen.value = true;
};

const openEditModal = (facility) => {
    isEditing.value = true;
    selectedFacilityId.value = facility.id;
    form.name = facility.name;
    form.type = facility.type;
    form.region_code = facility.region_code;
    form.address = facility.address;
    form.phone_number = facility.phone_number || '';
    form.latitude = facility.latitude || '';
    form.longitude = facility.longitude || '';
    form.hospital_class = facility.hospital_class || '';
    form.has_icu = Boolean(facility.has_icu);
    form.has_nicu = Boolean(facility.has_nicu);
    form.nicu_bed_count = facility.nicu_bed_count || 0;
    form.ambulance_status = facility.ambulance_status || 'tersedia';
    isFormModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value && selectedFacilityId.value) {
        form.put(route('admin.fasilitas.update', selectedFacilityId.value), {
            onSuccess: () => {
                isFormModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.fasilitas.store'), {
            onSuccess: () => {
                isFormModalOpen.value = false;
                form.reset();
            },
        });
    }
};

// Modal State: Delete Facility
const isDeleteModalOpen = ref(false);
const facilityToDelete = ref(null);
const isDeleting = ref(false);

const openDeleteModal = (facility) => {
    facilityToDelete.value = facility;
    isDeleteModalOpen.value = true;
};

const submitDelete = () => {
    if (!facilityToDelete.value) return;
    isDeleting.value = true;
    router.delete(route('admin.fasilitas.destroy', facilityToDelete.value.id), {
        onFinish: () => {
            isDeleting.value = false;
            isDeleteModalOpen.value = false;
            facilityToDelete.value = null;
        },
    });
};

const getTypeBadge = (type) => {
    switch (type) {
        case 'rumah_sakit':
            return { label: 'Rumah Sakit (RS)', bg: 'bg-purple-100 text-purple-900 border-purple-200' };
        case 'puskesmas':
            return { label: 'Puskesmas', bg: 'bg-blue-100 text-blue-900 border-blue-200' };
        case 'klinik':
            return { label: 'Klinik Bersalin', bg: 'bg-emerald-100 text-emerald-900 border-emerald-200' };
        case 'polindes':
            return { label: 'Polindes', bg: 'bg-amber-100 text-amber-900 border-amber-200' };
        case 'pustu':
            return { label: 'Pustu', bg: 'bg-neutral-100 text-neutral-800 border-neutral-200' };
        default:
            return { label: type, bg: 'bg-neutral-100 text-neutral-800 border-neutral-200' };
    }
};

const getAmbulanceBadge = (status) => {
    switch (status) {
        case 'siaga':
            return { label: 'Siaga (Tersedia)', bg: 'bg-emerald-100 text-emerald-800' };
        case 'dalam_perjalanan':
            return { label: 'Dalam Perjalanan', bg: 'bg-amber-100 text-amber-800' };
        case 'tidak_tersedia':
        default:
            return { label: 'Tidak Tersedia', bg: 'bg-rose-100 text-rose-800' };
    }
};
</script>

<template>
    <Head title="Manajemen Fasilitas Kesehatan — Admin SIGADIS" />

    <AdminLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#123356] text-xs font-bold border border-blue-200">
                        <span class="material-symbols-outlined text-sm">local_hospital</span>
                        <span>Jaringan Faskes Rujukan</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Manajemen Fasilitas Kesehatan
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Kelola data kontak IGD, kesiapan ambulans, dan kapasitas NICU/ICU rumah sakit rujukan maternal.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shadow-xs cursor-pointer active:scale-95"
                    >
                        <span class="material-symbols-outlined text-base text-[#F3AEC0]">add_circle</span>
                        <span>Tambah Fasilitas Baru</span>
                    </button>
                </div>
            </div>

            <!-- Metrik Ringkas Kapasitas Maternal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Total Faskes</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-[#123356]">{{ metrics.total }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Unit</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">RS Rujukan / PONEK</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-purple-800">{{ metrics.hospitals }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">RS</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Puskesmas / Faskes Primer</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-blue-800">{{ metrics.puskesmas }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Puskesmas</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Tempat Tidur NICU</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-emerald-700">{{ metrics.nicu_beds }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Bed Siaga</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Ambulans Siaga</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-emerald-700">{{ metrics.ambulance_ready }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Armada</span>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white p-4 rounded-2xl border border-[#E3E2E5] shadow-xs flex flex-col md:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 text-lg">search</span>
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Cari nama fasilitas, alamat, atau nomor telepon IGD..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select
                        v-model="typeFilter"
                        @change="applyFilter"
                        class="py-2.5 px-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-medium focus:bg-white focus:outline-none"
                    >
                        <option value="semua">Semua Tipe Faskes</option>
                        <option value="puskesmas">Puskesmas</option>
                        <option value="rumah_sakit">Rumah Sakit</option>
                        <option value="klinik">Klinik Bersalin</option>
                        <option value="polindes">Polindes</option>
                        <option value="pustu">Pustu</option>
                    </select>

                    <button
                        type="button"
                        @click="applyFilter"
                        class="px-4 py-2.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all cursor-pointer"
                    >
                        Filter
                    </button>
                </div>
            </div>

            <!-- Tabel Direktori Faskes -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#F2F3F5] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
                            <span class="material-symbols-outlined text-xl">domain</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Direktori Jaringan Fasilitas Rujukan</h2>
                            <p class="text-xs text-[#73777F]">Data kontak, titik GPS, dan kapasitas neonatal/ICU</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Nama Fasilitas & Tipe</th>
                                <th class="py-3.5 px-4">Alamat & Wilayah</th>
                                <th class="py-3.5 px-4">Kontak IGD / Telepon</th>
                                <th class="py-3.5 px-4">Kapasitas Maternal</th>
                                <th class="py-3.5 px-4 text-center">Status Ambulans</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr v-if="facilities.length === 0">
                                <td colspan="6" class="py-8 text-center text-xs text-[#73777F]">
                                    Belum ada data fasilitas kesehatan yang terdaftar.
                                </td>
                            </tr>

                            <tr
                                v-for="facility in facilities"
                                :key="facility.id"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6">
                                    <div class="font-bold text-[#123356] text-sm">{{ facility.name }}</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span :class="['px-2 py-0.5 rounded-md font-extrabold text-[10px] uppercase border', getTypeBadge(facility.type).bg]">
                                            {{ getTypeBadge(facility.type).label }}
                                        </span>
                                        <span v-if="facility.hospital_class" class="text-[10px] text-[#73777F] font-bold font-mono">
                                            Kelas {{ facility.hospital_class }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 max-w-xs">
                                    <div class="text-[#26292E] line-clamp-2">{{ facility.address }}</div>
                                    <div class="text-[11px] text-[#73777F] font-mono mt-0.5">
                                        Kode: {{ facility.region_code }}
                                        <span v-if="facility.latitude && facility.longitude" class="text-blue-700">
                                            • GPS: {{ facility.latitude }}, {{ facility.longitude }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <div v-if="facility.phone_number" class="flex items-center gap-1.5 font-bold text-[#123356]">
                                        <span class="material-symbols-outlined text-sm text-emerald-600">call</span>
                                        <span class="font-mono">{{ facility.phone_number }}</span>
                                    </div>
                                    <span v-else class="text-[#8A8D96] italic text-[11px]">Tidak ada kontak</span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span
                                            v-if="facility.has_icu"
                                            class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-800 border border-blue-200 text-[10px] font-bold"
                                        >
                                            ICU Tersedia
                                        </span>
                                        <span
                                            v-if="facility.has_nicu"
                                            class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-800 border border-purple-200 text-[10px] font-bold"
                                        >
                                            NICU: {{ facility.nicu_bed_count || 0 }} Bed
                                        </span>
                                        <span
                                            v-if="!facility.has_icu && !facility.has_nicu"
                                            class="text-[11px] text-[#8A8D96] italic"
                                        >
                                            Layanan Standar
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span :class="['px-2.5 py-1 rounded-full font-bold text-[11px]', getAmbulanceBadge(facility.ambulance_status).bg]">
                                        {{ getAmbulanceBadge(facility.ambulance_status).label }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            type="button"
                                            @click="openEditModal(facility)"
                                            class="p-2 rounded-xl text-blue-600 hover:bg-blue-50 transition-all cursor-pointer"
                                            title="Edit Fasilitas"
                                        >
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="openDeleteModal(facility)"
                                            class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 transition-all cursor-pointer"
                                            title="Hapus Fasilitas"
                                        >
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 1. ModalBox Form Tambah / Edit Fasilitas -->
        <ModalBox
            :show="isFormModalOpen"
            type="primary"
            :title="isEditing ? 'Edit Data Fasilitas Kesehatan' : 'Tambah Fasilitas Kesehatan Baru'"
            :message="isEditing ? 'Perbarui informasi kontak, titik lokasi GPS, dan kapasitas layanan maternal.' : 'Masukkan data faskes baru untuk melengkapi direktori rujukan darurat.'"
            :confirm-text="isEditing ? 'Simpan Perubahan' : 'Tambah Fasilitas'"
            :confirm-disabled="!form.name || !form.address || !form.region_code || form.processing"
            :loading="form.processing"
            @close="isFormModalOpen = false"
            @cancel="isFormModalOpen = false"
            @confirm="submitForm"
        >
            <form @submit.prevent="submitForm" class="space-y-3.5 pt-1 max-h-[60vh] overflow-y-auto pr-1">
                <!-- Nama & Tipe -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[11px] font-extrabold text-[#26292E]">Nama Fasilitas (Wajib)</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Contoh: Puskesmas Sungai Raya"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[11px] font-extrabold text-[#26292E]">Tipe Fasilitas</label>
                        <select
                            v-model="form.type"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-medium focus:bg-white focus:border-[#123356] focus:outline-none"
                        >
                            <option value="puskesmas">Puskesmas</option>
                            <option value="rumah_sakit">Rumah Sakit</option>
                            <option value="klinik">Klinik Bersalin</option>
                            <option value="polindes">Polindes</option>
                            <option value="pustu">Pustu</option>
                        </select>
                    </div>
                </div>

                <!-- Wilayah & Nomor Telepon -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[11px] font-extrabold text-[#26292E]">Kode Wilayah (Wajib)</label>
                        <input
                            v-model="form.region_code"
                            type="text"
                            required
                            placeholder="Contoh: 33.08.05.2001"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-mono focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[11px] font-extrabold text-[#26292E]">Kontak Telepon / IGD</label>
                        <input
                            v-model="form.phone_number"
                            type="text"
                            placeholder="Contoh: 0561-711234"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-mono focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Alamat Lengkap -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-extrabold text-[#26292E]">Alamat Lengkap (Wajib)</label>
                    <textarea
                        v-model="form.address"
                        rows="2"
                        required
                        placeholder="Alamat jalan, kelurahan, dan patokan lokasi"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    ></textarea>
                </div>

                <!-- Koordinat GPS -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[11px] font-extrabold text-[#26292E]">Latitude</label>
                        <input
                            v-model="form.latitude"
                            type="number"
                            step="any"
                            placeholder="-0.0833"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-mono focus:bg-white focus:outline-none"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[11px] font-extrabold text-[#26292E]">Longitude</label>
                        <input
                            v-model="form.longitude"
                            type="number"
                            step="any"
                            placeholder="109.3500"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-mono focus:bg-white focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Kapasitas Maternal (ICU & NICU) -->
                <div class="p-3 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-3">
                    <span class="block text-[11px] font-extrabold text-[#123356]">Kapasitas Pelayanan Maternal</span>
                    <div class="flex items-center gap-6 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.has_icu" class="rounded text-[#123356]" />
                            <span class="font-bold text-[#26292E]">Memiliki ICU</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.has_nicu" class="rounded text-[#123356]" />
                            <span class="font-bold text-[#26292E]">Memiliki NICU</span>
                        </label>
                    </div>

                    <div v-if="form.has_nicu" class="space-y-1 pt-1">
                        <label class="block text-[11px] font-bold text-[#26292E]">Jumlah Tempat Tidur NICU</label>
                        <input
                            v-model="form.nicu_bed_count"
                            type="number"
                            min="0"
                            class="w-32 p-2 rounded-xl border border-[#C3C6CF] bg-white text-xs font-bold"
                        />
                    </div>
                </div>

                <!-- Status Ambulans -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-extrabold text-[#26292E]">Status Armada Ambulans</label>
                    <select
                        v-model="form.ambulance_status"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-medium focus:bg-white focus:outline-none"
                    >
                        <option value="siaga">Siaga / Tersedia</option>
                        <option value="dalam_perjalanan">Dalam Perjalanan / Sedang Beroperasi</option>
                        <option value="tidak_tersedia">Tidak Tersedia / Dalam Perbaikan</option>
                    </select>
                </div>
            </form>
        </ModalBox>

        <!-- 2. ModalBox Hapus Fasilitas -->
        <ModalBox
            :show="isDeleteModalOpen"
            type="danger"
            title="Hapus Fasilitas Kesehatan"
            :message="`Apakah Anda yakin ingin menghapus data fasilitas ${facilityToDelete?.name} dari direktori rujukan?`"
            confirm-text="Ya, Hapus Fasilitas"
            :loading="isDeleting"
            @close="isDeleteModalOpen = false"
            @cancel="isDeleteModalOpen = false"
            @confirm="submitDelete"
        />
    </AdminLayout>
</template>
