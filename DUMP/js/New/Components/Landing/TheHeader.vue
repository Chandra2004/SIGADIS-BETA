<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useMascot } from '@/Composables/useMascot';
import { navItems, APK_URL } from '@/Data/landingContent';

const { src, alt } = useMascot();
const mobileMenuOpen = ref(false);
const page = usePage();

const isActive = (href) => computed(() => page.url === href || (href !== '/' && page.url.startsWith(href))).value;
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-brand-pink-500/50 bg-white/90 backdrop-blur-md">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <Link href="/" class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl border border-brand-pink-500/60 bg-brand-pink-50">
                    <img :src="src('logoSistem')" :alt="alt('logoSistem')" class="h-9 w-9 object-contain" />
                </div>
                <p class="text-lg font-black tracking-tight text-brand-navy-900">SIGADIS</p>
            </Link>

            <div class="hidden items-center gap-6 text-sm font-medium lg:flex">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'transition hover:text-brand-navy-900',
                        isActive(item.href) ? 'font-bold text-brand-navy-900' : 'text-brand-navy-700',
                    ]"
                >
                    {{ item.label }}
                </Link>
            </div>

            <div class="hidden items-center gap-3 sm:flex">
                <Link href="/login" class="btn border border-brand-navy-900/20 bg-white text-brand-navy-900 hover:border-brand-pink-500 hover:bg-brand-pink-50 hover:text-brand-navy-900">
                    Masuk
                </Link>
                <Link href="/register" class="btn border border-brand-navy-900/20 bg-white text-brand-navy-900 hover:border-brand-pink-500 hover:bg-brand-pink-50 hover:text-brand-navy-900">
                    Daftar
                </Link>
                <a :href="APK_URL" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                    Download APK
                </a>
            </div>

            <!-- Mobile hamburger -->
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-xl border border-brand-pink-500/60 text-brand-navy-900 sm:hidden"
                :aria-expanded="mobileMenuOpen"
                aria-label="Buka menu navigasi"
                @click="mobileMenuOpen = !mobileMenuOpen"
            >
                <svg v-if="!mobileMenuOpen" viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                <svg v-else viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18" /></svg>
            </button>
        </nav>

        <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileMenuOpen" class="border-t border-brand-pink-500/50 bg-white px-4 py-4 sm:hidden">
                <div class="flex flex-col gap-1 text-base font-medium">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'rounded-xl px-3 py-2.5 hover:bg-brand-pink-50 hover:text-brand-navy-900',
                            isActive(item.href) ? 'bg-brand-pink-50 font-bold text-brand-navy-900' : 'text-brand-navy-700',
                        ]"
                        @click="mobileMenuOpen = false"
                    >
                        {{ item.label }}
                    </Link>
                </div>
                <div class="mt-3 flex flex-col gap-2 border-t border-brand-pink-500/40 pt-3">
                    <Link href="/login" class="btn h-11 rounded-xl border border-brand-navy-900/20 bg-white text-brand-navy-900" @click="mobileMenuOpen = false">
                        Masuk
                    </Link>
                    <Link href="/register" class="btn h-11 rounded-xl border border-brand-navy-900/20 bg-white text-brand-navy-900" @click="mobileMenuOpen = false">
                        Daftar
                    </Link>
                    <a :href="APK_URL" target="_blank" rel="noopener noreferrer" class="btn btn-primary h-11 rounded-xl" @click="mobileMenuOpen = false">
                        Download APK
                    </a>
                </div>
            </div>
        </transition>
    </header>
</template>
