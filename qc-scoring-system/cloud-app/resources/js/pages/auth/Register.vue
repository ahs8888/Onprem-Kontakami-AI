<template>
    <AuthLayout title="Register">
        <form
            @submit.prevent="submit"
            class="z-[2] w-[95%] rounded-lg border bg-white px-7 py-5 shadow-lg md:w-[30%] md:px-10 md:py-8"
            x-data="{popup : false}"
        >
            <h1 class="font-krub-bold text-[24px] text-dark">Register</h1>
            <p class="mb-4 text-[13px] text-dark">to user</p>
            <Input
                type="text"
                placeholder="Fullname"
                id="name"
                name="name"
                :icon-component="IconFullname"
                required
                v-model="form.name"
                maxlength="50"
                hideLength="true"
                :error="form.errors.name"
            />
            <Input
                type="email"
                placeholder="Email Address"
                id="email"
                name="email"
                icon="isax-b icon-sms"
                required
                v-model="form.email"
                :error="form.errors.email"
            />

            <PhoneNumber
                placeholder="Phone Number"
                id="phone_number"
                name="phone_number"
                icon="isax-b icon-call"
                required
                v-model="form.phone_number"
                :error="form.errors.phone_number"
                :codes="phone_country"
                :code="form.phone_code"
                maxlength="20"
                @setCode="(code: string) => (form.phone_code = code)"
            />
            <Input
                type="text"
                placeholder="Company Name"
                id="company_name"
                name="company_name"
                :icon-component="IconCompanyName"
                required
                v-model="form.company_name"
                :error="form.errors.company_name"
            />

            <InputPassword
                placeholder="Password"
                id="password"
                name="password"
                :icon-component="IconPassword"
                required
                v-model="form.password"
                :error="form.errors.password"
            />
            <InputPassword
                placeholder="Confirm Password"
                id="confirm_password"
                name="confirm_password"
                :icon-component="IconPassword"
                help="Minimum 8 characters including capital & small letters (A-Z), (a-z), and numbers (1-9)"
                required
                v-model="form.confirm_password"
                :error="form.errors.confirm_password"
            />

            <div class="mt-4 mb-4 flex">
                <input
                    name="agreement"
                    id="agreement"
                    type="checkbox"
                    v-model="form.agreement"
                    value="1"
                    class="checkbox-shadow mt-1 rounded border-neutral-300 bg-yellow focus:ring-3 focus:ring-blue-300"
                    required
                />
                <label for="agreement" class="ml-2 font-krub-medium text-[12px] text-gray-900">
                    By signing up you understand and agree to Kontakami
                    <div class="inline">
                        <a href="javascript:;" x-on:click="popup=true" @click="viewPopup('term')" class="text-yellow underline">
                            Terms and Conditions
                        </a>
                    </div>
                    and
                    <div class="inline">
                        <a href="javascript:;" x-on:click="popup=true" @click="viewPopup('privacy')" class="text-yellow underline">
                            Privacy Policies
                        </a>
                    </div>
                </label>
            </div>
            <ButtonYellow
                type="submit"
                :disabled="
                    isEmptyValue(form.name) ||
                    isEmptyValue(form.email) ||
                    isEmptyValue(form.password) ||
                    isEmptyValue(form.confirm_password) ||
                    isEmptyValue(form.phone_number) ||
                    isEmptyValue(form.company_name) ||
                    form.password != form.confirm_password ||
                    !form.agreement ||
                    form.processing
                "
                :loading="form.processing"
                class="block w-full py-3 font-krub-medium uppercase"
            >
                Register
            </ButtonYellow>
            <p class="mt-4 mb-4 text-center font-krub-medium text-[14px]">
                Already have an account?
                <Link :href="route('auth.login.index')" class="text-yellow underline"> Login </Link>
            </p>

            <Popup :title="popup.title" class="sm:max-w-3xl">
                <span v-html="popup.content" class="text-[11px]"></span>
            </Popup>
        </form>
    </AuthLayout>
</template>
<script setup lang="ts">
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import Input from '@/components/input/Index.vue';
import InputPassword from '@/components/input/Password.vue';
import PhoneNumber from '@/components/input/PhoneNumber.vue';
import Popup from '@/components/popup/Index.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import IconFullname from '@/components/icon/input/IconFullname.vue';
import IconPassword from '@/components/icon/input/IconPassword.vue';
import IconCompanyName from '@/components/icon/input/IconCompanyName.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { isEmptyValue } from '@/composables/global-function';

const props = defineProps(['phone_country', 'term_condition', 'privacy_policy']);
const form = useForm({
    name: '',
    email: '',
    phone_number: '',
    phone_code: '62',
    company_name: '',
    password: '',
    confirm_password: '',
    agreement: false,
});

const popup = ref({
    title: '',
    content: '',
});

const viewPopup = (type: string) => {
    if (type == 'term') {
        popup.value = {
            title: 'Terms and Conditions',
            content: props.term_condition,
        };
    } else {
        popup.value = {
            title: 'Privacy Policies',
            content: props.privacy_policy,
        };
    }
};
const submit = () => {
    if (!form.processing) {
        form.post(route('auth.register.store'));
    }
};
</script>
