<template>
    <AppLayout title="Setting">
        <div class="p-5 max-w-3xl mx-auto">
            <TabMenu active="access-token" />

            <div class="mt-5 p-6 border border-[#EBEBEB] rounded-sm bg-white">
                <div class="flex items-center gap-3 bg-[#3943B70D] border !border-primary px-3 py-2 rounded-sm text-primary text-xs mb-7">
                    <i class="isax-b icon-info-circle text-sm"></i>
                    <span>
                        <strong>API Access Token</strong> allows other systems to securely access your API.
                        This token is confidential and should not be shared with unauthorized parties.
                        You can enter a token manually or click the "Generate" button to create a new one automatically.
                    </span>
                </div>

                <form class="flex items-center gap-2" @submit.prevent="submit">
                    <div class="w-full">
                        <Input
                            v-model="form.access_token"
                            :value="form.access_token"
                            :error="form.errors.access_token"
                            placeholder="Access Token"
                        />
                    </div>
                    <ButtonPrimary
                        @click="generateToken"
                        icon="isax icon-key"
                        class="mb-4"
                    >
                        Generate
                    </ButtonPrimary>

                    <ButtonPrimary
                        class="mb-4"
                        type="submit"
                        :loading="form.processing"
                        :disabled="
                            !form.access_token || form.processing
                        "
                    >Save</ButtonPrimary>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import TabMenu from "./TabMenu.vue";
import { useForm } from "@inertiajs/vue3";
import Input from "@/Components/Input/Index.vue";
import ButtonPrimary from "@/Components/Button/ButtonPrimary.vue";

const props = defineProps(["access_token"])

const form = useForm({
    access_token: props.access_token || ''
})

const submit = () => {
    if (!form.processing) {
        form.post(route("api.setting.access-token"));
    }
}

const generateToken = () => {
    const randomToken = [...Array(32)]
        .map(() => Math.random().toString(36)[2])
        .join('');
    form.access_token = randomToken;
}

</script>
