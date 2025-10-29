<template>
    <div x-data="{autoAnalysis:false}">
        <ButtonYellow type="button" x-on:click="autoAnalysis=!autoAnalysis"> Auto Analysis : {{ auto_prompt ? 'ON' : 'OFF' }} </ButtonYellow>
        <div
            x-show="autoAnalysis"
            class="fixed top-0 left-0 z-[99] flex h-full w-full items-center justify-center"
            x-cloak
            x-init="$watch('autoAnalysis', (value) =>value ? document.getElementById('app-body').classList.add('overflow-hidden') : document.getElementById('app-body').classList.remove('overflow-hidden') )"
        >
            <div
                x-show="autoAnalysis"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-on:click="autoAnalysis = false"
                @click="cancel"
                class="absolute inset-0 max-h-full w-full bg-[#000000b5]"
            ></div>
            <div
                x-show="autoAnalysis"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative max-h-[95%] w-full max-w-md bg-white pt-4 sm:rounded-lg"
            >
                <form @submit.prevent="submit">
                    <div class="flex items-center justify-between px-6 pb-4 font-krub-semibold text-[16px]">
                        <div class="flex gap-2">
                            <h3 class="text-dark">Auto Analysis</h3>
                            <Switch v-model="form.status" />
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                ref="cancelButton"
                                type="button"
                                x-on:click="autoAnalysis = false"
                                class="w-[70px] rounded-lg border bg-white py-2 font-krub-medium text-[12px]"
                                @click="cancel"
                            >
                                Cancel
                            </button>
                            <ButtonYellow
                                type="submit"
                                class="w-[70px]"
                                :loading="form.processing"
                                :disabled="form.processing || (form.status && !form.prompt_id)"
                            >
                                Save
                            </ButtonYellow>
                        </div>
                    </div>
                    <div class="px-6 py-3 pb-6" x-bind:class="{'overflow-auto':!autoAnalysis}" v-if="form.status">
                        <SelectSearch
                            label="Select Prompt"
                            placeholder="Please Select Prompt"
                            v-model="form.prompt_id"
                            :value="auto_prompt?.id"
                            :items="
                                prompts.map((row: any) => {
                                    return {
                                        label: row.name,
                                        value: row.id,
                                    };
                                })
                            "
                        />
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import SelectSearch from '@/components/input/SelectSearch.vue';
import Switch from '@/components/input/Switch.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps<{
    prompts: any;
    auto_prompt: any;
}>();

const cancelButton = ref<HTMLButtonElement | null>(null);
const form = useForm({
    status: props.auto_prompt ? true : false,
    prompt_id: props.auto_prompt?.id,
});

const submit = () => {
    if (!form.processing) {
        form.post(route('setup.recording-analysis.recording-scoring.auto-analysis'), {
            onSuccess: () => {
                cancelButton.value?.click();
            },
        });
    }
};

const cancel = () => {
    // form.status = props.auto_prompt ? true : false
    // form.prompt_id =  props.auto_prompt?.id
}

watch(
    () => form.status,
    () => {
        form.prompt_id = null;
    },
);
</script>
