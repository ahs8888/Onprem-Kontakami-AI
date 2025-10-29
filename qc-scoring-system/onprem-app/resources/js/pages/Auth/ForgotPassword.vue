<template>
    <AuthLayout title="Forgot Password">
        <form @submit.prevent="submit" class="md:w-[30%] w-[95%] bg-white rounded-lg border shadow-lg md:px-10 md:py-8 px-7 py-5 z-[2]" x-data="{popup : false}">
            <div
                class="bg-[#3943B724] border-primary border text-dark text-[11px] w-full mb-3 text-center rounded-md py-2"
                v-if="$page.props.flash.error"
            >
                {{ $page.props.flash.error }}
            </div>
            <h1 class="text-dark text-[24px] font-krub font-bold">
                Forgot Password
            </h1>
            <p class="text-dark text-[12px] mb-4 text-x">
                We will send you a verification code
            </p>
            <Input
                type="email"
                placeholder="Email"
                id="email"
                name="email"
                icon="isax-b icon-sms"
                required
                v-model="form.email"
                :error="form.errors.email"
            />
            <ButtonPrimary
                type="submit"
                :disabled="!form.email || form.processing"
                :loading="form.processing"
                class="w-full block py-3 font-krub font-medium uppercase"
            >
                SUBMIT
            </ButtonPrimary>
            <p class="text-center text-[14px] font-krub font-medium mt-4 mb-4">
                <Link
                    :href="route('auth.login.index')"
                    class="text-primary underline"
                >
                    Back to Login
                </Link>
            </p>
        </form>
    </AuthLayout>
</template>
<script setup lang="ts">
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Input from "@/Components/Input/Index.vue";
import ButtonPrimary from "@/Components/Button/ButtonPrimary.vue";
import { useForm, Link } from "@inertiajs/vue3";

const form = useForm({
    email: "",
});

const submit = () => {
    if (!form.processing) {
        form.post(route("auth.forgot-password.otp-send"));
    }
};
</script>
