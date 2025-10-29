<template>
    <AppLayout title="Add Recording Scoring" :back="route('setup.recording-analysis.recording-text.index')">
        <form @submit.prevent="submit" class="mx-auto px-5 pb-5 md:max-w-[60%] md:px-0">
            <h1 class="mb-2 font-krub-bold text-[17px]">Generate Analysis</h1>
            <div class="rounded-md border bg-white px-4 py-3">
                <Input label="Recording Folder" disabled :value="recording.folder" class="bg-[#ebebeb]" />
                <Input label="Scoring Name" placeholder="Scoring Name" v-model="form.name" />
                <SelectSearch
                    label="Choose Prompt"
                    placeholder="Choose"
                    v-model="form.prompt_id"
                    :items="
                        prompts.map(function (row: any) {
                            return {
                                value: row.id,
                                label: row.name,
                            };
                        })
                    "
                />
                <label for="" class="pre-text-content block font-krub-medium text-[12px] text-dark">Auto Analysis </label>
                <span class="inline-block text-[11px]"> 
                    Auto Analysis activation will automatically analize additional future recordings    
                </span>
                <div class="mt-2 w-fit">
                    <Switch v-model="form.auto" />
                </div>
            </div>
            <div class="mt-5 flex justify-end">
                <ButtonYellow type="submit" :disabled="!form.prompt_id || isEmptyValue(form.name) || form.processing" :loading="form.processing" class="px-3">
                    Start Analysis
                </ButtonYellow>
            </div>
        </form>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import Input from '@/components/input/Index.vue';
import SelectSearch from '@/components/input/SelectSearch.vue';
import Switch from '@/components/input/Switch.vue';
import { isEmptyValue } from '@/composables/global-function';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    prompts: any;
    recording: any;
}>();

const form = useForm({
    recording_id: props.recording.id,
    prompt_id: null,
    name: null,
    auto: false,
});
const submit = () => {
    if (!form.processing) {
        form.post(route('setup.recording-analysis.recording-text.add-scoring'));
    }
};
</script>
