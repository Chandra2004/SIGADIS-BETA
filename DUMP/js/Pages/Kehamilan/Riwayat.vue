<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AccountDrawer from '@/Components/Shared/AccountDrawer.vue';
import BottomTabBar from '@/Components/Shared/BottomTabBar.vue';
import LogoutConfirmDialog from '@/Components/Shared/LogoutConfirmDialog.vue';
import TopAppBar from '@/Components/Shared/TopAppBar.vue';

defineProps({
    motherName: { type: String, default: null },
    screeningSessions: { type: Array, required: true },
    referrals: { type: Array, required: true },
});

const showAccountMenu = ref(false);
const showLogoutConfirm = ref(false);

function logout() {
    router.post(route('auth.pregnant.logout'));
}

const dotClass = {
    tinggi: 'bg-risk-high',
    sedang: 'bg-risk-medium',
    rendah: 'bg-risk-low',
};

const badgeClass = {
    tinggi: 'bg-risk-high-bg text-risk-high',
    sedang: 'bg-risk-medium-bg text-risk-medium',
    rendah: 'bg-risk-low-bg text-risk-low',
};
</script>

<template>
    <Head title="Riwayat Saya" />

    <div class="min-h-screen bg-brand-pink-50 pb-24">
        <TopAppBar title="Riwayat" @menu="showAccountMenu = !showAccountMenu" />
        <AccountDrawer :show="showAccountMenu" @close="showAccountMenu = false" @logout="showAccountMenu = false; showLogoutConfirm = true" />
        <LogoutConfirmDialog
            :show="showLogoutConfirm"
            message="Ibu perlu memasukkan nomor HP lagi untuk masuk kembali."
            @confirm="logout"
            @cancel="showLogoutConfirm = false"
        />

        <div class="mx-auto w-full max-w-md px-6 py-6">
            <div class="mb-6 flex items-center gap-3">
                <img src="/assets/images/mascot/pose-12-merenung-ramah.png" alt="" class="h-16 w-16 shrink-0 object-contain" />
                <div>
                    <h1 class="text-lg font-bold text-brand-navy-900">Riwayat Skrining</h1>
                    <p class="text-sm text-brand-navy-700">Lacak perkembangan kesehatan kehamilan Ibu.</p>
                </div>
            </div>

            <section class="mb-6">
                <p v-if="screeningSessions.length === 0" class="text-sm text-brand-navy-700">Belum ada riwayat skrining.</p>
                <div v-else class="space-y-3">
                    <div v-for="s in screeningSessions" :key="s.id" class="relative rounded-lg border border-brand-navy-100 bg-white p-4 pl-5">
                        <span
                            class="absolute top-4 left-0 h-2.5 w-2.5 -translate-x-1/2 rounded-full"
                            :class="s.risk_assessment ? dotClass[s.risk_assessment.risk_level] : 'bg-neutral-300'"
                        />
                        <div class="mb-1 flex items-center justify-between">
                            <p class="text-xs text-neutral-500">{{ new Date(s.started_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}</p>
                            <span
                                v-if="s.risk_assessment"
                                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="badgeClass[s.risk_assessment.risk_level]"
                            >
                                Risiko {{ s.risk_assessment.risk_level }}
                            </span>
                        </div>
                        <p class="font-bold text-brand-navy-900 capitalize">{{ s.session_type }}</p>
                        <p v-if="s.risk_assessment" class="text-sm text-brand-navy-700">{{ s.risk_assessment.recommendation_text }}</p>
                        <p v-else class="text-sm text-neutral-500">Belum selesai</p>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-sm font-semibold text-brand-navy-700 uppercase">Rujukan</h2>
                <p v-if="referrals.length === 0" class="text-sm text-brand-navy-700">Belum ada catatan rujukan.</p>
                <div v-for="r in referrals" :key="r.id" class="mb-2 rounded-lg border border-brand-navy-100 bg-white p-3 text-sm">
                    <p class="font-medium text-brand-navy-900">{{ r.facility.name }}</p>
                    <p class="text-brand-navy-700">{{ new Date(r.referred_at).toLocaleDateString('id-ID') }}</p>
                </div>
            </section>
        </div>

        <BottomTabBar active="riwayat" />
    </div>
</template>
