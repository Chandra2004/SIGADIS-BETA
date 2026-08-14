<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    appName: {
        type: String,
        default: 'SIGADIS',
    },
});

const logoUrl = '/assets/images/asset-logo.png';
const apkUrl = 'https://dev-sigadis-beta.rf.gd/application/SIGADIS.apk';
</script>

<template>
    <Head title="Masuk - SIGADIS" />

    <div class="min-h-screen bg-[#FDF3F6] px-4 py-8 text-[#26292E] sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl overflow-hidden rounded-[2rem] border border-[#F3AEC0]/50 bg-white shadow-[0_24px_80px_rgba(44,74,110,0.08)]">
            <div class="grid min-h-[820px] lg:grid-cols-2">
                <section class="relative hidden overflow-hidden bg-gradient-to-br from-[#2C4A6E] via-[#3D6086] to-[#F3AEC0] p-8 text-white lg:flex lg:flex-col lg:justify-between">
                    <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute -left-10 bottom-0 h-56 w-56 rounded-full bg-[#F3AEC0]/30 blur-3xl"></div>

                    <div class="relative z-10 flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/20 bg-white/10 backdrop-blur-sm">
                            <img :src="logoUrl" alt="SIGADIS logo" class="h-10 w-10 object-contain" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#F7EFE4]">Aplikasi</p>
                            <h1 class="text-2xl font-black">SIGADIS</h1>
                        </div>
                    </div>

                    <div class="relative z-10 space-y-6">
                        <div class="rounded-[1.7rem] border border-white/20 bg-white/5 p-5 backdrop-blur-sm">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#F7EFE4]">Selamat datang kembali</p>
                            <h2 class="mt-3 text-4xl font-black leading-tight">Pantau dan tangani kondisi ibu dengan lebih aman.</h2>
                        </div>
                        <div class="flex justify-center">
                            <img src="/assets/mascot/mascot-pose-19.webp" alt="Maskot SIGADIS" class="w-52 drop-shadow-xl" />
                        </div>
                    </div>

                    <div class="relative z-10 rounded-[1.5rem] border border-white/20 bg-white/5 p-4 text-sm text-[#EAF4FF]">
                        Jangan ragu menghubungi bidan dan kader ketika kondisi Anda memerlukan perhatian lebih.
                    </div>
                </section>

                <section class="flex items-center justify-center p-5 sm:p-8 lg:p-12">
                    <div class="w-full max-w-md">
                        <div class="mb-8 flex items-center justify-between gap-3 lg:hidden">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#F3AEC0]/50 bg-[#FDF3F6]">
                                    <img :src="logoUrl" alt="SIGADIS logo" class="h-8 w-8 object-contain" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#3D6086]">SIGADIS</p>
                                    <h1 class="text-xl font-black text-[#2C4A6E]">Masuk</h1>
                                </div>
                            </div>
                            <a :href="apkUrl" target="_blank" class="btn btn-xs border border-[#2C4A6E]/20 bg-white text-[#2C4A6E] hover:bg-[#FDF3F6]">Download APK</a>
                        </div>

                        <div class="mb-8 hidden lg:block">
                            <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#2C4A6E]">Masuk</p>
                            <h2 class="mt-3 text-4xl font-black tracking-tight text-[#26292E]">Selamat datang kembali</h2>
                        </div>

                        <form class="space-y-5">
                            <div class="form-control">
                                <label class="label pb-2">
                                    <span class="label-text text-sm font-semibold text-[#2C4A6E]">Email atau nomor telepon</span>
                                </label>
                                <input
                                    type="text"
                                    placeholder="contoh: ibu.sigadis@gmail.com"
                                    class="input h-12 w-full rounded-2xl border border-[#D9E4F0] bg-[#FFFFFF] px-4 text-[#26292E] outline-none transition focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/10"
                                />
                            </div>

                            <div class="form-control">
                                <label class="label pb-2">
                                    <span class="label-text text-sm font-semibold text-[#2C4A6E]">Kata sandi</span>
                                </label>
                                <input
                                    type="password"
                                    placeholder="Masukkan kata sandi"
                                    class="input h-12 w-full rounded-2xl border border-[#D9E4F0] bg-[#FFFFFF] px-4 text-[#26292E] outline-none transition focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/10"
                                />
                            </div>

                            <div class="flex items-center justify-between gap-3 text-sm">
                                <label class="flex items-center gap-2 text-[#3D6086]">
                                    <input type="checkbox" class="checkbox checkbox-sm border-[#2C4A6E] checked:bg-[#2C4A6E] checked:border-[#2C4A6E]" />
                                    Ingat saya
                                </label>
                                <Link href="/reset-password" class="font-semibold text-[#2C4A6E] hover:text-[#3D6086]">Lupa kata sandi?</Link>
                            </div>

                            <button type="submit" class="btn h-12 w-full rounded-2xl border-0 bg-[#2C4A6E] text-base font-semibold text-white shadow-lg shadow-[#2C4A6E]/20 hover:bg-[#3D6086]">
                                Masuk
                            </button>

                            <div class="divider text-xs uppercase tracking-[0.2em] text-[#3D6086]">atau</div>

                            <a :href="apkUrl" target="_blank" class="btn h-12 w-full rounded-2xl border border-[#2C4A6E]/20 bg-[#FDF3F6] text-base font-semibold text-[#2C4A6E] hover:bg-[#F7EFE4]">
                                Download APK SIGADIS
                            </a>

                            <p class="text-center text-sm text-[#3D6086]">
                                Belum punya akun?
                                <Link href="/register" class="ml-1 font-bold text-[#2C4A6E] hover:text-[#3D6086]">Daftar sekarang</Link>
                            </p>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
