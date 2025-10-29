<template>
    <AppLayout title="Setting">
        <div class="p-5 max-w-3xl mx-auto">
            <TabMenu active="personal" />

            <div class="rounded-sm overflow-hidden mt-5">
                <div class="bg-[#EBEBEB] px-6 py-2">
                    <h2 class="text-neutral-80 font-bold text-base">Account Information</h2>
                </div>
                <form class="p-6 border border-[#EBEBEB] rounded-sm bg-white" @submit.prevent="submit">
                    <h2 class="text-darkest font-semibold mb-3">Personal Information</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 items-center">
                        <p class="text-dark-grey text-xs font-medium mb-5">Fullname</p>
                        <div class="lg:col-span-2">
                            <Input
                                v-model="form.fullname"
                                :value="form.fullname"
                                :error="form.errors.fullname"
                                placeholder="Fullname"
                                maxlength="50"
                            />
                        </div>
                    </div>

                    <div class="h-[1px] w-full bg-[#D3DCE4] my-4"></div>

                    <h2 class="text-darkest font-semibold mb-3">Security</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 items-center">
                        <p class="text-dark-grey text-xs font-medium mb-5">Current Password</p>
                        <div class="lg:col-span-2">
                            <InputPassword
                                v-model="form.current_password"
                                :value="form.current_password"
                                :error="form.errors.current_password"
                                placeholder="Current Password"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 items-center">
                        <p class="text-dark-grey text-xs font-medium mb-5">New Password</p>
                        <div class="lg:col-span-2">
                            <InputPassword
                                v-model="form.new_password"
                                :value="form.new_password"
                                :error="form.errors.new_password"
                                placeholder="New Password"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 items-center">
                        <p class="text-dark-grey text-xs font-medium mb-5">Confirm New Password</p>
                        <div class="lg:col-span-2">
                            <InputPassword
                                v-model="form.confirm_new_password"
                                :value="form.confirm_new_password"
                                :error="form.errors.confirm_new_password"
                                placeholder="Confirm New Password"
                                help="Minimum 8 characters including capital & small letters (A-Z), (a-z), and numbers (1-9)"
                            />
                        </div>
                    </div>

                    <ButtonPrimary
                        class="mt-4 ms-auto"
                        type="submit"
                        :loading="form.processing"
                        :disabled="
                            !form.fullname.trim()
                            || (form.current_password.trim() && (!form.new_password.trim() || !form.confirm_new_password.trim()))
                            || (form.new_password.trim() && (!form.current_password.trim() || !form.confirm_new_password.trim()))
                            || (form.confirm_new_password.trim() && (!form.current_password.trim() || !form.new_password.trim()))
                            || form.processing
                        "
                    >
                        Submit
                    </ButtonPrimary>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import TabMenu from "./TabMenu.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import Input from "@/Components/Input/Index.vue";
import InputPassword from "@/Components/Input/Password.vue";
import ButtonPrimary from "@/Components/Button/ButtonPrimary.vue";

const form = useForm({
    fullname: usePage().props.auth.user.name,
    current_password: '',
    new_password: '',
    confirm_new_password: ''
})

const submit = () => {
    if (!form.processing) {
        form.post(route("api.setting.personal-setting"), {
            onSuccess: () => {
                (document.getElementById('header-user-name') as HTMLElement).innerHTML = form.fullname
                form.current_password = ''
                form.new_password = ''
                form.confirm_new_password = ''
            }
        });
    }
}
</script>
