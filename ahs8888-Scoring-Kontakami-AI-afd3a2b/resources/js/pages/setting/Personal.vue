<template>
    <AppLayout title="Setting">
        <section class="mx-auto px-5 pb-5 md:max-w-[70%] md:px-0">
            <TabMenu active="personal-setting" :tabs="tabMenuSettings()" />

            <form @submit.prevent="submit" class="mt-5 rounded-md border bg-white">
                <div class="rounded-t-md bg-[#ddd] px-5 py-2 font-krub-semibold text-[14px]">Account Information</div>
                <div class="px-5 pb-4">
                    <AvatarProfile v-model="form.avatar" @setAvatar="(avatar: string) => (avatarPreview = avatar)" />
                    <div class="border-b py-4">
                        <h2 class="mb-2 font-krub-semibold text-[14px]">Personal Information</h2>
                        <div class="grid md:grid-cols-3">
                            <label for="name" class="mt-2 font-krub-medium text-[12px] text-[#3F4254]"> Fullname </label>

                            <div class="col-span-2">
                                <Input
                                    placeholder="Enter Fullname"
                                    name="name"
                                    id="name"
                                    maxlength="50"
                                    v-model="form.name"
                                    :value="form.name"
                                    :error="form.errors.name"
                                    required
                                />
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3">
                            <label for="email" class="mt-2 font-krub-medium text-[12px] text-[#3F4254]"> Email Address </label>

                            <div class="col-span-2">
                                <Input
                                    placeholder="Enter Email Address"
                                    type="email"
                                    name="email"
                                    id="email"
                                    maxlength="50"
                                    hide-length="true"
                                    v-model="form.email"
                                    :value="form.email"
                                    :error="form.errors.email"
                                    class="disabled:bg-[#f3f3f3]"
                                    disabled
                                />
                            </div>
                        </div>

                        <div class="grid md:grid-cols-3">
                            <label for="name" class="mt-2 font-krub-medium text-[12px] text-[#3F4254]"> Phone Number </label>

                            <div class="col-span-2">
                                <PhoneNumber
                                    placeholder="123xxx"
                                    id="phone"
                                    name="phone"
                                    v-model="form.phone_number"
                                    :value="form.phone_number"
                                    :error="form.errors.phone_number"
                                    :codes="phone_country"
                                    :code="form.phone_code"
                                    maxlength="20"
                                    required
                                    @setCode="(code: string) => (form.phone_code = code)"
                                />
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3">
                            <label for="company" class="mt-2 font-krub-medium text-[12px] text-[#3F4254]"> Company Name </label>

                            <div class="col-span-2">
                                <Input
                                    placeholder="Enter Company Name"
                                    name="company"
                                    id="company"
                                    maxlength="50"
                                    v-model="form.company"
                                    :value="form.company"
                                    :error="form.errors.company"
                                    required
                                />
                            </div>
                        </div>
                    </div>
                    <div class="py-4">
                        <h2 class="mb-2 font-krub-semibold text-[14px]">Security</h2>
                        <div class="grid md:grid-cols-3">
                            <label for="name" class="mt-2 font-krub-medium text-[12px] text-[#3F4254]"> Current Password </label>
                            <div class="col-span-2">
                                <Password
                                    placeholder="Current Password"
                                    id="password"
                                    name="password"
                                    maxlength="50"
                                    v-model="form.password"
                                    :value="form.password"
                                    :error="form.errors.password"
                                />
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3">
                            <label for="name" class="mt-2 font-krub-medium text-[12px] text-[#3F4254]"> New Password </label>
                            <div class="col-span-2">
                                <Password
                                    placeholder="New Password"
                                    id="new_password"
                                    name="new_password"
                                    maxlength="50"
                                    v-model="form.new_password"
                                    :value="form.new_password"
                                    :error="form.errors.new_password"
                                />
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3">
                            <label for="name" class="mt-2 font-krub-medium text-[12px] text-[#3F4254]"> Confirm New Password </label>
                            <div class="col-span-2">
                                <Password
                                    placeholder="Confirm New Password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    help="Minimum 8 characters including capital & small letters (A-Z), (a-z), and numbers (0-9)"
                                    maxlength="50"
                                    v-model="form.confirm_password"
                                    :value="form.confirm_password"
                                    :error="form.errors.confirm_password"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <ButtonYellow class="!px-9 !py-3" type="submit" :loading="form.processing" :disabled="isEmptyValue(form.company) || isEmptyValue(form.name) || form.processing || !form.isDirty || !isValidPasswordChange() || form.confirm_password!=form.new_password"> Submit </ButtonYellow>
                    </div>
                </div>
            </form>
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import TabMenu from '@/components/button/TabMenu.vue';
import Input from '@/components/input/Index.vue';
import Password from '@/components/input/Password.vue';
import PhoneNumber from '@/components/input/PhoneNumber.vue';
import AvatarProfile from '@/components/module/profile/AvatarProfile.vue';
import { isEmptyValue, updateUserHeaderValue } from '@/composables/global-function';
import AppLayout from '@/layouts/AppLayout.vue';
import { tabMenuSettings } from '@/util';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    phone_country: any;
}>();
const user = usePage().props.auth?.user;
const avatarPreview = ref(user.avatar);

const form = useForm({
    avatar: null,
    name: user.name,
    email: user.email,
    phone_number: user.phone_number,
    phone_code: user.phone_code,
    company: user.company,
    password: '',
    confirm_password: '',
    new_password: '',
});

const isValidPasswordChange = () => {
    const values = [form.password.trim(), form.new_password.trim(), form.confirm_password.trim()];
    const allEmpty = values.every((v) => v === '');
    const allFilled = values.every((v) => v !== '');

    return allEmpty || allFilled;
};
const submit = () => {
    if (!form.processing) {
        form.post(route('setting.personal.update-profile'),{
            onSuccess : () => {
                updateUserHeaderValue("name",form.name)
                updateUserHeaderValue("profile",avatarPreview.value )
                form.password = ""
                form.confirm_password = ""
                form.new_password = ""
            }
        });
    }
};
</script>
