<template>
    <AppLayout title="Recording Scoring">
        <section x-data="{conversation:false}">
            <Breadcrumb :breadcrumbs="breadcrumbs" />
            <div class="flex justify-between">
                <TableSearch />
                <!-- <div class="flex items-center gap-2">
                    <FilterScore :reset="route('setup.recording-analysis.recording-scoring.show', recording.uuid)" />
                </div> -->
            </div>

            <Table :columns="['Date', 'File Name', 'Status', 'Detail']" :paginate="paginate">
                <tr  v-for="(row, index) in paginate.data.value" :key="index">
                    <Td class="whitespace-nowrap">
                        {{ row.created_at }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.filename }}
                    </Td>
                    <!-- <Td>
                        <span
                            v-bind:class="{
                                'text-red': row.avg_score <= 50,
                                'text-yellow-600': row.avg_score > 50 && row.avg_score <= 75,
                                'text-success': row.avg_score > 75,
                            }"
                            v-if="row.is_done"
                        >
                            {{ Math.round(row.avg_score) }}%
                        </span>
                        <span v-else>0%</span>
                    </Td> -->
                    <Td class="whitespace-nowrap">
                        <span class="text-success" v-if="row.is_done">Done</span>
                        <span class="text-yellow-600" v-if="!row.is_done && recording.status == 'progress'">Progress</span>
                        <span class="text-blue-500" v-if="!row.is_done && recording.status == 'queue'">Queue</span>
                        <span class="text-red" v-if="!row.is_done && recording.status == 'failed'">Failed</span>
                    </Td>
                    <Td class="whitespace-nowrap">
                        <button
                            type="button"
                            class="font-krub-semibold text-yellow underline"
                            @click="
                                row.is_done
                                    ? openDetail(route('setup.recording-analysis.recording-scoring.analysis', [recording.uuid, row.uuid]))
                                    : () => {}
                            "
                            v-if="row.is_done"
                        >
                            View
                        </button>
                    </Td>
                </tr>
            </Table>
            <PopupRecordingConversation label="Show text" :conversations="conversations" />
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import Breadcrumb from '@/components/etc/Breadcrumb.vue';
// import FilterScore from '@/components/popup/FilterScore.vue';
import PopupRecordingConversation from '@/components/popup/PopupRecordingConversation.vue';
import TableSearch from '@/components/table/TableSearch.vue';
import Table from '@/components/table/Table.vue';
import Td from '@/components/table/Td.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { usePaginate } from '@/composables/usePaginate';

const props = defineProps<{
    recording: any;
}>();
const paginate = usePaginate({
    route: route('setup.recording-analysis.recording-scoring.datatable-item',props.recording.uuid),
});

const conversations = ref([]);
const breadcrumbs = [
    {
        label: 'Recording Scoring',
        route: route('setup.recording-analysis.recording-scoring.index'),
    },
    {
        label: props.recording.foldername,
        route: null,
    },
];

const openDetail = (url: string) => {
    window.open(url);
};
</script>
