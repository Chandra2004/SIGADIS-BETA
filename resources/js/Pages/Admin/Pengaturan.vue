<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalBox from '@/Components/ModalBox.vue';

const props = defineProps({
    admins: {
        type: Array,
        default: () => [],
    },
    currentAdmin: {
        type: Object,
        default: () => ({ id: 0, full_name: '', email: '', institution: '' }),
    },
    systemSettings: {
        type: Object,
        default: () => ({}),
    },
});

// Profile Form State
const profileForm = useForm({
    full_name: props.currentAdmin.full_name,
    institution: props.currentAdmin.institution,
    password: '',
});

const submitProfile = () => {
    profileForm.put(route('admin.pengaturan.profile.update'), {
        onSuccess: () => {
            profileForm.password = '';
        },
    });
};

// Modal State: Create New Admin
const isCreateAdminModalOpen = ref(false);
const newAdminForm = useForm({
    full_name: '',
    email: '',
    password: '',
    institution: props.currentAdmin.institution || 'Puskesmas Sungai Raya',
});

const openCreateAdminModal = () => {
    newAdminForm.reset();
    newAdminForm.institution = props.currentAdmin.institution || 'Puskesmas Sungai Raya';
    isCreateAdminModalOpen.value = true;
};

const submitCreateAdmin = () => {
    newAdminForm.post(route('admin.pengaturan.admin.store'), {
        onSuccess: () => {
            isCreateAdminModalOpen.value = false;
            newAdminForm.reset();
        },
    });
};

// Modal State: Delete Admin
const isDeleteModalOpen = ref(false);
const adminToDelete = ref(null);
const isDeleting = ref(false);

const openDeleteModal = (admin) => {
    adminToDelete.value = admin;
    isDeleteModalOpen.value = true;
};

