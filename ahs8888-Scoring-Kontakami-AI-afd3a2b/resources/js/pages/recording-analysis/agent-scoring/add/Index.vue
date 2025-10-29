<template>
    <AppLayout title="Add Files" :back="route('setup.recording-analysis.agent-scoring.index')">
        <form @submit.prevent="submit" class="mx-auto md:max-w-[80%]">
            <h1 class="mb-2 font-krub-bold text-[17px]">Select Recording Scoring Folder</h1>
            <TableScoring @setAnalylisId="(uuid: string) => (form.analysis_uuid = uuid)" :selected="form.analysis_uuid" />
            <div class="flex items-center justify-between mt-5 mb-1">
                <label class="pre-text-content  block items-center font-krub-medium text-[14px] text-dark"> Additional File </label>
                <a :href="template" target="_blank" class="text-[12px] text-yellow underline">
                    <i class="isax icon-document-download"></i>
                    Download CSV Template
                </a>
            </div>
            <div class="table-main main-datatable overflow-auto rounded-md border">
                <table class="w-full table-auto border-collapse text-sm">
                    <tr v-if="!form.file">
                        <Td class="py-4 !text-center text-[14px]"> No Files </Td>
                    </tr>
                    <tr v-else>
                        <Th>
                            <div class="flex items-center justify-between font-normal">
                                {{ (form.file as any).name }}
                                <ButtonIconDelete class="!text-[11px]" @click="deleteFile()" />
                            </div>
                        </Th>
                    </tr>
                    <tr v-if="!form.file">
                        <Th>
                            <div class="flex justify-end">
                                <button type="button" class="flex items-center gap-1 text-yellow" @click="chooseFile">
                                    <i class="isax-b icon-add text-[15px]"></i>
                                    Add External Files
                                </button>
                            </div>
                        </Th>
                    </tr>
                </table>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <ButtonOutlineGrey class="w-[100px]" :href="route('setup.recording-analysis.agent-scoring.index')"> Cancel </ButtonOutlineGrey>
                <ButtonYellow type="submit" :disabled="form.processing || !form.file || !form.analysis_uuid" :loading="form.processing" class="px-4">
                    Start Analysis
                </ButtonYellow>
            </div>
            <input type="file" ref="fileExternalInput" accept=".csv" @change="handleExternalFile" class="hidden" />
        </form>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonIconDelete from '@/components/button/ButtonIconDelete.vue';
import ButtonOutlineGrey from '@/components/button/ButtonOutlineGrey.vue';
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import Td from '@/components/table/Td.vue';
import Th from '@/components/table/Th.vue';
import { showAlert } from '@/composables/global-function';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import TableScoring from './TableScoring.vue';

defineProps<{
    template: string;
}>();

const fileExternalInput = ref<HTMLInputElement | null>(null);
const form = useForm({
    file: null,
    analysis_uuid: '',
});

const chooseFile = () => {
    fileExternalInput.value?.click();
};

const handleExternalFile = (event: any) => {
    const file = event.target.files[0];
    if (file) {
        if (!file.name.endsWith('.csv')) {
            showAlert('Only .csv files are allowed.');
            return;
        }

        form.file = file;
    }
};

const deleteFile = () => {
    form.file = null;
    if(fileExternalInput.value){
        fileExternalInput.value.value = ''
    }
};

const submit = () => {
    if (!form.processing) {
        form.post(route('setup.recording-analysis.agent-scoring.analysis'));
    }
};
</script>
