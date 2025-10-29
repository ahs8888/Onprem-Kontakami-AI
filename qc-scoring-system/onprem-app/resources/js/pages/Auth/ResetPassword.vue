<template>
    <AuthLayout title="Reset Password">
        <form @submit.prevent="submit" class="md:w-[30%] w-[95%] bg-white rounded-lg border shadow-lg md:px-10 md:py-8 px-7 py-5 z-[2]" x-data="{popup : false}">
            <div
                class="bg-[#3943B724] border-primary border text-dark text-[11px] w-full mb-3 text-center rounded-md py-2"
                v-if="$page.props.flash.error"
            >
                {{ $page.props.flash.error }}
            </div>

            <h1 class="text-dark text-[24px] font-krub font-bold mb-4">
                Reset Password
            </h1>

            <Input
                type="text"
                placeholder="Username"
                id="username"
                name="username"
                icon="isax-b icon-user"
                required
                v-model="form.username"
                :error="form.errors.username"
                maxlength="50"
            />

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
                v-model="form.confirm_password"
                :error="form.errors.confirm_password"
                help="Minimum 8 characters including capital & small letters (A-Z), (a-z), and numbers (1-9)"
            />
            <ButtonPrimary
                type="submit"
                :disabled="
                    !form.username.trim() ||
                    !form.password.trim() ||
                    !form.confirm_password.trim() ||
                    (form.password.trim() != form.confirm_password.trim())||
                    form.processing
                "
                :loading="form.processing"
                class="w-full block py-3 font-krub font-medium uppercase mt-4"
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
import AppLogo from "@/Components/Icon/Logo/AppLogo.vue";
import Input from "@/Components/Input/Index.vue";
import InputPassword from "@/Components/Input/Password.vue";
import ButtonPrimary from "@/Components/Button/ButtonPrimary.vue";
import { useForm, Link } from "@inertiajs/vue3";

const props = defineProps(['tokenId'])
const form = useForm({
    username: "",
    password: "",
    confirm_password: "",
});

const submit = () => {
    if (!form.processing) {
        form.post(route("auth.reset-password.store"));
    }
};
</script>
