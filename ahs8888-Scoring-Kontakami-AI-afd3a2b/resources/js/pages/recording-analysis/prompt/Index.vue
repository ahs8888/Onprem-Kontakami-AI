<template>
    <AppLayout :title="is_admin ? 'Prompt Setup' : 'Recording Analysis'">
        <TabMenu active="prompt" :tabs="getTabMenuRecordingAnalysis(is_admin)" v-if="!is_admin" />
        <section x-data="{confirmation:false}">
            <div class="flex justify-between">
                <TableSearch />
                <div class="flex gap-3">
                    <FilterDateRange :reset="route('setup.recording-analysis.prompt.index')" />
                    <ButtonYellow :href="route('setup.recording-analysis.prompt.choise')" icon="isax icon-add"> Add Prompt </ButtonYellow>
                </div>
            </div>
            <Table :columns="columns" :paginate="paginate" class="table-action">
                <tr v-for="(row, index) in paginate.data.value" :key="index">
                    <Td class="whitespace-nowrap">
                        {{ row.created_at }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.name }}
                    </Td>
                    <Td class="whitespace-nowrap" v-if="is_admin">
                        {{ row.company }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.type_label }}
                    </Td>
                    <Td>
                        <div class="flex justify-end" v-if="!row.by_admin || is_admin">
                            <div
                                class="flex w-fit items-center gap-1 rounded-sm border border-[#FFB00F] bg-[#FFF9EA] px-2 py-[2px] text-[11px]"
                                v-if="row.in_use"
                            >
                                <i class="isax-b icon-info-circle text-[17px] text-[#FFB00F]"></i>
                                <span>Data Analysis in progress using this prompt</span>
                            </div>
                            <DropdownAction>
                                <DropdownItem :href="route('setup.recording-analysis.prompt.edit', [row.type, row.uuid])">
                                    <i class="isax icon-edit me-1"></i>
                                    Edit Prompt
                                </DropdownItem>
                                <DropdownItem class="text-red" @click="showConfirmDelete(row.uuid, row.name)" x-on:click="confirmation=!confirmation">
                                    <i class="isax icon-trash me-1"></i>
                                    Delete
                                </DropdownItem>
                            </DropdownAction>
                        </div>
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
import DropdownAction from '@/components/dropdown/DropdownAction.vue';
import DropdownItem from '@/components/dropdown/DropdownItem.vue';
import DeleteConfirmation from '@/components/popup/DeleteConfirmation.vue';
import FilterDateRange from '@/components/popup/FilterDateRange.vue';
import Table from '@/components/table/Table.vue';
import TableSearch from '@/components/table/TableSearch.vue';
import Td from '@/components/table/Td.vue';
import { showAlert } from '@/composables/global-function';
import { usePaginate } from '@/composables/usePaginate';
import AppLayout from '@/layouts/AppLayout.vue';
import { getTabMenuRecordingAnalysis } from '@/util';
import axios from 'axios';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    is_admin: boolean;
}>();

const confirmation = ref('Are you sure you want to delete ?');
const columns = ref(['Date', 'Prompt Name', 'Template Type', 'Action']);
const selectedID = ref('');
const paginate = usePaginate({
    route: route('setup.recording-analysis.prompt.datatable'),
});

const deleteAction = (callback: any) => {
    if (selectedID.value) {
        axios.delete(route('setup.recording-analysis.prompt.destroy', selectedID.value)).then(() => {
            callback();
            showAlert('Prompt Successfully deleted !', 'success');
            paginate.fetchData();
        });
    }
};

const showConfirmDelete = (uuid: string, name: string) => {
    selectedID.value = uuid;
    confirmation.value = `Are you sure you want to delete <b>${name}</b>?`;
};

onMounted(() => {
    if (props.is_admin) {
        columns.value = ['Date', 'Prompt Name', 'Company Name', 'Template Type', 'Action'];
    }
});
</script>
