<template>
    <AuthLayout title="Verification code">
        <form @submit.prevent="submit" class="md:w-[30%] w-[95%] bg-white rounded-lg border shadow-lg md:px-10 md:py-8 px-7 py-5 z-[2]" x-data="{popup : false}">
            <div class="text-center">
                <p class="text-dark text-[24px] font-krub-bold mb-5">
                    Verification code - Kontakami
                </p>
            </div>
            <p class="text-dark text-[15px] mb-4">
                Please enter 4 digit verification code that we sent to your
                email address {{ email }}
            </p>
            <div class="flex gap-2 my-8 justify-center">
                <InputOtp v-model="form.otp[0]" />
                <InputOtp v-model="form.otp[1]" />
                <InputOtp v-model="form.otp[2]" />
                <InputOtp v-model="form.otp[3]" />
            </div>

            <p class="text-dark text-[15px] mb-4 text-center" v-if="countdown">
                Try again in {{ countdown }}
            </p>
            <p
                class="text-[#CE1212] text-[15px] mb-4 text-center"
                v-if="!countdown"
            >
                If you didn’t receive a code.
                <span
                    class="cursor-pointer text-yellow font-krub-medium"
                    @click="resendOtp"
                >
                    Resend
                </span>
            </p>

            <p class="text-center text-red text-[13px] mb-2">
                {{ $page.props.flash.error }}
            </p>
            <ButtonYellow
                type="submit"
                class="w-full block py-3 mt-7 font-krub-medium uppercase"
                :disabled="
                    form.otp[0] === '' ||
                    form.otp[1] === '' ||
                    form.otp[2] === '' ||
                    form.otp[3] === '' ||
                    form.processing
                "
                :loading="form.processing"
            >
                Submit
            </ButtonYellow>
        </form>
    </AuthLayout>
</template>
<script setup lang="ts">
import AuthLayout from "@/layouts/AuthLayout.vue";
import InputOtp from "@/components/input/InputOtp.vue";
import ButtonYellow from "@/components/button/ButtonYellow.vue";
import { startCountdownInterval } from "@/composables/global-function";
import { router, useForm } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";

const props = defineProps(["token", "email", "resend_url", "action"]);

const countdown = ref("");
const intervalKey: any = ref(null);
const form: any = useForm({
    otp: [],
});

const submit = () => {
    if (!form.processing) {
        form.post(props.action, {
            onFinish: () => {
                clearOtp();
            },
        });
    }
};

const resendOtp = () => {
    startCountdown();
    router.post(props.resend_url, {
        email: props.email
    });
};

const clearOtp = () => {
    document.querySelectorAll('input[name="otp[]"]').forEach((input: any) => {
        input.value = "";
    });
    form.otp = ["", "", "", ""];
};

const startCountdown = () => {
    startCountdownInterval(
        props.token,
        intervalKey.value,
        (timer: any) => {
            countdown.value = timer;
        },
        (key: any) => {
            intervalKey.value = key;
        }
    );
};

onMounted(() => {
    clearOtp();
    startCountdown();
});
</script>
