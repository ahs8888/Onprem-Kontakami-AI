<template>
    <AppLayout title="Add Profiling" :back="route('setup.agent-analysis.profiling.index')">
        <section class="mx-auto md:max-w-[60%]">
            <div class="rounded-md border bg-white px-5 py-3 pb-5">
                <label class="pre-text-content mb-1 flex items-center gap-1 font-krub-medium text-[14px] text-dark"> File </label>

                <div class="table-main main-datatable overflow-auto rounded-md border">
                    <table class="w-full table-auto border-collapse text-sm">
                        <!-- <tr>
                            <Td class="py-4 !text-center text-[14px]"> No Files </Td>
                        </tr> -->
                        <tr>
                            <Td>
                                <div class="flex items-center justify-between">
                                    <strong>Foldername</strong>
                                    <ButtonIconDelete class="!text-[11px]" />
                                </div>
                            </Td>
                        </tr>
                        <tr>
                            <Td>
                                <div class="flex items-center justify-between">
                                    filanane_asdas_ASd.wav
                                    <ButtonIconDelete class="!text-[11px]" />
                                </div>
                            </Td>
                        </tr>
                        <tr>
                            <Td>
                                <div class="flex items-center justify-between">
                                    filanane_asdas_ASd.wav
                                    <ButtonIconDelete class="!text-[11px]" />
                                </div>
                            </Td>
                        </tr>
                        <tr>
                            <Td>
                                <div class="flex items-center justify-between">
                                    <strong>book1_asdasad_AS.xlsx</strong>
                                    <ButtonIconDelete class="!text-[11px]" />
                                </div>
                            </Td>
                        </tr>
                        <tr>
                            <Th>
                                <div class="flex justify-end">
                                    <Dropdown>
                                        <button
                                            type="button"
                                            class="flex items-center gap-1 text-yellow uppercase"
                                            x-on:click="dropdownOpen=!dropdownOpen"
                                            x-ref="buttonDropdown"
                                        >
                                            <i class="isax-b icon-add text-[15px]"></i>
                                            Add Files
                                        </button>
                                        <template #items>
                                            <DropdownItem class="font-normal">
                                                <i class="isax icon-import-2 me-1"></i>
                                                From Internal
                                            </DropdownItem>
                                            <DropdownItem class="font-normal" @click="chooseFile">
                                                <i class="isax icon-export-3 me-1"></i>
                                                From External
                                            </DropdownItem>
                                        </template>
                                    </Dropdown>
                                </div>
                            </Th>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <ButtonOutlineGrey class="w-[100px]" :href="route('setup.agent-analysis.profiling.index')"> Cancel </ButtonOutlineGrey>
                <ButtonYellow type="submit" :disabled="form.processing || !form.files.length" :loading="form.processing" class="px-4">
                    Start Analysis
                </ButtonYellow>
            </div>
            <input type="file" ref="fileExternalInput" accept=".xlsx" @change="handleExternalFile" class="hidden" />
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonIconDelete from '@/components/button/ButtonIconDelete.vue';
import ButtonOutlineGrey from '@/components/button/ButtonOutlineGrey.vue';
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import Dropdown from '@/components/dropdown/Dropdown.vue';
import DropdownItem from '@/components/dropdown/DropdownItem.vue';
import Td from '@/components/table/Td.vue';
import Th from '@/components/table/Th.vue';
import { showAlert } from '@/composables/global-function';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const fileExternalInput = ref<HTMLButtonElement | null>(null);
const form = useForm({
    files: [],
});

const chooseFile = () => {
    fileExternalInput.value?.click();
};
const handleExternalFile = (event: any) => {
    const file = event.target.files[0];
    if (file) {
        if (!file.name.endsWith('.xlsx')) {
            showAlert('Only .xlsx files are allowed.');
            return;
        }

        (form.files as any).push({
            type: 'external',
            name : file.name,
            file : file,
            analysis_scoring_id : null
        });
        console.log('Selected file:', file);
    }
};
</script>
