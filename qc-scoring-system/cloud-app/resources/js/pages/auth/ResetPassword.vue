<template>
    <AuthLayout title="Reset Password">
        <form @submit.prevent="submit" class="md:w-[30%] w-[95%] bg-white rounded-lg border shadow-lg md:px-10 md:py-8 px-7 py-5 z-[2]" x-data="{popup : false}">
            <div
                class="bg-[#3943B724] border-yellow border text-dark text-[11px] w-full mb-3 text-center rounded-md py-2"
                v-if="$page.props.flash.error"
            >
                {{ $page.props.flash.error }}
            </div>

            <h1 class="text-dark text-[24px] font-krub-bold">
                Reset Password
            </h1>
            <p class="text-dark text-[12px] mb-4 text-x">
                Enter your new password
            </p>

            <InputPassword
                placeholder="New Password"
                id="password"
                name="password"
                icon="isax-b icon-lock"
                required
                v-model="form.password"
                :error="form.errors.password"
            />

            <InputPassword
                placeholder="Re-Enter New Password"
                id="confirm_password"
                name="confirm_password"
                icon="isax-b icon-lock"
                required
                help="Minimum 8 characters including capital & small letters (A-Z), (a-z), and numbers (1-9)"
                v-model="form.confirm_password"
                :error="form.errors.confirm_password"
            />
            <ButtonYellow
                type="submit"
                :disabled="
                    !form.password ||
                    !form.confirm_password ||
                    (form.password !=form.confirm_password) || form.processing"
                :loading="form.processing"
                class="w-full block py-3 font-krub-medium uppercase"
            >
                SUBMIT
            </ButtonYellow>
            <p class="text-center text-[14px] font-krub-medium mt-4 mb-4">
                <Link
                    :href="route('auth.login.index')"
                    class="text-yellow underline"
                >
                    Back to Login
                </Link>
            </p>
        </form>
    </AuthLayout>
</template>
<script setup lang="ts">
import AuthLayout from "@/layouts/AuthLayout.vue";
import InputPassword from "@/components/input/Password.vue";
import ButtonYellow from "@/components/button/ButtonYellow.vue";
import { useForm, Link } from "@inertiajs/vue3";

const props = defineProps(['tokenId'])
const form = useForm({
    password: "",
    confirm_password: "",
});

const submit = () => {
    if (!form.processing) {
        form.post(route("auth.forgot-password.update-password",props.tokenId));
    }
};
</script>
