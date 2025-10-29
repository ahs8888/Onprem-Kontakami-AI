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
                        class="bg-[#3943B724] border-yellow border text-dark text-[11px] w-full mb-3 text-center rounded-md py-2"
                        v-if="$page.props.flash.error"
                    >
                        {{ $page.props.flash.error }}
                    </div>

                    <h1 class="text-dark text-[25px] font-krub-bold mb-5">Login</h1>
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

                    <InputPassword
                        placeholder="Password"
                        id="password"
                        name="password"
                        icon="isax-b icon-lock"
                        required
                        v-model="form.password"
                        :error="form.errors.password"
                    />
                    <ButtonYellow
                        type="submit"
                        :disabled="
                            !form.email || !form.password || form.processing
                        "
                        :loading="form.processing"
                        class="w-full block py-3 font-krub-medium uppercase"
                    >
                        SIGN IN
                    </ButtonYellow>
                </form>
            </div>
        </section>
    </AuthLayout>
</template>
<script setup lang="ts">
import AuthLayout from "@/layouts/AuthLayout.vue";
import Input from "@/components/input/Index.vue";
import InputPassword from "@/components/input/Password.vue";
import ButtonYellow from "@/components/button/ButtonYellow.vue";
import { useForm } from "@inertiajs/vue3";

defineProps(["menus"]);
const form = useForm({
    email: "",
    password: "",
});

const submit = () => {
    if (!form.processing) {
        form.post(route("admin.auth.login.store"), {
            onSuccess: () => {
            },
        });
    }
};
</script>
