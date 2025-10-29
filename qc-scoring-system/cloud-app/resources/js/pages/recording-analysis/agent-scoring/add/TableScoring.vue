<template>
    <section>
        <div class="flex justify-between">
            <TableSearch />
            <div class="flex items-center gap-2">
                <FilterDateRange :reset="route('setup.recording-analysis.agent-scoring.create')" />
            </div>
        </div>
        <Table :columns="['', 'Date', 'Scoring Name', 'Folder Name', 'Prompt', 'Total Data']" :paginate="paginate">
            <tr v-for="(row, index) in paginate.data.value" :key="index" @click="$emit('setAnalylisId', row.uuid)" class="cursor-pointer">
                <Td class="w-[20px]">
                    <div class="border-2 border-yellow rounded-full">
                        <input
                            type="radio"
                            name="type"
                            class="h-[15px] w-[15px] cursor-pointer !border-white shadow-none outline-none checked:!border-[3px]"
                            :value="row.uuid"
                            :checked="selected == row.uuid"
                            @click="$emit('setAnalylisId', row.uuid)"
                        />
                    </div>
                </Td>
                <Td class="whitespace-nowrap">
                    {{ row.created_at }}
                </Td>
                <Td class="whitespace-nowrap">
                    {{ row.name }}
                </Td>
                <Td class="whitespace-nowrap">
                    {{ row.foldername }}
                </Td>
                <Td class="whitespace-nowrap">
                    {{ row.prompt_name }}
                </Td>
                <Td class="whitespace-nowrap">
                    {{ row.total_file }}
                </Td>
            </tr>
        </Table>
    </section>
</template>
<script setup lang="ts">
import FilterDateRange from '@/components/popup/FilterDateRange.vue';
import Table from '@/components/table/Table.vue';
import TableSearch from '@/components/table/TableSearch.vue';
import Td from '@/components/table/Td.vue';
import { usePaginate } from '@/composables/usePaginate';

defineProps<{
    selected: any;
}>();
const paginate = usePaginate({
    route: route('setup.recording-analysis.recording-scoring.datatable'),
    query: {
        'filter[status]': 'done',
    },
});
</script>
