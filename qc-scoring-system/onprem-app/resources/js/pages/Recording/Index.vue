<template>
    <AppLayout title="Recording List">
        <!-- NEW: Unlinked Recordings Banner -->
        <div v-if="unlinkedCount > 0" class="mx-5 mt-3 mb-2">
            <div class="flex items-center gap-3 bg-yellow-50 border border-yellow-300 px-4 py-3 rounded-lg">
                <i class="isax icon-info-circle text-yellow-600 text-xl"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-yellow-800">
                        {{ unlinkedCount }} recording{{ unlinkedCount > 1 ? 's are' : ' is' }} waiting to be linked to tickets.
                    </p>
                    <p class="text-xs text-yellow-700 mt-1">
                        Link recordings to tickets to enable better analysis and tracking.
                    </p>
                </div>
                <a 
                    href="#" 
                    class="text-sm font-medium text-yellow-700 hover:text-yellow-900 underline whitespace-nowrap"
                >
                    Link Now →
                </a>
            </div>
        </div>
        
        <RecordingList :paginate="paginate" />
    </AppLayout>
</template>
<script setup lang="ts">
import RecordingList from "@/Components/Module/Recording/RecordingList.vue"
import { usePaginate } from "@/Hooks/usePaginate";
import AppLayout from "@/Layouts/AppLayout.vue"

// NEW: Receive unlinkedCount from controller
defineProps<{
    unlinkedCount?: number
}>()

const paginate = usePaginate({
    route: route('api.recordings.datatable')
})
</script>
