<script setup>
import { useMascot } from '@/Composables/useMascot';
import { roles } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';

const { src, alt } = useMascot();
</script>

<template>
    <section class="bg-brand-cream-100 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading eyebrow="Untuk siapa?" title="SIGADIS Dirancang untuk Kebutuhan Bersama" />

            <div class="mt-12 grid gap-8 lg:grid-cols-3">
                <div v-for="role in roles" :key="role.name" class="rounded-[2rem] border border-brand-pink-500/40 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-brand-navy-900 to-brand-pink-500 shadow-lg shadow-brand-pink-500/20">
                        <img v-if="role.mascotKey" :src="src(role.mascotKey)" :alt="alt(role.mascotKey)" class="h-16 w-16 object-contain" />
                        <span v-else class="text-white [&_svg]:h-8 [&_svg]:w-8" v-html="role.icon" />
                    </div>
                    <h3 class="mt-6 text-2xl font-black text-ink-900">{{ role.name }}</h3>
                    <p class="mt-4 text-base leading-7 text-brand-navy-700">{{ role.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
