<template>
    <AppLayout title="Agent Analysis">
        <TabMenu active="prompt" :tabs="tabMenuAgentAnalysis()"/>
         <section x-data="{confirmation:false}">
            <div class="flex justify-between">
                <TableSearch />
                <div class="flex gap-3">
                    <ButtonYellow :href="route('setup.agent-analysis.prompt.choise')" icon="isax icon-add"> Add Prompt </ButtonYellow>
                </div>
            </div>
            <Table :columns="['Date', 'Prompt Name', 'Template Type', 'Action']" :paginate="paginate" class="table-action">
                <tr v-for="(row, index) in paginate.data.value" :key="index">
                    <Td class="whitespace-nowrap">
                        {{ row.created_at }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.name }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.type_label }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        <DropdownAction>
                            <DropdownItem :href="route('setup.agent-analysis.prompt.edit',[row.type,row.uuid])">
                                <i class="isax icon-edit me-1"></i>
                                Edit Prompt
                            </DropdownItem>
                            <DropdownItem class="text-red" @click="selectedID = row.uuid" x-on:click="confirmation=!confirmation">
                                <i class="isax icon-trash me-1"></i>
                                Delete
                            </DropdownItem>
                        </DropdownAction>
                    </Td>
                </tr>
            </Table>

            <Confirmation
                class="delete-confirmation"
                confirmation="Are you sure you want to delete ?"
                @action="deleteAction"
            />
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TabMenu from '@/components/button/TabMenu.vue';
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import DropdownAction from '@/components/dropdown/DropdownAction.vue';
import DropdownItem from '@/components/dropdown/DropdownItem.vue';
import Confirmation from '@/components/popup/Confirmation.vue';
import Table from '@/components/table/Table.vue';
import TableSearch from '@/components/table/TableSearch.vue';
import Td from '@/components/table/Td.vue';
import { showAlert } from '@/composables/global-function';
import { usePaginate } from '@/composables/usePaginate';
import { tabMenuAgentAnalysis } from '@/util';
import axios from 'axios';
import { ref } from 'vue';

defineProps<{
    guideline: string;
}>();

const selectedID = ref(null);
const paginate = usePaginate({
    route: route('setup.agent-analysis.prompt.datatable'),
});

const deleteAction = (callback: any) => {
    if (selectedID.value) {
        axios.delete(route('setup.agent-analysis.prompt.destroy', selectedID.value)).then(() => {
            callback();
            showAlert('Prompt Successfully deleted !', 'success');
            paginate.fetchData();
        });
    }
};
</script>
