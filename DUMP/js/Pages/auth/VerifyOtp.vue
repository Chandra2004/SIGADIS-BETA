<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import TaskHeader from '@/Components/Shared/TaskHeader.vue';

const props = defineProps({
    phoneNumber: { type: String, required: true },
    otpRequestId: { type: String, default: null },
    debugCode: { type: String, default: null },
});

const digits = ref(['', '', '', '', '', '']);
const boxRefs = useTemplateRef('boxes');
const cooldown = ref(60);
let timer = null;

const form = useForm({
    phone_number: props.phoneNumber,
    otp_request_id: props.otpRequestId,
    otp_code: '',
});

const resend = useForm({ phone_number: props.phoneNumber });

function startCooldown() {
    cooldown.value = 60;
    clearInterval(timer);
    timer = setInterval(() => {
        if (cooldown.value > 0) cooldown.value--;
        else clearInterval(timer);
    }, 1000);
}

onMounted(startCooldown);
onUnmounted(() => clearInterval(timer));

function onDigitInput(index, event) {
    const value = event.target.value.replace(/[^0-9]/g, '').slice(-1);
    digits.value[index] = value;

    if (value && index < 5) {
        boxRefs.value[index + 1]?.focus();
    }

    if (digits.value.every((d) => d !== '')) {
        submit();
    }
}

function onBackspace(index, event) {
    if (event.target.value === '' && index > 0) {
        boxRefs.value[index - 1]?.focus();
    }
}

function submit() {
    form.otp_code = digits.value.join('');
    if (form.otp_code.length !== 6) return;

    form.post(route('auth.pregnant.otp.verify'), {
        onError: () => {
            digits.value = ['', '', '', '', '', ''];
            boxRefs.value[0]?.focus();
        },
    });
}

function resendCode() {
    if (cooldown.value > 0) return;
    resend.post(route('auth.pregnant.otp.send'), { onSuccess: startCooldown });
}
</script>

<template>
    <Head title="Verifikasi Kode" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TaskHeader title="SIGADIS" :show-close="false" :back-href="route('auth.pregnant.phone.show')" />

        <div class="mx-auto w-full max-w-sm flex-1 px-6 pt-4 text-center">
            <h1 class="mb-2 text-left text-2xl font-bold text-brand-navy-900">Verifikasi Kode</h1>
            <p class="mb-1 text-left text-sm text-brand-navy-700">Masukkan 6 digit kode yang dikirim ke nomor {{ phoneNumber }}</p>
            <p v-if="debugCode" class="mb-6 text-left text-xs text-[--color-accent-amber]">
                Mode uji (belum ada gateway WhatsApp): kode Anda {{ debugCode }}
            </p>

            <div class="mt-6 mb-4 flex justify-center gap-2">
                <input
                    v-for="(digit, i) in digits"
                    :key="i"
                    ref="boxes"
                    v-model="digits[i]"
                    type="tel"
                    inputmode="numeric"
                    maxlength="1"
                    class="input input-bordered h-14 w-12 border-brand-navy-100 bg-white text-center text-xl"
                    @input="onDigitInput(i, $event)"
                    @keydown.backspace="onBackspace(i, $event)"
                />
            </div>

            <p v-if="form.errors.otp_code" class="mb-4 text-sm text-[--color-error-form]">
                {{ form.errors.otp_code }}
            </p>

            <p v-if="cooldown > 0" class="mb-4 text-sm text-brand-navy-700">
                Kirim ulang dalam <span class="font-semibold text-brand-navy-900">00:{{ String(cooldown).padStart(2, '0') }}</span>
            </p>
            <button
                v-else
                type="button"
                :disabled="resend.processing"
                class="btn btn-ghost mb-4 text-brand-navy-700"
                @click="resendCode"
            >
                Kirim Ulang Kode
            </button>

            <img src="/assets/images/mascot/pose-19-memperkenalkan-diri.png" alt="" class="mx-auto h-20 w-20 object-contain" />
        </div>
    </div>
</template>
