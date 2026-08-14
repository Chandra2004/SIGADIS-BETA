<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppShell from '@/Components/Desktop/AppShell.vue';
import Icon from '@/Components/Shared/Icon.vue';

const props = defineProps({
    pregnancy: { type: Object, required: true },
    screeningSessions: { type: Array, required: true },
    referrals: { type: Array, required: true },
    clinicalVisits: { type: Array, required: true },
    postpartumAssessment: { type: Object, default: null },
    caseStatus: { type: Object, required: true },
    canManage: { type: Boolean, required: true },
    canCancelNifas: { type: Boolean, required: true },
});

const page = usePage();
const deliveredAt = ref(new Date().toISOString().slice(0, 10));
const deliveryNotes = ref('');
const showDeliveredModal = ref(false);
const showCloseModal = ref(false);
const showEditDate = ref(false);
const showAddVisit = ref(false);
const editedDeliveredAt = ref(props.pregnancy.nifas_started_at?.slice(0, 10) ?? '');

function markDelivered() {
    router.post(route('bidan.patients.mark-delivered', props.pregnancy.id), {
        delivered_at: deliveredAt.value,
        delivery_notes: deliveryNotes.value || null,
    });
    showDeliveredModal.value = false;
}

function cancelNifas() {
    router.post(route('bidan.patients.cancel-nifas', props.pregnancy.id));
}

function editDeliveryDate() {
    router.post(route('bidan.patients.edit-delivery-date', props.pregnancy.id), { delivered_at: editedDeliveredAt.value });
    showEditDate.value = false;
}

const visitForm = useForm({
    visit_type: 'routine_screening',
    status_tag: 'normal',
    blood_pressure_systolic: '',
    blood_pressure_diastolic: '',
    symptoms: '',
    clinical_notes: '',
});

function addVisit() {
    visitForm
        .transform((data) => ({
            ...data,
            blood_pressure_systolic: data.blood_pressure_systolic || null,
            blood_pressure_diastolic: data.blood_pressure_diastolic || null,
            symptoms: data.symptoms ? data.symptoms.split(',').map((s) => s.trim()).filter(Boolean) : [],
        }))
        .post(route('bidan.patients.clinical-visits.store', props.pregnancy.id), {
            onSuccess: () => {
                visitForm.reset();
                showAddVisit.value = false;
            },
        });
}

const closeForm = useForm({
    confirmed: false,
    physical_recovery_status: 'complete',
    infant_growth_status: 'on_target',
    infant_weight_kg: '',
    family_planning_status: 'not_counseled',
    family_planning_method: '',
    next_steps: '',
    final_summary_note: '',
});

function closeCase() {
    closeForm.post(route('bidan.patients.close-case', props.pregnancy.id), {
        onSuccess: () => (showCloseModal.value = false),
    });
}

function logout() {
    router.post(route('auth.staff.logout'));
}

const visitTypeLabel = { routine_screening: 'Skrining Rutin', follow_up: 'Kunjungan Lanjutan', other: 'Lainnya' };
const statusTagLabel = { normal: 'Normal', monitor: 'Perlu Dipantau', elevated: 'Meningkat' };
const statusTagClass = {
    normal: 'bg-risk-low-bg text-risk-low',
    monitor: 'bg-risk-medium-bg text-risk-medium',
    elevated: 'bg-risk-high-bg text-risk-high',
};
const statusTagDot = { normal: 'bg-risk-low', monitor: 'bg-risk-medium', elevated: 'bg-risk-high' };
const riskDot = { tinggi: 'bg-risk-high', sedang: 'bg-risk-medium', rendah: 'bg-risk-low' };

