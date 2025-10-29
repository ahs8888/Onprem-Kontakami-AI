<template>
    <AppLayout title="Recording Analysis">
        <TabMenu active="agent-scoring" :tabs="getTabMenuRecordingAnalysis()" />

        <section x-data="{confirmation:false}">
            <div class="flex justify-between">
                <TableSearch />
                <div class="flex items-center gap-2">
                    <ButtonYellow :href="route('setup.recording-analysis.agent-scoring.create')" icon="isax icon-add"> Add Scoring </ButtonYellow>
                    <FilterRecordingScoring :reset="route('setup.recording-analysis.agent-scoring.index')" />
                </div>
            </div>
            <div class="bg-alert border rounded-md px-3 py-2 text-[11px] mb-2" v-if="loading">
                Downloading the analysis will take a while, please wait until the download is complete.
            </div>

            <Table :columns="['Date', 'Scoring Name', 'Folder Name', 'Total Data', 'Status', 'Action']" :paginate="paginate" class="table-action">
                <tr
                    v-for="(row, index) in paginate.data.value"
                    :key="index"
                    class="cursor-pointer"
                    :class="{
                        'bg-[#E6F5EA]': row.is_new,
                        'bg-[#ff7c7c30]': row.is_failed,
                    }"
                    @click="router.visit(route('setup.recording-analysis.agent-scoring.show', row.uuid))"
                >
                    <Td class="whitespace-nowrap">
                        {{ row.created_at }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.analysis_name }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.foldername }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.total_data }}
                    </Td>
                    <Td
                        class="font-bold whitespace-nowrap text-blue-500 capitalize"
                        v-bind:class="{
                            'text-red': row.is_failed,
                            'text-success': row.status == 'done',
                            'text-blue-500': row.status == 'queue',
                            ' text-yellow-600': row.status == 'progress',
                        }"
                    >
                        {{ row.status }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        <DropdownAction @click.stop="" v-if="row.status != 'progress'">
                            <DropdownItem
                                type="button"
                                @click="exportAnalysis(row.uuid)"
                                v-if="row.status == 'done'"
                                :disabled="loading"
                                :loading="loading"
                            >
                                <i class="isax icon-document-download me-1"></i>
                                Export Analysis
                            </DropdownItem>
                            <DropdownItem type="button" @click="retry(row.uuid)" v-if="row.is_failed">
                                <i class="isax icon-refresh me-1"></i>
                                Retry
                            </DropdownItem>
                            <DropdownItem class="text-red" @click="showConfirmDelete(row.uuid,row.analysis_name)" x-on:click="confirmation=!confirmation">
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
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import TabMenu from '@/components/button/TabMenu.vue';
import TableSearch from '@/components/table/TableSearch.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Table from '@/components/table/Table.vue';
import Td from '@/components/table/Td.vue';
import DropdownAction from '@/components/dropdown/DropdownAction.vue';
import DropdownItem from '@/components/dropdown/DropdownItem.vue';
import DeleteConfirmation from '@/components/popup/DeleteConfirmation.vue';
import { getTabMenuRecordingAnalysis } from '@/util';
import { ref } from 'vue';
import { useForm,router } from '@inertiajs/vue3';
import axios from 'axios';
import { getAllQueryParamToPost, showAlert } from '@/composables/global-function';
import { usePaginate } from '@/composables/usePaginate';
import FilterRecordingScoring from '@/components/popup/FilterRecordingScoring.vue';

const paginate = usePaginate({
    route: route('setup.recording-analysis.agent-scoring.datatable'),
});

const loading = ref(false);
const selectedID = ref('');
const form = useForm({
    uuid: '',
});
const confirmation = ref('Are you sure you want to delete ?');

const showConfirmDelete = (uuid: string, name: string) => {
    selectedID.value = uuid;
    confirmation.value = `Are you sure you want to delete <b>${name}</b>?`;
};
const deleteAction = (callback: any) => {
    if (selectedID.value) {
        axios.delete(route('setup.recording-analysis.agent-scoring.destroy', selectedID.value)).then(() => {
            callback();
            showAlert('Agent Scoring Successfully deleted !', 'success');
            paginate.fetchData();
        });
    }
};

const retry = (uuid: string) => {
    if (!form.processing) {
        form.uuid = uuid;
        form.post(route('setup.recording-analysis.agent-scoring.retry', uuid));
    }
};

const exportAnalysis = (uuid:string) => {
    if (!loading.value) {
        loading.value = true;
        try {
            axios({
                method: 'post',
                url: route('setup.recording-analysis.agent-scoring.export-scoring', uuid),
                data: getAllQueryParamToPost(),
                responseType: 'blob',
            })
                .then((result) => {
                    if (result.headers.filename) {
                        const blob = new Blob([result.data], {
                            type: 'application/zip' ,
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
