<template>
    <AppLayout title="Recording Analysis">
        <section x-data="{conversation:false}">
            <Breadcrumb :breadcrumbs="breadcrumbs" />

            <div class="flex justify-between">
                <TableSearch />
            </div>
            <Table :columns="['Date', 'File Name', 'Detail']" :paginate="paginate">
                <tr v-for="(row, index) in paginate.data.value" :key="index">
                    <Td class="whitespace-nowrap">
                        {{ row.created_at }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        {{ row.filename }}
                    </Td>
                    <Td class="whitespace-nowrap">
                        <button
                            type="button"
                            class="font-krub-semibold text-yellow underline"
                            @click="conversations = row.conversations"
                            x-on:click="conversation=true"
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
import PopupRecordingConversation from '@/components/popup/PopupRecordingConversation.vue';
import Table from '@/components/table/Table.vue';
import TableSearch from '@/components/table/TableSearch.vue';
import Td from '@/components/table/Td.vue';
import { usePaginate } from '@/composables/usePaginate';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps<{
    recording: any;
}>();
const paginate = usePaginate({
    route: route('setup.recording-analysis.recording-text.datatable-item', props.recording.uuid),
});
const conversations = ref([]);
const breadcrumbs = [
    {
        label: 'Recording Text',
        route: route('setup.recording-analysis.recording-text.index'),
    },
    {
        label: props.recording.folder,
        route: null,
    },
];
</script>