const submitDeleteAdmin = () => {
    if (!adminToDelete.value) return;
    isDeleting.value = true;
    router.delete(route('admin.pengaturan.admin.destroy', adminToDelete.value.id), {
        onFinish: () => {
            isDeleting.value = false;
            isDeleteModalOpen.value = false;
            adminToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Pengaturan Sistem & Kelola Admin — Admin SIGADIS" />

    <AdminLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-[#123356] text-xs font-bold border border-slate-300">
                        <span class="material-symbols-outlined text-sm">settings</span>
                        <span>Konfigurasi Global & Keamanan</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Pengaturan Sistem & Kelola Admin
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Kelola akun staf administrator, profil institusi dinas/puskesmas, dan parameter operasional eskalasi gawat darurat.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="openCreateAdminModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shadow-xs cursor-pointer active:scale-95"
                    >
                        <span class="material-symbols-outlined text-base text-[#F3AEC0]">person_add</span>
                        <span>Tambah Admin Baru</span>
                    </button>
                </div>
            </div>

            <!-- Grid 2 Kolom: Profil Admin & Parameter Sistem -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- 1. Profil Administrator & Institusi (Kiri) -->
                <div class="lg:col-span-1 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-5">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-blue-50 text-[#123356]">
                            <span class="material-symbols-outlined text-xl">account_circle</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Profil Administrator Anda</h2>
                            <p class="text-xs text-[#73777F]">Identitas dan institusi dinas yang bertugas</p>
                        </div>
                    </div>

                    <form @submit.prevent="submitProfile" class="space-y-4 pt-1">
                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold text-[#26292E]">Nama Lengkap</label>
                            <input
                                v-model="profileForm.full_name"
                                type="text"
                                required
                                class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold text-[#26292E]">Email Login</label>
                            <input
                                :value="currentAdmin.email"
                                type="email"
                                disabled
                                class="w-full p-2.5 rounded-xl border border-[#E3E2E5] bg-neutral-100 text-neutral-500 font-mono text-xs cursor-not-allowed"
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold text-[#26292E]">Institusi Pelayanan</label>
                            <input
                                v-model="profileForm.institution"
                                type="text"
                                required
                                placeholder="Contoh: Puskesmas Sungai Raya"
                                class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold text-[#26292E]">Ganti Kata Sandi (Opsional)</label>
                            <input
                                v-model="profileForm.password"
                                type="password"
                                placeholder="Kosongkan jika tidak ingin mengubah"
                                class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="profileForm.processing"
                            class="w-full py-2.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shadow-xs cursor-pointer active:scale-95 disabled:opacity-50"
                        >
                            <span v-if="profileForm.processing">Menyimpan...</span>
                            <span v-else>Simpan Perubahan Profil</span>
                        </button>
                    </form>
                </div>

                <!-- 2. Parameter Integrasi & Infrastruktur (Kanan) -->
                <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-5">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-purple-50 text-purple-700">
                            <span class="material-symbols-outlined text-xl">tune</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Status Sistem & Parameter Operasional</h2>
                            <p class="text-xs text-[#73777F]">Konfigurasi eskalasi darurat, gateway pesan, dan lingkungan server</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div class="p-4 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-1.5">
                            <div class="flex items-center justify-between text-xs text-[#73777F]">
                                <span class="font-bold">WhatsApp OTP Gateway</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            </div>
                            <div class="text-sm font-extrabold text-[#123356]">{{ systemSettings.whatsapp_gateway_status }}</div>
                            <p class="text-[10px] text-[#73777F] leading-tight">Pengiriman kode OTP & verifikasi registrasi.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-1.5">
                            <div class="flex items-center justify-between text-xs text-[#73777F]">
                                <span class="font-bold">Batas Eskalasi Darurat</span>
                                <span class="material-symbols-outlined text-sm text-blue-600">timer</span>
                            </div>
                            <div class="text-sm font-extrabold text-emerald-800">{{ systemSettings.emergency_timeout_minutes }} Menit</div>
                            <p class="text-[10px] text-[#73777F] leading-tight">Waktu otomatis eskalasi dari Primary Kader ke Secondary Kader.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-1.5">
                            <div class="flex items-center justify-between text-xs text-[#73777F]">
                                <span class="font-bold">Database Driver</span>
                                <span class="material-symbols-outlined text-sm text-purple-600">database</span>
                            </div>
                            <div class="text-sm font-extrabold font-mono text-[#123356] uppercase">{{ systemSettings.database_connection }}</div>
                            <p class="text-[10px] text-[#73777F] leading-tight">Penyimpanan lokal terenkripsi.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-1.5">
                            <div class="flex items-center justify-between text-xs text-[#73777F]">
                                <span class="font-bold">Versi Sistem</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Stable</span>
                            </div>
                            <div class="text-sm font-extrabold font-mono text-[#123356]">{{ systemSettings.app_version }}</div>
                            <p class="text-[10px] text-[#73777F] leading-tight">SIGADIS Mobile & Web Enterprise.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Tabel Daftar Akun Administrator Sistem -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#F2F3F5] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
                            <span class="material-symbols-outlined text-xl">admin_panel_settings</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Daftar Akun Administrator Sistem</h2>
                            <p class="text-xs text-[#73777F]">Staf berwenang yang memiliki hak akses penuh ke panel kontrol</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Nama Administrator</th>
                                <th class="py-3.5 px-4">Email Login</th>
                                <th class="py-3.5 px-4">Institusi Asal</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr
                                v-for="admin in admins"
                                :key="admin.id"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6 font-bold text-[#123356]">
                                    {{ admin.full_name }}
                                </td>
                                <td class="py-4 px-4 font-mono text-[#26292E]">
                                    {{ admin.email }}
                                </td>
                                <td class="py-4 px-4 text-[#73777F]">
                                    {{ admin.institution }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span
                                        v-if="admin.is_current_user"
                                        class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-[10px]"
                                    >
                                        Akun Anda Saat Ini
                                    </span>
                                    <span
                                        v-else
                                        class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 font-bold text-[10px]"
                                    >
                                        Staf Admin
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button
                                        v-if="!admin.is_current_user"
                                        type="button"
                                        @click="openDeleteModal(admin)"
                                        class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 transition-all cursor-pointer"
                                        title="Hapus Akun Admin"
                                    >
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                    <span v-else class="text-[11px] text-[#8A8D96] italic">
                                        Aktif
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 1. ModalBox Tambah Admin Baru -->
        <ModalBox
            :show="isCreateAdminModalOpen"
            type="primary"
            title="Tambah Akun Administrator Baru"
            message="Buat akun staf administrator baru untuk mengelola sistem SIGADIS."
            confirm-text="Buat Akun Admin"
            :confirm-disabled="!newAdminForm.full_name || !newAdminForm.email || newAdminForm.password.length < 8 || newAdminForm.processing"
            :loading="newAdminForm.processing"
            @close="isCreateAdminModalOpen = false"
            @cancel="isCreateAdminModalOpen = false"
            @confirm="submitCreateAdmin"
        >
            <form @submit.prevent="submitCreateAdmin" class="space-y-3.5 pt-1">
                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Nama Lengkap (Wajib)</label>
                    <input
                        v-model="newAdminForm.full_name"
                        type="text"
                        required
                        placeholder="Contoh: dr. Hendra Setiawan"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Email Login (Wajib)</label>
                    <input
                        v-model="newAdminForm.email"
                        type="email"
                        required
                        placeholder="Contoh: hendra@puskesmas.go.id"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-mono focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Kata Sandi (Min 8 Karakter)</label>
                    <input
                        v-model="newAdminForm.password"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Institusi Asal</label>
                    <input
                        v-model="newAdminForm.institution"
                        type="text"
                        placeholder="Contoh: Dinas Kesehatan Kabupaten Kubu Raya"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>
            </form>
        </ModalBox>

        <!-- 2. ModalBox Hapus Admin -->
        <ModalBox
            :show="isDeleteModalOpen"
            type="danger"
            title="Hapus Akun Administrator"
            :message="`Apakah Anda yakin ingin mencabut hak akses dan menghapus akun administrator ${adminToDelete?.full_name} (${adminToDelete?.email})?`"
            confirm-text="Ya, Hapus Akun"
            :loading="isDeleting"
            @close="isDeleteModalOpen = false"
            @cancel="isDeleteModalOpen = false"
            @confirm="submitDeleteAdmin"
        />
    </AdminLayout>
</template>