/** Riwayat gabungan (Figma "Pregnancy & Postpartum History") -- gabungan kunjungan klinis + sesi skrining, urut waktu, tanpa data baru. */
const timeline = computed(() => {
    const visits = props.clinicalVisits.map((v) => ({
        key: `visit-${v.id}`,
        at: v.visited_at,
        dot: statusTagDot[v.status_tag] ?? 'bg-neutral-300',
        title: visitTypeLabel[v.visit_type] ?? v.visit_type,
        badge: statusTagLabel[v.status_tag],
        badgeClass: statusTagClass[v.status_tag],
        body: [
            v.blood_pressure_systolic ? `Tensi: ${v.blood_pressure_systolic}/${v.blood_pressure_diastolic}` : null,
            v.symptoms?.length ? `Keluhan: ${v.symptoms.join(', ')}` : null,
            v.clinical_notes,
        ].filter(Boolean).join(' — '),
        by: v.midwife?.full_name,
    }));

    const sessions = props.screeningSessions.map((s) => ({
        key: `session-${s.id}`,
        at: s.started_at,
        dot: s.risk_assessment ? (riskDot[s.risk_assessment.risk_level] ?? 'bg-neutral-300') : 'bg-neutral-300',
        title: `Skrining ${s.session_type}`,
        badge: s.risk_assessment ? `Risiko ${s.risk_assessment.risk_level}` : 'Belum ada hasil',
        badgeClass: s.risk_assessment ? (riskDot[s.risk_assessment.risk_level] === 'bg-risk-high' ? 'bg-risk-high-bg text-risk-high' : riskDot[s.risk_assessment.risk_level] === 'bg-risk-medium' ? 'bg-risk-medium-bg text-risk-medium' : 'bg-risk-low-bg text-risk-low') : 'bg-neutral-100 text-neutral-600',
        body: s.risk_assessment?.recommendation_text ?? '',
        by: null,
    }));

    return [...visits, ...sessions].sort((a, b) => new Date(b.at) - new Date(a.at));
});
</script>

