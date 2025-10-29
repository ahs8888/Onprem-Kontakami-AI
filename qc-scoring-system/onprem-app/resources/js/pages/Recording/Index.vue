<template>
    <AppLayout title="Recording List">
        <RecordingList :paginate="paginate" />
    </AppLayout>
</template>
<script setup lang="ts">
import RecordingList from "@/Components/Module/Recording/RecordingList.vue"
import { usePaginate } from "@/Hooks/usePaginate";
import AppLayout from "@/Layouts/AppLayout.vue"

const paginate = usePaginate({
    route: route('api.recordings.datatable')
})
</script>
