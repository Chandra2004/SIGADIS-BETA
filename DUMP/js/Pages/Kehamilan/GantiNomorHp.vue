<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import TaskHeader from '@/Components/Shared/TaskHeader.vue';

const props = defineProps({
    currentPhoneNumber: { type: String, required: true },
    oldNumberVerified: { type: Boolean, required: true },
    oldOtpRequestId: { type: String, default: null },
    oldOtpDebugCode: { type: String, default: null },
    newPhoneNumber: { type: String, default: null },
    newOtpRequestId: { type: String, default: null },
    newOtpDebugCode: { type: String, default: null },
});

const page = usePage();

const sendOldForm = useForm({});
const verifyOldForm = useForm({ otp_request_id: props.oldOtpRequestId, otp_code: '' });

const sendNewForm = useForm({ new_phone_number: '' });
const verifyNewForm = useForm({
    new_phone_number: props.newPhoneNumber,
    otp_request_id: props.newOtpRequestId,
    otp_code: '',
});

const isValidNewPhone = computed(() => /^08[0-9]{8,13}$/.test(sendNewForm.new_phone_number));

function sendOldOtp() {
    sendOldForm.post(route('akun.ganti-nomor.send-old'));
}

function verifyOldOtp() {
    verifyOldForm.otp_request_id = props.oldOtpRequestId;
    verifyOldForm.post(route('akun.ganti-nomor.verify-old'));
}

function sendNewOtp() {
    if (!isValidNewPhone.value) return;
    sendNewForm.post(route('akun.ganti-nomor.send-new'));
}

function verifyNewOtp() {
    verifyNewForm.new_phone_number = props.newPhoneNumber;
    verifyNewForm.otp_request_id = props.newOtpRequestId;
    verifyNewForm.post(route('akun.ganti-nomor.verify-new'));
}
</script>

<template>
    <Head title="Ganti Nomor HP" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TaskHeader title="Ganti Nomor HP" :back-href="route('kehamilan.beranda')" :show-close="false" />

        <div class="mx-auto w-full max-w-md flex-1 px-6 py-6">
            <div class="mb-6 flex justify-center gap-2">
                <span class="h-2 w-8 rounded-full" :class="!oldNumberVerified ? 'bg-brand-navy-900' : 'bg-brand-navy-100'" />
                <span class="h-2 w-8 rounded-full" :class="oldNumberVerified ? 'bg-brand-navy-900' : 'bg-brand-navy-100'" />
            </div>

            <p v-if="page.props.flash?.success" class="mb-4 rounded-lg bg-risk-low-bg p-3 text-sm text-risk-low">
                {{ page.props.flash.success }}
            </p>

            <!-- Langkah 1: verifikasi nomor lama -->
            <section v-if="!oldNumberVerified" class="space-y-4 rounded-xl bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-brand-navy-700 uppercase">Langkah 1: Verifikasi Nomor Lama</h2>
                <p class="text-sm text-brand-navy-700">
                    Kami perlu memastikan Ibu masih memegang nomor lama sebelum menggantinya:
                    <span class="font-semibold">{{ currentPhoneNumber }}</span>
                </p>

                <button
                    v-if="!oldOtpRequestId"
                    type="button"
                    class="btn w-full border-none bg-brand-navy-900 text-white"
                    :disabled="sendOldForm.processing"
                    @click="sendOldOtp"
                >
                    Kirim Kode ke Nomor Lama
                </button>

                <template v-else>
                    <p v-if="oldOtpDebugCode" class="text-xs text-[--color-accent-amber]">
                        Mode uji (gateway WA belum aktif): kode Anda {{ oldOtpDebugCode }}
                    </p>
                    <input
                        v-model="verifyOldForm.otp_code"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        placeholder="Masukkan 6 digit kode"
                        class="input input-bordered w-full text-center text-lg tracking-widest"
                    />
                    <p v-if="verifyOldForm.errors.old_otp_code" class="text-sm text-[--color-error-form]">
                        {{ verifyOldForm.errors.old_otp_code }}
                    </p>
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-ghost flex-1" @click="sendOldOtp">Kirim Ulang</button>
                        <button
                            type="button"
                            class="btn flex-1 border-none bg-brand-navy-900 text-white"
                            :disabled="verifyOldForm.otp_code.length !== 6 || verifyOldForm.processing"
                            @click="verifyOldOtp"
                        >
                            Verifikasi
                        </button>
                    </div>
                </template>
            </section>

            <!-- Langkah 2: verifikasi nomor baru -->
            <section v-else class="space-y-4 rounded-xl bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-brand-navy-700 uppercase">Langkah 2: Nomor HP Baru</h2>

                <template v-if="!newOtpRequestId">
                    <label class="text-sm text-brand-navy-700">Nomor HP baru</label>
                    <input
                        v-model="sendNewForm.new_phone_number"
                        type="tel"
                        inputmode="numeric"
                        placeholder="08xxxxxxxxxx"
                        class="input input-bordered w-full"
                    />
                    <p v-if="sendNewForm.errors.new_phone_number" class="text-sm text-[--color-error-form]">
                        {{ sendNewForm.errors.new_phone_number }}
                    </p>
                    <button
                        type="button"
                        class="btn w-full border-none bg-brand-navy-900 text-white"
                        :disabled="!isValidNewPhone || sendNewForm.processing"
                        @click="sendNewOtp"
                    >
                        Kirim Kode ke Nomor Baru
                    </button>
                </template>

                <template v-else>
                    <p class="text-sm text-brand-navy-700">Kode dikirim ke <span class="font-semibold">{{ newPhoneNumber }}</span></p>
                    <p v-if="newOtpDebugCode" class="text-xs text-[--color-accent-amber]">
                        Mode uji (gateway WA belum aktif): kode Anda {{ newOtpDebugCode }}
                    </p>
                    <input
                        v-model="verifyNewForm.otp_code"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        placeholder="Masukkan 6 digit kode"
                        class="input input-bordered w-full text-center text-lg tracking-widest"
                    />
                    <p v-if="verifyNewForm.errors.new_otp_code" class="text-sm text-[--color-error-form]">
                        {{ verifyNewForm.errors.new_otp_code }}
                    </p>
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-ghost flex-1" @click="sendNewOtp">Kirim Ulang</button>
                        <button
                            type="button"
                            class="btn flex-1 border-none bg-brand-navy-900 text-white"
                            :disabled="verifyNewForm.otp_code.length !== 6 || verifyNewForm.processing"
                            @click="verifyNewOtp"
                        >
                            Verifikasi &amp; Ganti Nomor
                        </button>
                    </div>
                </template>
            </section>
        </div>
    </div>
</template>
