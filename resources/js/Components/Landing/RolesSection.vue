<script setup>
import { useMascot } from '@/Composables/useMascot';
import { roles } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';

const { src, alt } = useMascot();

const roleStyles = [
    // Ibu
    {
        gradient: 'from-brand-pink-50 to-brand-pink-200/60',
        border: 'border-brand-pink-200',
        badge: 'bg-brand-pink-200 text-brand-navy-900',
        badgeLabel: 'Ibu Hamil',
    },
    // Bidan/Kader
    {
        gradient: 'from-brand-navy-100/60 to-brand-navy-100',
        border: 'border-brand-navy-100',
        badge: 'bg-brand-navy-900 text-white',
        badgeLabel: 'Tenaga Kesehatan',
    },
    // Admin
    {
        gradient: 'from-brand-cream-100 to-accent-gold-light/40',
        border: 'border-accent-amber/20',
        badge: 'bg-accent-gold-light text-brand-navy-900',
        badgeLabel: 'Administrator',
    },
];
</script>

<template>
    <section class="relative overflow-hidden py-24">
        <div class="pointer-events-none absolute inset-0 bg-linear-to-b from-white to-brand-cream-100/40" />

        <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
            <SectionHeading eyebrow="Untuk siapa?" title="SIGADIS Dirancang untuk Kebutuhan Bersama">
                Satu sistem, tiga peran berbeda â€” semuanya terhubung untuk tujuan yang sama: keselamatan ibu.
            </SectionHeading>

            <div class="mt-14 grid gap-6 lg:grid-cols-3">
                <div
                    v-for="(role, i) in roles"
                    :key="role.name"
                    :class="[
                        'group relative overflow-hidden rounded-3xl border bg-linear-to-b p-8 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl',
                        roleStyles[i]?.gradient ?? 'from-white to-brand-pink-50',
                        roleStyles[i]?.border ?? 'border-neutral-100',
                    ]"
                >
                    <!-- Role badge -->
                    <span
                        :class="['inline-flex rounded-full px-3 py-1 text-xs font-bold tracking-wide', roleStyles[i]?.badge ?? 'bg-brand-pink-200 text-brand-navy-900']"
                    >
                        {{ roleStyles[i]?.badgeLabel ?? role.name }}
                    </span>

                    <!-- Mascot or icon â€” large and centered -->
                    <div class="relative mx-auto mt-6 flex h-36 w-36 items-end justify-center">
                        <!-- Glow circle behind mascot -->
                        <div class="absolute inset-0 rounded-full bg-white/60 blur-xl" />

                        <img
                            v-if="role.mascotKey"
                            :src="src(role.mascotKey)"
                            :alt="alt(role.mascotKey)"
                            class="relative z-10 h-full w-full object-contain drop-shadow-xl transition-transform duration-500 group-hover:scale-110 group-hover:-translate-y-2"
                        />
                        <div
                            v-else
                            class="relative z-10 flex h-24 w-24 items-center justify-center rounded-full bg-white shadow-lg [&_svg]:h-12 [&_svg]:w-12 [&_svg]:text-brand-navy-900 transition-transform duration-500 group-hover:scale-110"
                            v-html="role.icon"
                        />
                    </div>

                    <h3 class="mt-6 text-2xl font-black text-brand-navy-900">{{ role.name }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-neutral-500">{{ role.description }}</p>

                    <!-- Decorative corner circle -->
                    <div class="pointer-events-none absolute -right-8 -bottom-8 h-28 w-28 rounded-full bg-white/30" />
                </div>
            </div>
        </div>
    </section>
</template>

