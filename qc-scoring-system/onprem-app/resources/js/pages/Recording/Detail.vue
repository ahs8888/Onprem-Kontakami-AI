<template>
    <AppLayout title="Recording List">
        <RecordingDetail :paginate="paginate" :data="data" />
    </AppLayout>
</template>
<script setup lang="ts">
import RecordingDetail from "@/Components/Module/Recording/RecordingDetail.vue"
import { usePaginate } from "@/Hooks/usePaginate";
import AppLayout from "@/Layouts/AppLayout.vue"

const props = defineProps(["data"])

const paginate = usePaginate({
    route: route('api.recordings.detail-datatable', props.data.id)
})
</script>
