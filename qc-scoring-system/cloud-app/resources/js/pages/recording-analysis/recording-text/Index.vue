<template>
    <AppLayout title="Recording Analysis">
        <TabMenu active="recording-text" :tabs="getTabMenuRecordingAnalysis()" />
        <section x-data="{confirmation:false}">
            <div class="flex justify-between">
                <TableSearch />
                <div class="flex items-center gap-2">
                    <!-- <ButtonExport :action="route('setup.recording-analysis.recording-text.export')" /> -->
                    <FilterDateRange :reset="route('setup.recording-analysis.recording-text.index')" />
                </div>
            </div>
            <div class="bg-alert border rounded-md px-3 py-2 text-[11px] mb-2" v-if="loading">
                Downloading the analysis will take a while, please wait until the download is complete.
            </div>

            <Table :columns="['Date', 'Folder Name', 'Total Data', 'Action']" :paginate="paginate" class="table-action">
                <tr
                    v-for="(row, index) in paginate.data.value"
                    :key="index"
                    class="cursor-pointer"
                    :class="{
                        'bg-[#E6F5EA]': row.is_new,
                    }"
                    @click="router.visit(route('setup.recording-analysis.recording-text.show', row.uuid))"
                >
                    <Td class="whitespace-nowrap">
                        {{ row.created_at }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.folder }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.total_file }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        <DropdownAction @click.stop="">
                            <DropdownItem
                                :href="route('setup.recording-analysis.recording-text.scoring', row.uuid)"
                                class="text-yellow"
                                v-if="!row.in_use && !row.analisis_prompt_id"
                            >
                                <i class="isax icon-add me-1"></i>
                                Add Scoring
                            </DropdownItem>
                            <DropdownItem type="button" v-if="!row.in_use && row.analisis_prompt_id" @click.stop="stopAutoAnalysis(row.uuid)">
                                <i class="isax-b icon-stop-circle me-1 text-red"></i>
                                Stop Auto Analysis
                            </DropdownItem>
                            <DropdownItem type="button" class="text-[#7f7f7f1]" v-if="row.in_use">
                                <IconProgress class="ml-[-2px] h-[12px] w-[12px] me-1 animate-spin" />
                                Progress
                            </DropdownItem>
                            <DropdownItem
                                type="button"
                                @click="exportRecording(row.uuid)"
                                :disabled="loading"
                                :loading="loading"
                            >
                                <IconExport class="me-1"/>
                                Export Transcribe
                            </DropdownItem>
                            <DropdownItem class="text-red" @click="showConfirmDelete(row.uuid,row.folder)" x-on:click="confirmation=!confirmation" v-if="!row.in_use">
                                <i class="isax icon-trash me-1"></i>
                                Delete
                            </DropdownItem>
                        </DropdownAction>
                    </Td>
                </tr>
            </Table>

            <DeleteConfirmation class="delete-confirmation" :confirmation="confirmation" @action="deleteAction" />
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import TabMenu from '@/components/button/TabMenu.vue';
import TableSearch from '@/components/table/TableSearch.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import IconExport from '@/components/icon/etc/IconExport.vue';
import Table from '@/components/table/Table.vue';
import Td from '@/components/table/Td.vue';
import DeleteConfirmation from '@/components/popup/DeleteConfirmation.vue';
import DropdownAction from '@/components/dropdown/DropdownAction.vue';
import DropdownItem from '@/components/dropdown/DropdownItem.vue';
import FilterDateRange from '@/components/popup/FilterDateRange.vue';
import IconProgress from '@/components/icon/etc/IconProgress.vue';
import { getTabMenuRecordingAnalysis } from '@/util';
import { usePaginate } from '@/composables/usePaginate';
import { ref } from 'vue';
import { getAllQueryParamToPost, showAlert } from '@/composables/global-function';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const selectedID = ref('');
const loading = ref(false);
const paginate = usePaginate({
    route: route('setup.recording-analysis.recording-text.datatable'),
});
const confirmation = ref('Are you sure you want to delete ?');

const showConfirmDelete = (uuid: string, name: string) => {
    selectedID.value = uuid;
    confirmation.value = `Are you sure you want to delete <b>${name}</b>?`;
};

const deleteAction = (callback: any) => {
    if (selectedID.value) {
        axios.delete(route('setup.recording-analysis.recording-text.destroy', selectedID.value)).then(() => {
            callback();
            showAlert('Recording Text Successfully deleted !', 'success');
            paginate.fetchData();
        });
    }
};

const stopAutoAnalysis = (uuid: string) => {
    axios.post(route('setup.recording-analysis.recording-text.stop-scoring', uuid)).then(() => {
        showAlert('Auto analysis Recording Text Successfully stoped !', 'success');
        paginate.fetchData();
    });
};

const exportRecording = (uuid: string) => {
    if (!loading.value) {
        loading.value = true;
        try {
            axios({
                method: 'post',
                url: route('setup.recording-analysis.recording-text.export-item', uuid),
                data: getAllQueryParamToPost(),
                responseType: 'blob',
            })
                .then((result) => {
                    if (result.headers.filename) {
                        const blob = new Blob([result.data], {
                            type: 'application/zip',
                        });
                        const blobURL = URL.createObjectURL(blob);
                        const anchor = document.createElement('a');
                        anchor.href = blobURL;
                        anchor.download = result.headers.filename;
                        anchor.click();
                        URL.revokeObjectURL(blobURL);
                    } else {
                        showAlert('Failed to download data');
                    }
                    loading.value = false;
                })
                .catch((error) => {
                    console.log(error);
                    loading.value = false;
                    showAlert('Failed to download data');
                });
        } catch (err) {
            console.log(err);
            loading.value = false;
            showAlert('Failed to download data');
        }
    }
};
</script>
