<template>
    <AppLayout title="Setting">
        <section class="mx-auto px-5 pb-5 md:max-w-[70%] md:px-0">
            <TabMenu active="cloud-location" :tabs="tabMenuSettings()" />

            <div class="mt-5 rounded-md border bg-white px-5 py-5">
                <AlertInformation>
                    Please copy token below and paste them to Settings section in Kontakami On Premises (or Kontakami CRM)
                </AlertInformation>

                <div class="mt-4 flex gap-2">
                    <input
                        class="mb-2 min-h-[42px] w-full rounded-lg border px-4 py-2 text-[12px] shadow-none outline-none placeholder:text-[#615e5e]"
                        disabled
                        readonly
                        :value="code"
                    />
                    <button
                        class="!h-[42px] rounded-sm px-6 text-[13px] text-white"
                        v-bind:class="{
                            'bg-yellow': !copied,
                            'bg-success': copied,
                        }"
                        type="button"
                        @click="copy"
                    >
                        {{ copied ? 'Copied' : 'Copy' }}
                    </button>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import TabMenu from '@/components/button/TabMenu.vue';
import AlertInformation from '@/components/card/AlertInformation.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { tabMenuSettings } from '@/util';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const code = ref(usePage().props.auth.user.code);
const copied = ref(false);

const copy = () => {
    copied.value = true;
    navigator.clipboard.writeText(code.value);
    setTimeout(() => {
        copied.value = false;
    }, 900);
};
</script>
