<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Total Recordings -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Recordings</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total }}</p>
                </div>
                <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="isax icon-music-circle text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Linked Recordings -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Linked to Tickets</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.linked }}</p>
                    <p class="text-xs text-green-600 mt-1">
                        {{ linkedPercentage }}% complete
                    </p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="isax icon-link text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Linking -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Needs Linking</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.unlinked }}</p>
                    <a 
                        v-if="stats.unlinked > 0"
                        :href="route('ticket-import.index')"
                        class="text-xs text-yellow-600 hover:text-yellow-700 font-medium mt-1 inline-block"
                    >
                        Import tickets →
                    </a>
                </div>
                <div class="h-12 w-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="isax icon-warning-2 text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    stats: {
        total: number;
        linked: number;
        unlinked: number;
    };
}

const props = defineProps<Props>();

const linkedPercentage = computed(() => {
    if (props.stats.total === 0) return 0;
    return Math.round((props.stats.linked / props.stats.total) * 100);
});
</script>
