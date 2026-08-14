<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppShell from '@/Components/Desktop/AppShell.vue';

defineProps({
    motherName: { type: String, required: true },
    screeningSessions: { type: Array, required: true },
    referrals: { type: Array, required: true },
});

const riskDotClass = {
    tinggi: 'bg-risk-high',
    sedang: 'bg-risk-medium',
    rendah: 'bg-risk-low',
};

function logout() {
    router.post(route('auth.staff.logout'));
}
</script>

<template>
    <Head title="Riwayat Pasien" />

    <AppShell @logout="logout">
        <div class="mx-auto max-w-xl space-y-6 px-6 py-8">
            <h1 class="text-xl font-bold text-brand-navy-900">Riwayat {{ motherName }}</h1>

            <section class="rounded-xl border border-neutral-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold text-neutral-700 uppercase">Sesi Skrining</h2>
                <p v-if="screeningSessions.length === 0" class="text-sm text-neutral-500">Belum ada riwayat sebelumnya.</p>
                <div v-else class="space-y-2">
                    <div v-for="s in screeningSessions" :key="s.id" class="relative rounded-lg border border-neutral-100 p-3 pl-5 text-sm">
                        <span
                            class="absolute top-4 left-0 h-2.5 w-2.5 -translate-x-1/2 rounded-full"
                            :class="s.risk_assessment ? riskDotClass[s.risk_assessment.risk_level] : 'bg-neutral-300'"
                        />
                        <span class="font-medium capitalize">{{ s.session_type }}</span>
                        &middot; {{ s.risk_assessment ? `Risiko ${s.risk_assessment.risk_level}` : 'Belum ada hasil' }}
                        &middot; {{ new Date(s.started_at).toLocaleDateString('id-ID') }}
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold text-neutral-700 uppercase">Rujukan</h2>
                <p v-if="referrals.length === 0" class="text-sm text-neutral-500">Belum ada rujukan sebelumnya.</p>
                <ul v-else class="space-y-2">
                    <li v-for="r in referrals" :key="r.id" class="border-b border-neutral-100 pb-2 text-sm last:border-0">
                        {{ r.facility.name }} &middot; {{ new Date(r.referred_at).toLocaleDateString('id-ID') }}
                    </li>
                </ul>
            </section>
        </div>
    </AppShell>
</template>
