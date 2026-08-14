<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useMascot } from '@/Composables/useMascot';
import { navItems, APK_URL } from '@/Data/landingContent';

const { src, alt } = useMascot();
const mobileMenuOpen = ref(false);
const scrolled = ref(false);
const page = usePage();

const isActive = (href) => computed(() => page.url === href || (href !== '/' && page.url.startsWith(href))).value;

const handleScroll = () => { scrolled.value = window.scrollY > 20; };
onMounted(() => window.addEventListener('scroll', handleScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));
</script>

<template>
    <header
        :class="[
            'sticky top-0 z-50 transition-all duration-300',
            scrolled
                ? 'bg-white/95 backdrop-blur-md shadow-sm shadow-brand-navy-900/5 border-b border-neutral-200/60'
                : 'bg-transparent'
        ]"
    >
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-3 group">
                <img :src="src('logoSistem')" :alt="alt('logoSistem')" class="h-9 w-9 object-contain transition-transform duration-200 group-hover:scale-110" />
                <span class="text-xl font-black tracking-tight text-brand-navy-900">SIGADIS</span>
            </Link>

            <!-- Desktop Nav -->
            <div class="hidden lg:flex lg:items-center lg:gap-1">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'relative px-4 py-2 text-sm font-semibold rounded-full transition-all duration-200',
                        isActive(item.href)
                            ? 'text-brand-navy-900 bg-brand-navy-100'
                            : 'text-neutral-500 hover:text-brand-navy-900 hover:bg-brand-navy-100/60'
                    ]"
                >
                    {{ item.label }}
                </Link>
            </div>

            <!-- Desktop CTA -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                <Link href="/login" class="text-sm font-semibold text-brand-navy-900 hover:text-brand-navy-700 transition-colors px-3 py-2">
                    Masuk
                </Link>
                <Link href="/register" class="rounded-full bg-brand-pink-200 px-5 py-2.5 text-sm font-semibold text-brand-navy-900 transition-all duration-200 hover:bg-brand-pink-500 hover:shadow-sm">
                    Daftar
                </Link>
                <a :href="APK_URL" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-brand-navy-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-brand-navy-700 hover:shadow-md hover:-translate-y-px">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.523 15.341 13 12.839V7a1 1 0 1 0-2 0v5.839L6.477 15.34a1 1 0 1 0 1 1.732L12 14.575l4.523 2.497a1 1 0 0 0 1-1.731Z"/><path fill-rule="evenodd" d="M12 1C5.925 1 1 5.925 1 12s4.925 11 11 11 11-4.925 11-11S18.075 1 12 1ZM3 12a9 9 0 1 1 18 0 9 9 0 0 1-18 0Z" clip-rule="evenodd"/></svg>
                    Download APK
                </a>
            </div>

            <!-- Mobile Hamburger -->
            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-full text-brand-navy-900 hover:bg-brand-navy-100 transition-colors sm:hidden"
                @click="mobileMenuOpen = !mobileMenuOpen"
                aria-label="Toggle navigation"
                :aria-expanded="mobileMenuOpen"
            >
                <svg v-if="!mobileMenuOpen" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <svg v-else viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </nav>

        <!-- Mobile Dropdown -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-3"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-3"
        >
            <div v-if="mobileMenuOpen" class="absolute w-full bg-white/98 backdrop-blur-md border-b border-neutral-200/60 px-6 py-6 shadow-xl sm:hidden">
                <div class="flex flex-col gap-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'rounded-xl px-4 py-3 text-base font-semibold transition-colors',
                            isActive(item.href)
                                ? 'bg-brand-navy-100 text-brand-navy-900'
                                : 'text-neutral-600 hover:bg-neutral-50 hover:text-brand-navy-900'
                        ]"
                        @click="mobileMenuOpen = false"
                    >
                        {{ item.label }}
                    </Link>
                </div>
                <div class="mt-5 flex flex-col gap-3 pt-5 border-t border-neutral-100">
                    <Link href="/login" class="flex w-full items-center justify-center rounded-full border-2 border-brand-navy-100 bg-white py-3 text-sm font-semibold text-brand-navy-900 transition-colors hover:border-brand-navy-900" @click="mobileMenuOpen = false">
                        Masuk
                    </Link>
                    <Link href="/register" class="flex w-full items-center justify-center rounded-full bg-brand-pink-200 py-3 text-sm font-semibold text-brand-navy-900 hover:bg-brand-pink-500 transition-colors" @click="mobileMenuOpen = false">
                        Daftar
                    </Link>
                    <a :href="APK_URL" target="_blank" rel="noopener noreferrer" class="flex w-full items-center justify-center gap-2 rounded-full bg-brand-navy-900 py-3 text-sm font-semibold text-white shadow-sm" @click="mobileMenuOpen = false">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.523 15.341 13 12.839V7a1 1 0 1 0-2 0v5.839L6.477 15.34a1 1 0 1 0 1 1.732L12 14.575l4.523 2.497a1 1 0 0 0 1-1.731Z"/><path fill-rule="evenodd" d="M12 1C5.925 1 1 5.925 1 12s4.925 11 11 11 11-4.925 11-11S18.075 1 12 1ZM3 12a9 9 0 1 1 18 0 9 9 0 0 1-18 0Z" clip-rule="evenodd"/></svg>
                        Download APK
                    </a>
                </div>
            </div>
        </transition>
    </header>
</template>
