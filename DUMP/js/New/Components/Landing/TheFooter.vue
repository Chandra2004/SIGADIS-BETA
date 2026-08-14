<script setup>
import { Link } from '@inertiajs/vue3';
import { useMascot } from '@/Composables/useMascot';
import { navItems } from '@/Data/landingContent';

const { src, alt } = useMascot();
const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t border-brand-pink-500/40 bg-brand-cream-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.2fr_0.8fr_0.8fr_1fr] lg:px-8">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl border border-brand-pink-500/60 bg-brand-pink-50">
                        <img :src="src('logoSistem')" :alt="alt('logoSistem')" class="h-8 w-8 object-contain" />
                    </div>
                    <p class="text-xl font-black tracking-tight text-brand-navy-900">SIGADIS</p>
                </div>
                <p class="mt-4 text-base leading-7 text-brand-navy-700">Sistem Gawat Darurat Ibu-Selamat</p>
            </div>

            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-brand-navy-700">Navigasi</p>
                <ul class="mt-4 space-y-3 text-brand-navy-900">
                    <li v-for="item in navItems" :key="item.href">
                        <Link :href="item.href" class="hover:text-brand-navy-700">{{ item.label }}</Link>
                    </li>
                    <li><Link href="/download-apk" class="hover:text-brand-navy-700">Download APK</Link></li>
                </ul>
            </div>

            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-brand-navy-700">Kontak</p>
                <ul class="mt-4 space-y-3 text-brand-navy-900">
                    <li>Email</li>
                    <li>Telepon</li>
                    <li>Alamat</li>
                </ul>
            </div>

            <div class="lg:text-right">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-brand-navy-700">Ikuti</p>
                <div class="mt-4 flex gap-3 lg:justify-end">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border border-brand-pink-500/60 bg-white text-brand-navy-900">f</div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border border-brand-pink-500/60 bg-white text-brand-navy-900">in</div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border border-brand-pink-500/60 bg-white text-brand-navy-900">◎</div>
                </div>
            </div>
        </div>

        <div class="border-t border-brand-pink-500/40">
            <div class="mx-auto max-w-7xl px-4 py-6 text-center text-sm text-brand-navy-700 sm:px-6 lg:px-8">
                © {{ year }} SIGADIS. All rights reserved.
            </div>
        </div>
    </footer>
</template>
