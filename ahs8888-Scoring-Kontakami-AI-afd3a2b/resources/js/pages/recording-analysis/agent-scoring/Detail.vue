<template>
    <AppLayout title="Agent Scoring">
        <section x-data="{agentFile:false}">
            <Breadcrumb :breadcrumbs="breadcrumbs"> </Breadcrumb>
            <div class="flex justify-between">
                <TableSearch />
                <!-- <div class="flex items-center gap-2">
                    <FilterScore :reset="route('setup.recording-analysis.agent-scoring.show', scoring.uuid)" />
                </div> -->
            </div>

            <Table :columns="['Date', 'Agent Name', 'Supervisor', 'Total File', 'Status', 'Detail']" :paginate="paginate">
                <tr v-for="(row, index) in paginate.data.value" :key="index" class="cursor-pointer">
                    <Td class="whitespace-nowrap">
                        {{ row.created_at }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.agent || '-' }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.spv || '-' }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        <button
                            type="button"
                            x-on:click="agentFile=true"
                            @click="itemFiles = row.files"
                            class="font-krub-semibold text-yellow underline"
                        >
                            View File
                        </button>
                    </Td>
                    <Td class="whitespace-nowrap">
                        <span class="text-success" v-if="row.is_done">Done</span>
                        <span class="text-yellow-600" v-if="!row.is_done && scoring.status == 'progress'">Progress</span>
                        <span class="text-blue-500" v-if="!row.is_done && scoring.status == 'queue'">Queue</span>
                        <span class="text-red" v-if="!row.is_done && scoring.status == 'failed'">Failed</span>
                    </Td>
                    <!-- <Td>
                        <span
                            v-bind:class="{
                                'text-red': row.avg_score <= 50,
                                'text-yellow-600': row.avg_score > 50 && row.avg_score <= 75,
                                'text-success': row.avg_score > 75,
                            }"
                        >
                            {{ Math.round(row.avg_score) }}%
                        </span>
                    </Td> -->
                    <Td class="whitespace-nowrap">
                        <button
                            type="button"
                            @click="
                                row.is_done ? openDetail(route('setup.recording-analysis.agent-scoring.scoring', [scoring.uuid, row.uuid])) : () => {}
                            "
                            class="font-krub-semibold text-yellow underline"
                            v-if="row.is_done"
                        >
                            Detail
                        </button>
                    </Td>
                </tr>
            </Table>
            <PopupFileAgentScoring label="View File" :files="itemFiles" />
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import Breadcrumb from '@/components/etc/Breadcrumb.vue';
import PopupFileAgentScoring from '@/components/module/scoring/PopupFileAgentScoring.vue';
// import FilterScore from '@/components/popup/FilterScore.vue';
import Table from '@/components/table/Table.vue';
import TableSearch from '@/components/table/TableSearch.vue';
import Td from '@/components/table/Td.vue';
import { usePaginate } from '@/composables/usePaginate';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps<{
    scoring: any;
}>();
const paginate = usePaginate({
    route: route('setup.recording-analysis.agent-scoring.datatable-item', props.scoring.uuid),
});
const itemFiles = ref([]);
const breadcrumbs = [
    {
        label: 'Agent Scoring',
        route: route('setup.recording-analysis.agent-scoring.index'),
    },
    {
        label: `${props.scoring.analysis_name} - ${props.scoring.foldername}`,
        route: null,
    },
];

const openDetail = (url: string) => {
    window.open(url);
};
</script>
