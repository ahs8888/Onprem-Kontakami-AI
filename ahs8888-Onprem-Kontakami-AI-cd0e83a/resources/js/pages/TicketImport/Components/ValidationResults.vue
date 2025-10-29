<template>
    <div>
        <h2 class="text-2xl font-bold mb-4">Step 3: Validation Results</h2>
        <p class="text-gray-600 mb-6">
            Review the validation results before importing.
        </p>

        <!-- Summary Cards -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium">Ready to Link</p>
                        <p class="text-3xl font-bold text-green-700 mt-1">
                            {{ matchedCount }}
                        </p>
                    </div>
                    <i class="isax icon-tick-circle text-4xl text-green-600"></i>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-yellow-600 font-medium">Not Found</p>
                        <p class="text-3xl font-bold text-yellow-700 mt-1">
                            {{ unmatchedCount }}
                        </p>
                    </div>
                    <i class="isax icon-danger text-4xl text-yellow-600"></i>
                </div>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600 font-medium">Failed</p>
                        <p class="text-3xl font-bold text-red-700 mt-1">
                            {{ failedCount }}
                        </p>
                    </div>
                    <i class="isax icon-close-circle text-4xl text-red-600"></i>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex space-x-8">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="py-3 px-1 border-b-2 font-medium text-sm transition-colors"
                    :class="{
                        'border-blue-600 text-blue-600': activeTab === tab.id,
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== tab.id
                    }"
                >
                    {{ tab.label }} ({{ tab.count }})
                </button>
            </nav>
        </div>

        <!-- Results Table -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Recording Name
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ticket ID
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer Name
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Message
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(result, index) in filteredResults" :key="index">
                            <td class="px-4 py-3 text-sm text-gray-900 font-mono">
                                {{ result.recording_name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ result.ticket_id }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ result.customer_name || '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': result.status === 'matched',
                                        'bg-yellow-100 text-yellow-800': result.status === 'unmatched',
                                        'bg-red-100 text-red-800': result.status === 'failed'
                                    }"
                                >
                                    {{ result.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ result.message }}
                            </td>
                        </tr>
                        <tr v-if="filteredResults.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                No {{ activeTab }} records found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Warning Message -->
        <div v-if="unmatchedCount > 0 || failedCount > 0" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-start">
                <i class="isax icon-info-circle text-yellow-600 text-xl mr-3 mt-0.5"></i>
                <div class="flex-1">
                    <h4 class="font-medium text-yellow-800">Some records will not be imported</h4>
                    <p class="text-sm text-yellow-700 mt-1">
                        Only {{ matchedCount }} recordings will be linked to tickets. 
                        <span v-if="unmatchedCount > 0">
                            {{ unmatchedCount }} recording(s) were not found in the system.
                        </span>
                        <span v-if="failedCount > 0">
                            {{ failedCount }} record(s) have validation errors.
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-between">
            <button 
                @click="$emit('back')"
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors"
            >
                ← Back to Mapping
            </button>
            <button 
                @click="handleImport"
                :disabled="matchedCount === 0 || importing"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
            >
                <span v-if="importing">
                    <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Importing...
                </span>
                <span v-else>
                    Import {{ matchedCount }} Recording(s) →
                </span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface ValidationResult {
    recording_name: string;
    ticket_id: string;
    customer_name?: string;
    agent_name?: string;
    call_intent?: string;
    call_outcome?: string;
    ticket_url?: string;
    status: 'matched' | 'unmatched' | 'failed';
    message: string;
    recording_id?: number;
}

interface Props {
    results: ValidationResult[];
}

const props = defineProps<Props>();
const emit = defineEmits(['import', 'back']);

const activeTab = ref('matched');
const importing = ref(false);

const matchedCount = computed(() => 
    props.results.filter(r => r.status === 'matched').length
);

const unmatchedCount = computed(() => 
    props.results.filter(r => r.status === 'unmatched').length
);

const failedCount = computed(() => 
    props.results.filter(r => r.status === 'failed').length
);

const tabs = computed(() => [
    { id: 'matched', label: 'Matched', count: matchedCount.value },
    { id: 'unmatched', label: 'Not Found', count: unmatchedCount.value },
    { id: 'failed', label: 'Failed', count: failedCount.value }
]);

const filteredResults = computed(() => {
    return props.results.filter(r => r.status === activeTab.value);
});

const handleImport = () => {
    if (matchedCount.value === 0) return;
    importing.value = true;
    emit('import');
};
</script>
