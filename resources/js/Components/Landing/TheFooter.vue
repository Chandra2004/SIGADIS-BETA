<script setup>
import { Link } from '@inertiajs/vue3';
import { useMascot } from '@/Composables/useMascot';
import { navItems, APK_URL } from '@/Data/landingContent';

const { src, alt } = useMascot();
const year = new Date().getFullYear();

const quickLinks = [
    { label: 'Beranda', href: '/' },
    { label: 'Tentang', href: '/tentang' },
    { label: 'Fitur', href: '/fitur' },
    { label: 'Cara Kerja', href: '/cara-kerja' },
    { label: 'FAQ', href: '/faq' },
    { label: 'Download APK', href: APK_URL },
];
</script>

<template>
    <footer class="relative overflow-hidden bg-brand-navy-900">
        <!-- Top decoration -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-32 -right-32 h-72 w-72 rounded-full bg-brand-pink-500/8 blur-3xl" />
            <div class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-brand-navy-700/50 blur-2xl" />
            <div class="absolute inset-0 opacity-[0.03]"
                style="background-image: linear-gradient(rgba(255,255,255,0.2) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.2) 1px, transparent 1px); background-size: 48px 48px;" />
        </div>

        <div class="relative mx-auto max-w-7xl px-6 pt-16 pb-10 lg:px-8">
            <!-- Top section: brand + links + contact -->
            <div class="grid gap-10 lg:grid-cols-4">
                <!-- Brand -->
                <div class="lg:col-span-2">
                    <Link href="/" class="inline-flex items-center gap-3 group">
                        <img :src="src('logoSistem')" :alt="alt('logoSistem')" class="h-10 w-10 object-contain transition-transform group-hover:scale-110" />
                        <span class="text-xl font-black tracking-tight text-white">SIGADIS</span>
                    </Link>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-brand-navy-100">
                        <strong class="text-white">Sistem Gawat Darurat Ibu-Selamat.</strong><br/>
                        Mendampingi dengan hangat, melindungi dengan sigap. Menghubungkan ibu hamil dengan bidan dan sistem respons darurat dalam satu platform.
                    </p>

                    <!-- Social -->
                    <div class="mt-6 flex gap-3">
                        <a href="#" aria-label="Facebook" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white transition-all hover:bg-brand-pink-500 hover:scale-110">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>
                        </a>
                        <a href="#" aria-label="Instagram" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white transition-all hover:bg-brand-pink-500 hover:scale-110">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>

                <!-- Navigation -->
                <div>
                    <h3 class="text-xs font-black uppercase tracking-widest text-brand-navy-100">Navigasi</h3>
                    <ul class="mt-5 flex flex-col gap-3">
                        <li v-for="item in quickLinks" :key="item.href">
                            <a
                                :href="item.href"
                                class="text-sm text-brand-navy-100/70 transition-all hover:text-white hover:translate-x-1 inline-block"
                            >
                                {{ item.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="text-xs font-black uppercase tracking-widest text-brand-navy-100">Kontak</h3>
                    <ul class="mt-5 flex flex-col gap-4">
                        <li class="flex items-center gap-3 text-sm text-brand-navy-100/70">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10">
                                <svg class="h-3.5 w-3.5 text-brand-pink-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </span>
                            halo@sigadis.id
                        </li>
                        <li class="flex items-center gap-3 text-sm text-brand-navy-100/70">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10">
                                <svg class="h-3.5 w-3.5 text-brand-pink-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.92 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.82 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6"/></svg>
                            </span>
                            (031) 555-0123
                        </li>
                        <li class="flex items-start gap-3 text-sm leading-relaxed text-brand-navy-100/70">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10 mt-0.5">
                                <svg class="h-3.5 w-3.5 text-brand-pink-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            Jl. Medika No. 8, Surabaya<br/>Jawa Timur, Indonesia
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom bar -->
            <div class="mt-14 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-8 sm:flex-row text-sm text-brand-navy-100/50">
                <p>© {{ year }} SIGADIS. All rights reserved.</p>
                <div class="flex gap-6">
                    <Link href="#" class="hover:text-white transition-colors">Syarat &amp; Ketentuan</Link>
                    <Link href="#" class="hover:text-white transition-colors">Kebijakan Privasi</Link>
                </div>
            </div>
        </div>
    </footer>
</template>
