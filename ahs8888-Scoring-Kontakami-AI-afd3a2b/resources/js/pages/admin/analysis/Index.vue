<template>
    <AppLayout title="AI Call Data Record">
        <h2 class="mt-[-4px] font-krub-bold text-[14px]">Recording List</h2>
        <section x-data="{confirmation:false}" class="mt-2">
            <div class="flex justify-between">
                <TableSearch />
                <div class="flex gap-3">
                    <ButtonExport :action="route('admin.analysis-record.export')" />
                    <FilterAdminRecording :reset="route('admin.analysis-record.index')" />
                </div>
            </div>

            <Table
                :columns="['Date', 'Email Address', 'Company Name', 'Folder Name', 'Total Data', 'Total Token', 'Action Type']"
                :paginate="paginate"
            >
                <tr v-for="(row, index) in paginate.data.value" :key="index">
                    <Td class="whitespace-nowrap">
                        {{ row.created_at }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.email }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.company }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.folder }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.data }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.token }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        <span v-if="row.type=='vtt'">Voice to text</span>
                        <span v-if="row.type=='rs'">Recording Scoring</span>
                        <span v-if="row.type=='as'">Agent Scoring</span>
                    </Td>
                </tr>
            </Table>
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonExport from '@/components/button/ButtonExport.vue';
import FilterAdminRecording from '@/components/popup/FilterAdminRecording.vue';
import Table from '@/components/table/Table.vue';
import TableSearch from '@/components/table/TableSearch.vue';
import Td from '@/components/table/Td.vue';
import { usePaginate } from '@/composables/usePaginate';
import AppLayout from '@/layouts/AppLayout.vue';

const paginate = usePaginate({
    route: route('admin.analysis-record.datatable'),
});
</script>
