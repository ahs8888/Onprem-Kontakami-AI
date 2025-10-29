<template>
    <AuthLayout title="Login">
        <section
            class="bg-white rounded-lg border shadow-lg md:w-[30%] w-[95%] z-[2]"
        >
            <div class="flex md:flex-row flex-col">
                <form
                    @submit.prevent="submit"
                    class="flex-1 md:px-10 md:py-8 px-4 py-5"
                    x-data="{popup : false}"
                >
                    <div
                        class="bg-[#38a36321] border-[#38A363] border text-dark text-[11px] w-full mb-3 text-center rounded-md py-2"
                        v-if="$page.props.flash.success"
                    >
                        {{ $page.props.flash.success }}
                    </div>
                    <div
                        class="bg-[#3943B724] border !border-primary text-dark text-[11px] w-full mb-3 text-center rounded-md py-2"
                        v-if="$page.props.flash.error"
                    >
                        {{ $page.props.flash.error }}
                    </div>

                    <h1 class="text-dark text-[25px] font-krub font-bold mb-5">Login</h1>
                    <Input
                        type="text"
                        placeholder="Username"
                        id="username"
                        name="username"
                        icon="isax-b icon-user"
                        required
                        v-model="form.username"
                        :error="form.errors.username"
                    />

                    <InputPassword
                        placeholder="Password"
                        id="password"
                        name="password"
                        icon="isax-b icon-lock"
                        required
                        v-model="form.password"
                        :error="form.errors.password"
                    />
                    <p
                        class="text-center text-[14px] font-krub font-medium mb-4 mt-7"
                    >
                        Forgot your password ?
                        <Link
                            :href="route('auth.reset-password.index')"
                            class="text-primary underline"
                        >
                            click here
                        </Link>
                    </p>
                    <ButtonPrimary
                        type="submit"
                        :disabled="
                            !form.username || !form.password || form.processing
                        "
                        :loading="form.processing"
                        class="w-full block py-3 font-krub font-medium uppercase"
                    >
                        LOGIN
                    </ButtonPrimary>
                    <p
                        class="text-center text-[14px] font-krub font-medium mt-4 mb-4"
                    >
                        Don't have an account?
                        <Link
                            :href="route('auth.register.index')"
                            class="text-primary underline"
                        >
                            Register
                        </Link>
                    </p>
                </form>
            </div>
        </section>
    </AuthLayout>
</template>
<script setup lang="ts">
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Input from "@/Components/Input/Index.vue";
import InputPassword from "@/Components/Input/Password.vue";
import ButtonPrimary from "@/Components/Button/ButtonPrimary.vue";
import { useForm, Link, usePage } from "@inertiajs/vue3";

defineProps(["menus"]);
const form = useForm({
    username: "",
    password: "",
});

const submit = () => {
    if (!form.processing) {
        form.post(route("auth.login.store"));
    }
};
</script>
