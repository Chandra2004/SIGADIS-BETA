<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AccountDrawer from '@/Components/Shared/AccountDrawer.vue';
import BottomTabBar from '@/Components/Shared/BottomTabBar.vue';
import EmergencyButton from '@/Components/Shared/EmergencyButton.vue';
import Icon from '@/Components/Shared/Icon.vue';
import LogoutConfirmDialog from '@/Components/Shared/LogoutConfirmDialog.vue';
import NotificationBell from '@/Components/Shared/NotificationBell.vue';
import TopAppBar from '@/Components/Shared/TopAppBar.vue';

const props = defineProps({
    motherName: { type: String, required: true },
    profilePhotoUrl: { type: String, default: null },
    pregnancy: { type: Object, default: null },
    nextSessionType: { type: String, default: null },
    allPregnancies: { type: Array, default: () => [] },
});

const page = usePage();
const showLogoutConfirm = ref(false);
const showAccountMenu = ref(false);
const showSwitcher = ref(false);
const emergencyRef = ref(null);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 19) return 'Selamat Sore';
    return 'Selamat Malam';
});

const formattedDueDate = computed(() => {
    if (!props.pregnancy?.estimated_due_date) return 'Belum diketahui';
    return new Date(props.pregnancy.estimated_due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
});

const riskBadge = {
    tinggi: { label: 'Risiko Tinggi', class: 'bg-risk-high-bg text-risk-high' },
    sedang: { label: 'Risiko Sedang', class: 'bg-risk-medium-bg text-risk-medium' },
    rendah: { label: 'Risiko Rendah', class: 'bg-risk-low-bg text-risk-low' },
};

const nifasDay = computed(() => {
    if (props.pregnancy?.status !== 'nifas' || !props.pregnancy.nifas_started_at) {
        return null;
    }

    const started = new Date(props.pregnancy.nifas_started_at);
    return Math.min(42, Math.floor((Date.now() - started.getTime()) / 86400000) + 1);
});

function logout() {
    router.post(route('auth.pregnant.logout'));
}

function startScreening() {
    router.get(route('skrining.transisi'), { session_type: props.nextSessionType });
}

function switchPregnancy(id) {
    showSwitcher.value = false;

    if (id === props.pregnancy?.id) {
        return;
    }

    router.post(route('kehamilan.switch-active', id));
}

const shareFeedback = ref('');

async function shareExperience() {
    const text = 'Saya baru saja menyelesaikan pendampingan kehamilan lewat SIGADIS. Terima kasih untuk bidan dan kader yang mendampingi!';

    if (navigator.share) {
        try {
            await navigator.share({ title: 'SIGADIS', text });
        } catch {
            // Pengguna membatalkan share sheet, bukan error yang perlu ditampilkan.
        }
        return;
    }

    await navigator.clipboard.writeText(text);
    shareFeedback.value = 'Teks disalin, silakan tempel di aplikasi favorit Ibu.';
    setTimeout(() => (shareFeedback.value = ''), 4000);
}
</script>

<template>
    <Head title="Beranda" />

    <div class="min-h-screen bg-brand-pink-50 pb-24">
        <TopAppBar @menu="showAccountMenu = !showAccountMenu">
            <template #actions>
                <NotificationBell
                    index-route="kehamilan.notifikasi.index"
                    mark-read-route="kehamilan.notifikasi.mark-read"
                    mark-all-read-route="kehamilan.notifikasi.mark-all-read"
                />
            </template>
        </TopAppBar>

        <AccountDrawer
            :show="showAccountMenu"
            @close="showAccountMenu = false"
            @logout="showAccountMenu = false; showLogoutConfirm = true"
        />

        <LogoutConfirmDialog
            :show="showLogoutConfirm"
            message="Ibu perlu memasukkan nomor HP lagi untuk masuk kembali."
            @confirm="logout"
            @cancel="showLogoutConfirm = false"
        />

        <div class="mx-auto w-full max-w-md px-6 pt-6">
            <p v-if="page.props.flash?.success" class="mb-4 rounded-lg bg-risk-low-bg p-3 text-sm text-risk-low">
                {{ page.props.flash.success }}
            </p>

            <div v-if="!pregnancy" class="rounded-lg border border-brand-navy-100 bg-white p-4 text-center text-sm text-brand-navy-700">
                <img src="/assets/images/mascot/pose-18-menunggu-santai.png" alt="" class="mx-auto mb-3 h-24 w-24 object-contain" />
                Ibu belum punya profil kehamilan.
                <a :href="route('kehamilan.registrasi.show')" class="font-semibold text-brand-navy-900 underline">
                    Mulai registrasi kehamilan
                </a>
            </div>

            <template v-else>
                <!-- Kartu sapaan (Beranda §3.6.1) -->
                <section
                    class="mb-4 flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm"
                    :class="{ 'cursor-pointer': allPregnancies.length > 1 }"
                    @click="allPregnancies.length > 1 && (showSwitcher = true)"
                >
                    <img
                        v-if="profilePhotoUrl"
                        :src="profilePhotoUrl"
                        alt=""
                        class="h-12 w-12 shrink-0 rounded-full object-cover"
                    />
                    <div v-else class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-navy-100 text-lg font-bold text-brand-navy-900">
                        {{ motherName.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1">
                        <p class="text-base font-bold text-brand-navy-900">{{ greeting }}, {{ motherName }}!</p>
                        <p v-if="pregnancy.status === 'hamil'" class="text-sm text-[--color-brand-pink-500]">
                            Usia Kehamilan: {{ pregnancy.current_gestational_age_weeks }} Minggu
                        </p>
                        <p v-else-if="pregnancy.status === 'nifas'" class="flex items-center gap-1 text-sm text-risk-low">
                            <Icon name="check" size="h-3.5 w-3.5" /> Pendampingan Nifas
                        </p>
                        <p v-else-if="pregnancy.status === 'case_closed'" class="text-sm text-neutral-500">Kasus Ditutup</p>
                    </div>
                    <span v-if="allPregnancies.length > 1" class="text-xs font-semibold text-brand-navy-700 underline">Ganti Profil</span>
                </section>

                <!-- Flows.md §16.2.1: switcher profil, bottom-sheet, cuma relevan kalau >1 profil. -->
                <div v-if="showSwitcher" class="fixed inset-0 z-50 flex items-end bg-black/50" @click.self="showSwitcher = false">
                    <div class="w-full rounded-t-2xl bg-white p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-bold text-brand-navy-900">Pilih Profil Kehamilan</h2>
                            <button type="button" aria-label="Tutup" class="btn btn-circle btn-sm border-none bg-brand-navy-100" @click="showSwitcher = false">
                                &#10005;
                            </button>
                        </div>
                        <div class="mb-4 space-y-2">
                            <button
                                v-for="p in allPregnancies"
                                :key="p.id"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border p-3 text-left"
                                :class="p.id === pregnancy.id ? 'border-2 border-brand-navy-900 bg-brand-pink-50' : 'border-neutral-200'"
                                @click="switchPregnancy(p.id)"
                            >
                                <span class="flex items-center gap-2">
                                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" alt="" class="h-8 w-8 rounded-full object-cover" />
                                    <span v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-navy-100 text-xs font-bold text-brand-navy-900">
                                        {{ p.mother_name.charAt(0).toUpperCase() }}
                                    </span>
                                    <span class="font-medium text-brand-navy-900">{{ p.mother_name }}</span>
                                </span>
                                <span v-if="p.id === pregnancy.id" class="rounded-full bg-risk-low-bg px-2 py-0.5 text-xs font-semibold text-risk-low">
                                    Aktif Saat Ini
                                </span>
                            </button>
                        </div>
                        <a :href="route('kehamilan.registrasi.show')" class="btn btn-outline w-full border-brand-navy-100 text-brand-navy-700">
                            + Tambah Profil Kehamilan Baru
                        </a>
                    </div>
                </div>

                <div v-if="pregnancy.status === 'nifas'" class="mb-4 flex items-center gap-3 rounded-lg bg-brand-navy-900 p-4 text-sm text-white">
                    <img src="/assets/images/mascot/pose-13-menyambut-kembali.png" alt="" class="h-16 w-16 shrink-0 object-contain" />
                    <div>
                        Selamat atas kelahiran buah hati Ibu! SIGADIS akan terus mendampingi selama masa nifas.
                        <p class="mt-1 font-semibold">Hari ke-{{ nifasDay }} dari 42 hari masa nifas.</p>
                    </div>
                </div>

                <template v-if="pregnancy.status === 'case_closed'">
                    <div class="mb-4 flex items-center gap-3 rounded-lg border border-brand-navy-100 bg-white p-4 text-sm text-brand-navy-700">
                        <img src="/assets/images/mascot/pose-14-merayakan.png" alt="" class="h-16 w-16 shrink-0 object-contain" />
                        <span>Kasus kehamilan ini sudah ditutup. Riwayat tetap dapat diakses.</span>
                    </div>
                    <div class="mb-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-brand-navy-100 bg-white p-3 text-center">
                            <p class="text-xs text-neutral-500 uppercase">Total Skrining</p>
                            <p class="text-lg font-bold text-brand-navy-900">{{ pregnancy.screening_sessions_count }} Sesi</p>
                        </div>
                        <div class="rounded-lg border border-brand-navy-100 bg-white p-3 text-center">
                            <p class="text-xs text-neutral-500 uppercase">Status Akhir</p>
                            <p class="text-lg font-bold text-risk-low">Kasus Ditutup</p>
                        </div>
                    </div>

                    <section class="mb-4 flex items-center justify-between rounded-xl bg-brand-navy-900 p-4 text-white">
                        <div>
                            <p class="font-bold">Terima Kasih, Ibu!</p>
                            <button
                                type="button"
                                class="btn btn-sm mt-2 gap-1 border-none bg-[--color-brand-pink-500] text-white"
                                @click="shareExperience"
                            >
                                <Icon name="heart" size="h-4 w-4" /> Bagikan Pengalaman
                            </button>
                        </div>
                        <img src="/assets/images/mascot/pose-14-merayakan.png" alt="" class="h-16 w-16 shrink-0 object-contain" />
                    </section>
                    <p v-if="shareFeedback" class="mb-4 text-center text-xs text-brand-navy-700">{{ shareFeedback }}</p>
                </template>

                <!-- Kartu progres kehamilan (Beranda §3.6.2) -->
                <section v-if="pregnancy.status === 'hamil'" class="mb-4 rounded-xl bg-brand-pink-200 p-4">
                    <div class="mb-2 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-brand-navy-900">Progres Kehamilan</p>
                            <p class="text-xs text-brand-navy-700">Perkiraan Lahir: {{ formattedDueDate }}</p>
                        </div>
                        <span class="text-xl font-bold text-brand-navy-900">{{ pregnancy.progress_percent }}%</span>
                    </div>
                    <div class="mb-3 h-2 w-full rounded-full bg-white/60">
                        <div class="h-2 rounded-full bg-brand-navy-900" :style="{ width: pregnancy.progress_percent + '%' }" />
                    </div>
                    <button
                        v-if="nextSessionType"
                        type="button"
                        class="btn btn-sm w-full border-none bg-white text-brand-navy-900"
                        @click="startScreening"
                    >
                        Cek Skrining SADAR Hari Ini &rarr;
                    </button>
                </section>

                <!-- Grid aksi cepat (Beranda §3.6.3) -->
                <section class="mb-4 grid grid-cols-4 gap-2 text-center">
                    <button
                        v-if="nextSessionType"
                        type="button"
                        class="flex cursor-pointer flex-col items-center gap-1 rounded-lg border border-brand-navy-100 bg-white py-3 text-xs text-brand-navy-900 transition-colors hover:bg-brand-pink-50"
                        @click="startScreening"
                    >
                        <Icon name="shield" size="h-6 w-6" /> Skrining SADAR
                    </button>
                    <a :href="route('kehamilan.faskes')" class="flex cursor-pointer flex-col items-center gap-1 rounded-lg border border-brand-navy-100 bg-white py-3 text-xs text-brand-navy-900 transition-colors hover:bg-brand-pink-50">
                        <Icon name="hospital" size="h-6 w-6" /> Cari Faskes
                    </a>
                    <a :href="route('kehamilan.riwayat')" class="flex cursor-pointer flex-col items-center gap-1 rounded-lg border border-brand-navy-100 bg-white py-3 text-xs text-brand-navy-900 transition-colors hover:bg-brand-pink-50">
                        <Icon name="clock" size="h-6 w-6" /> Riwayat
                    </a>
                    <button
                        type="button"
                        class="flex cursor-pointer flex-col items-center gap-1 rounded-lg border border-emergency-alert bg-emergency-pending-bg py-3 text-xs font-semibold text-emergency-alert transition-colors hover:bg-emergency-alert hover:text-white"
                        @click="emergencyRef?.open()"
                    >
                        <Icon name="alert" size="h-6 w-6" /> Darurat
                    </button>
                </section>

                <!-- Banner ajakan skrining (Beranda §3.6.4) -->
                <section v-if="nextSessionType" class="mb-4 flex items-center justify-between rounded-xl bg-brand-navy-900 p-4 text-white">
                    <div>
                        <p class="font-bold">Jaga Kesehatan Janin</p>
                        <p class="mb-2 text-xs text-white/80">Pantau tanda bahaya sejak dini.</p>
                        <button type="button" class="btn btn-sm border-none bg-[--color-brand-pink-500] text-white" @click="startScreening">
                            Mulai Skrining SADAR
                        </button>
                    </div>
                    <img src="/assets/images/mascot/pose-04-waspada-tenang.png" alt="" class="h-16 w-16 shrink-0 object-contain" />
                </section>

                <!-- Monitoring kehamilan (Beranda §3.6.5) -->
                <section v-if="pregnancy.latest_risk_assessment" class="mb-4">
                    <h2 class="mb-2 text-sm font-bold text-brand-navy-900">Monitoring Kehamilan</h2>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-brand-navy-100 bg-white p-3">
                            <p class="mb-1 text-[10px] text-neutral-500 uppercase">Gejala Terakhir</p>
                            <p class="line-clamp-2 text-sm font-medium text-brand-navy-900">
                                {{ pregnancy.latest_risk_assessment.recommendation_text }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-brand-navy-100 bg-white p-3">
                            <p class="mb-1 text-[10px] text-neutral-500 uppercase">Status Risiko</p>
                            <span
                                class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="riskBadge[pregnancy.latest_risk_assessment.risk_level].class"
                            >
                                {{ riskBadge[pregnancy.latest_risk_assessment.risk_level].label }}
                            </span>
                        </div>
                    </div>
                </section>

                <div class="mb-4 space-y-3 rounded-lg border border-brand-navy-100 bg-white p-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-brand-navy-700">Status</span>
                        <span class="font-medium text-brand-navy-900 capitalize">{{ pregnancy.status.replace('_', ' ') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-brand-navy-700">Bidan Pendamping</span>
                        <span class="font-medium text-brand-navy-900">
                            {{ pregnancy.active_midwife_assignment?.midwife?.full_name ?? 'Belum ada' }}
                        </span>
                    </div>
                    <a
                        v-if="pregnancy.status !== 'case_closed'"
                        :href="route('kehamilan.ganti-bidan.show')"
                        class="block text-right text-xs font-semibold text-brand-navy-900 underline"
                    >
                        Ganti Bidan Pendamping
                    </a>
                </div>
            </template>
        </div>

        <EmergencyButton v-if="pregnancy" ref="emergencyRef" raised />
        <BottomTabBar v-if="pregnancy" active="beranda" />
    </div>
</template>