<template>
    <Head :title="pregnancy.mother_name" />

    <AppShell @logout="logout">
        <div class="mx-auto max-w-xl space-y-6 px-6 py-8">
            <a :href="route('bidan.dashboard')" class="text-sm text-brand-navy-700">&larr; Kembali ke Dashboard</a>

            <p v-if="page.props.flash?.success" class="rounded-lg bg-risk-low-bg p-3 text-sm text-risk-low">{{ page.props.flash.success }}</p>

            <div class="rounded-xl border border-neutral-200 bg-white p-6">
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-2xl font-bold text-neutral-900">{{ pregnancy.mother_name }}</p>
                    <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold text-neutral-700 capitalize">
                        {{ pregnancy.status.replace('_', ' ') }}
                    </span>
                </div>
                <p class="text-sm text-neutral-500">{{ pregnancy.gestational_age_weeks }} minggu kehamilan saat registrasi</p>

                <p v-if="pregnancy.status === 'nifas'" class="mt-2 text-sm text-neutral-700">
                    Masa nifas dimulai {{ new Date(pregnancy.nifas_started_at).toLocaleDateString('id-ID') }}
                    <span v-if="caseStatus.nifas_day">&middot; Hari ke-{{ caseStatus.nifas_day }} dari 42</span>
                </p>
                <p v-if="pregnancy.delivery_notes" class="mt-1 text-sm text-neutral-500">Catatan persalinan: {{ pregnancy.delivery_notes }}</p>
                <p v-if="pregnancy.status === 'case_closed'" class="mt-2 text-sm text-neutral-700">
                    Kasus ditutup {{ new Date(pregnancy.case_closed_at).toLocaleDateString('id-ID') }}
                </p>

                <div v-if="pregnancy.address || pregnancy.emergency_contact_name" class="mt-3 grid grid-cols-2 gap-3 border-t border-neutral-100 pt-3 text-sm">
                    <div v-if="pregnancy.address" class="col-span-2">
                        <p class="text-neutral-500">Alamat</p>
                        <p class="font-medium text-neutral-900">{{ pregnancy.address }}</p>
                    </div>
                    <div v-if="pregnancy.emergency_contact_name">
                        <p class="text-neutral-500">Kontak Darurat</p>
                        <p class="font-medium text-neutral-900">{{ pregnancy.emergency_contact_name }}</p>
                    </div>
                    <div v-if="pregnancy.emergency_contact_phone">
                        <p class="text-neutral-500">No. HP</p>
                        <a :href="`tel:${pregnancy.emergency_contact_phone}`" class="font-medium text-brand-navy-900 underline">{{ pregnancy.emergency_contact_phone }}</a>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-3 border-t border-neutral-100 pt-3 text-sm">
                    <div>
                        <p class="text-neutral-500">Total Kunjungan/Skrining</p>
                        <p class="font-medium text-neutral-900">{{ caseStatus.total_visits }}</p>
                    </div>
                    <div>
                        <p class="text-neutral-500">Bidan Utama</p>
                        <p class="font-medium text-neutral-900">{{ caseStatus.primary_midwife ?? '-' }}</p>
                    </div>
                </div>

                <div v-if="canManage && pregnancy.status === 'hamil'" class="mt-6">
                    <button type="button" class="btn w-full border-none bg-brand-navy-900 text-white" @click="showDeliveredModal = true">
                        Tandai Telah Bersalin
                    </button>
                </div>

                <div v-if="canCancelNifas" class="mt-3 flex gap-2">
                    <button type="button" class="btn btn-outline flex-1 border-neutral-300 text-neutral-700" @click="showEditDate = true">
                        Ubah Tanggal Persalinan
                    </button>
                    <button type="button" class="btn btn-outline flex-1 border-neutral-300 text-neutral-700" @click="cancelNifas">
                        Batalkan Status Nifas
                    </button>
                </div>
                <div v-if="showEditDate" class="mt-3 space-y-2 rounded-lg border border-neutral-200 p-3">
                    <label class="text-sm text-neutral-700">Tanggal persalinan yang benar</label>
                    <input v-model="editedDeliveredAt" type="date" class="input input-bordered w-full" :max="new Date().toISOString().slice(0, 10)" />
                    <div class="flex gap-2 pt-1">
                        <button type="button" class="btn btn-ghost flex-1" @click="showEditDate = false">Batal</button>
                        <button type="button" class="btn flex-1 border-none bg-brand-navy-900 text-white" @click="editDeliveryDate">
                            Simpan
                        </button>
                    </div>
                </div>

                <div v-if="canManage && pregnancy.status !== 'case_closed'" class="mt-3">
                    <button type="button" class="btn btn-outline w-full border-neutral-300 text-neutral-700" @click="showCloseModal = true">
                        Konfirmasi Case Closed
                    </button>
                </div>
            </div>

            <div v-if="postpartumAssessment" class="rounded-lg border border-neutral-200 bg-white p-4 text-sm">
                <h2 class="mb-3 text-sm font-semibold text-neutral-700 uppercase">Final Midwife Assessment</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-neutral-500">Pemulihan Fisik</p>
                        <p class="font-medium text-neutral-900">{{ postpartumAssessment.physical_recovery_status === 'complete' ? 'Selesai' : 'Perlu Tindak Lanjut' }}</p>
                    </div>
                    <div>
                        <p class="text-neutral-500">Tumbuh Kembang Bayi</p>
                        <p class="font-medium text-neutral-900">
                            {{ postpartumAssessment.infant_growth_status === 'on_target' ? 'Sesuai Target' : 'Perlu Dipantau' }}
                            <span v-if="postpartumAssessment.infant_weight_kg">({{ postpartumAssessment.infant_weight_kg }} kg)</span>
                        </p>
                    </div>
                </div>
                <p v-if="postpartumAssessment.next_steps" class="mt-2 text-neutral-700">Langkah selanjutnya: {{ postpartumAssessment.next_steps }}</p>
                <p v-if="postpartumAssessment.final_summary_note" class="mt-2 text-neutral-500 italic">"{{ postpartumAssessment.final_summary_note }}"</p>
            </div>

            <!-- Figma "Pregnancy & Postpartum History": gabungan kunjungan + skrining urut waktu, dari data yang sudah ada. -->
            <section class="rounded-lg border border-neutral-200 bg-white p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-neutral-700 uppercase">Riwayat Kehamilan &amp; Nifas</h2>
                    <button v-if="canManage" type="button" class="text-xs font-semibold text-brand-navy-900 underline" @click="showAddVisit = !showAddVisit">
                        + Tambah Catatan
                    </button>
                </div>

                <div v-if="showAddVisit" class="mb-4 space-y-2 rounded-lg border border-neutral-200 p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <select v-model="visitForm.visit_type" class="select select-bordered select-sm w-full">
                            <option value="routine_screening">Skrining Rutin</option>
                            <option value="follow_up">Kunjungan Lanjutan</option>
                            <option value="other">Lainnya</option>
                        </select>
                        <select v-model="visitForm.status_tag" class="select select-bordered select-sm w-full">
                            <option value="normal">Normal</option>
                            <option value="monitor">Perlu Dipantau</option>
                            <option value="elevated">Meningkat</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input v-model="visitForm.blood_pressure_systolic" type="number" placeholder="Sistolik" class="input input-bordered input-sm w-full" />
                        <input v-model="visitForm.blood_pressure_diastolic" type="number" placeholder="Diastolik" class="input input-bordered input-sm w-full" />
                    </div>
                    <input v-model="visitForm.symptoms" type="text" placeholder="Keluhan, pisahkan dengan koma" class="input input-bordered input-sm w-full" />
                    <textarea v-model="visitForm.clinical_notes" rows="2" placeholder="Catatan klinis" class="textarea textarea-bordered textarea-sm w-full"></textarea>
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-ghost btn-sm flex-1" @click="showAddVisit = false">Batal</button>
                        <button type="button" class="btn btn-sm flex-1 border-none bg-brand-navy-900 text-white" :disabled="visitForm.processing" @click="addVisit">
                            Simpan
                        </button>
                    </div>
                </div>

                <p v-if="timeline.length === 0" class="text-sm text-neutral-500">Belum ada riwayat.</p>
                <div v-else class="space-y-3">
                    <div v-for="item in timeline" :key="item.key" class="relative border-b border-neutral-100 pb-3 pl-4 text-sm last:border-0">
                        <span class="absolute top-1.5 left-0 h-2.5 w-2.5 -translate-x-1/2 rounded-full" :class="item.dot" />
                        <div class="mb-1 flex items-center justify-between">
                            <span class="font-medium text-neutral-900">{{ item.title }}</span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="item.badgeClass">{{ item.badge }}</span>
                        </div>
                        <p class="text-neutral-500">
                            {{ new Date(item.at).toLocaleDateString('id-ID') }}<span v-if="item.by"> &middot; {{ item.by }}</span>
                        </p>
                        <p v-if="item.body" class="mt-1 text-neutral-700">{{ item.body }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-neutral-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold text-neutral-700 uppercase">Rujukan</h2>
                <p v-if="referrals.length === 0" class="text-sm text-neutral-500">Belum ada rujukan sebelumnya.</p>
                <ul v-else class="space-y-2">
                    <li v-for="r in referrals" :key="r.id" class="border-b border-neutral-100 pb-2 text-sm last:border-0">
                        {{ r.facility.name }} &middot; {{ new Date(r.referred_at).toLocaleDateString('id-ID') }}
                    </li>
                </ul>
            </section>

            <a :href="route('bidan.patients.export-history', pregnancy.id)" class="btn btn-outline w-full border-neutral-300 text-neutral-700">
                Unduh Riwayat (PDF)
            </a>
        </div>

        <!-- Modal: Tandai Telah Bersalin -->
        <div v-if="showDeliveredModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-6">
            <div class="w-full max-w-md rounded-xl bg-white shadow-lg">
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4">
                    <h2 class="text-lg font-bold text-brand-navy-900">Tandai Telah Bersalin</h2>
                    <button type="button" aria-label="Tutup" class="cursor-pointer text-neutral-400" @click="showDeliveredModal = false">
                        <Icon name="x" size="h-5 w-5" />
                    </button>
                </div>
                <div class="space-y-3 px-6 py-4">
                    <div>
                        <label class="text-sm text-neutral-700">Tanggal Persalinan</label>
                        <input v-model="deliveredAt" type="date" class="input input-bordered w-full" :max="new Date().toISOString().slice(0, 10)" />
                    </div>
                    <div>
                        <label class="text-sm text-neutral-700">Catatan Persalinan (opsional)</label>
                        <textarea v-model="deliveryNotes" rows="2" class="textarea textarea-bordered w-full" placeholder="Kondisi ibu, bayi, atau kejadian penting saat persalinan..."></textarea>
                    </div>
                    <p class="rounded-lg bg-neutral-50 p-3 text-xs text-neutral-600">
                        Setelah ditandai bersalin, pasien akan otomatis dipindahkan ke fase Nifas untuk pemantauan
                        selama 42 hari ke depan.
                    </p>
                </div>
                <div class="flex justify-end gap-2 border-t border-neutral-200 px-6 py-4">
                    <button type="button" class="btn btn-outline" @click="showDeliveredModal = false">Batal</button>
                    <button type="button" class="btn border-none bg-brand-navy-900 text-white" @click="markDelivered">
                        Tandai Telah Bersalin &amp; Transisi ke Nifas
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: Final Midwife Assessment / Close Case -->
        <div v-if="showCloseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-6 py-8">
            <div class="max-h-full w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-lg">
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4">
                    <h2 class="text-lg font-bold text-brand-navy-900">Final Midwife Assessment</h2>
                    <button type="button" aria-label="Tutup" class="cursor-pointer text-neutral-400" @click="showCloseModal = false">
                        <Icon name="x" size="h-5 w-5" />
                    </button>
                </div>
                <div class="space-y-3 px-6 py-4">
                    <div>
                        <label class="text-sm text-neutral-700">Pemulihan Fisik</label>
                        <select v-model="closeForm.physical_recovery_status" class="select select-bordered w-full">
                            <option value="complete">Selesai</option>
                            <option value="needs_followup">Perlu Tindak Lanjut</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm text-neutral-700">Tumbuh Kembang Bayi</label>
                            <select v-model="closeForm.infant_growth_status" class="select select-bordered w-full">
                                <option value="on_target">Sesuai Target</option>
                                <option value="needs_monitoring">Perlu Dipantau</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-neutral-700">Berat Bayi (kg)</label>
                            <input v-model="closeForm.infant_weight_kg" type="number" step="0.01" class="input input-bordered w-full" />
                        </div>
                    </div>

                    <div>
                        <label class="text-sm text-neutral-700">Keluarga Berencana (KB)</label>
                        <select v-model="closeForm.family_planning_status" class="select select-bordered w-full">
                            <option value="not_counseled">Belum Dikonseling</option>
                            <option value="counseled_undecided">Sudah Dikonseling, Belum Memutuskan</option>
                            <option value="counseled_decided">Sudah Dikonseling &amp; Memutuskan</option>
                        </select>
                        <input
                            v-if="closeForm.family_planning_status === 'counseled_decided'"
                            v-model="closeForm.family_planning_method"
                            type="text"
                            placeholder="Metode KB yang dipilih"
                            class="input input-bordered mt-2 w-full"
                        />
                    </div>

                    <div>
                        <label class="text-sm text-neutral-700">Langkah Selanjutnya</label>
                        <textarea v-model="closeForm.next_steps" rows="2" class="textarea textarea-bordered w-full"></textarea>
                    </div>

                    <div>
                        <label class="text-sm text-neutral-700">Catatan Akhir (opsional)</label>
                        <textarea v-model="closeForm.final_summary_note" rows="2" class="textarea textarea-bordered w-full"></textarea>
                    </div>

                    <label class="flex cursor-pointer items-start gap-2 rounded-lg bg-neutral-50 p-3">
                        <input v-model="closeForm.confirmed" type="checkbox" class="checkbox mt-0.5" />
                        <span class="text-sm text-neutral-700">
                            Saya konfirmasi seluruh pemeriksaan nifas yang diperlukan sudah selesai dan
                            terdokumentasi sesuai protokol klinis.
                        </span>
                    </label>
                    <p v-if="closeForm.errors.confirmed" class="text-sm text-[--color-error-form]">Wajib dicentang sebelum menutup kasus.</p>

                    <p class="text-sm text-neutral-700">
                        Yakin menutup kasus {{ pregnancy.mother_name }}? Riwayat tetap tersimpan, tapi status
                        kehamilan ini akan ditandai selesai.
                    </p>
                </div>
                <div class="flex justify-end gap-2 border-t border-neutral-200 px-6 py-4">
                    <button type="button" class="btn btn-outline" @click="showCloseModal = false">Keep Case Open</button>
                    <button
                        type="button"
                        class="btn border-none bg-neutral-900 text-white"
                        :disabled="closeForm.processing"
                        @click="closeCase"
                    >
                        Konfirmasi Case Closed
                    </button>
                </div>
            </div>
        </div>
    </AppShell>
</template>
