<template>
    <AppLayout title="Setting">
        <div class="p-5 max-w-3xl mx-auto">
            <TabMenu active="clouds" />

            <div class="mt-5 p-6 border border-[#EBEBEB] rounded-sm bg-white">
                <div class="flex items-center gap-3 bg-[#3943B70D] border !border-primary px-3 py-2 rounded-sm text-primary text-xs mb-7">
                    <i class="isax-b icon-info-circle text-sm"></i>
                    <span>Please find and copy token from Settings section in Kontakami.ai and paste them here</span>
                </div>

                <form class="flex items-center gap-2" @submit.prevent="submit">
                    <div class="w-full">
                        <Input
                            v-model="form.token"
                            :value="form.token"
                            :error="form.errors.token"
                            placeholder="Token"
                        />
                    </div>

                    <ButtonPrimary
                        class="mb-4"
                        type="submit"
                        :loading="form.processing"
                        :disabled="
                            !form.token || form.processing
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

const props = defineProps(["token"])

const form = useForm({
    token: props.token || ''
})

const submit = () => {
    if (!form.processing) {
        form.post(route("api.setting.clouds-location"));
    }
}
</script>
