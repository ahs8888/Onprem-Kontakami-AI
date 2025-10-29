<template>
    <AppLayout title="Add Recording Scoring" :back="route('setup.recording-analysis.recording-scoring.index')">
        <form @submit.prevent="submit" class="mx-auto px-5 pb-5 md:max-w-[60%] md:px-0">
            <h1 class="mb-2 font-krub-bold text-[17px]">Generate Analysis</h1>
            <div class="rounded-md border bg-white px-4 py-3">
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
                <label for="" class="pre-text-content mb-1 block font-krub-medium text-[12px] text-dark"> Select Recording Text Folder </label>
                <div class="mt-1 mb-2 rounded-md border">
                    <div class="rounded-t-md border-b bg-[#F9FBFC] px-3 py-2 ps-[50px] font-krub-semibold text-[13px]">Folder Name</div>
                    <ul class="max-h-[400px] overflow-auto">
                        <li v-for="(recording, index) in recordings" :key="index">
                            <label :for="`recording_${recording.id}`" class="flex cursor-pointer items-center border-b py-2">
                                <div class="w-[50px] text-center">
                                    <input
                                        type="radio"
                                        :id="`recording_${recording.id}`"
                                        name="recording_id"
                                        v-model="form.recording_id"
                                        :value="recording.id"
                                        class="mt-[-2px] h-[18px] w-[18px] cursor-pointer border-2 !border-yellow shadow-none outline-none checked:bg-yellow"
                                    />
                                </div>
                                <div class="text-[13px]">
                                    {{ recording.folder }}
                                </div>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-5 flex justify-end">
                <ButtonYellow type="submit" :disabled="!form.prompt_id || !form.recording_id || form.processing" :loading="form.processing" class="px-3">
                    Start Analysis
                </ButtonYellow>
            </div>
        </form>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import SelectSearch from '@/components/input/SelectSearch.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

defineProps<{
    prompts: any;
    recordings: any;
}>();

const form = useForm({
    prompt_id: null,
    recording_id: null,
});

const submit = () => {
    if (!form.processing) {
        form.post(route('setup.recording-analysis.recording-scoring.store'));
    }
};
</script>
