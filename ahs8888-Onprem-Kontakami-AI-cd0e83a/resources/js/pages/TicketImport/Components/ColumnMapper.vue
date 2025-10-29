<template>
    <div>
        <h2 class="text-2xl font-bold mb-4">Step 2: Map Your Columns</h2>
        <p class="text-gray-600 mb-6">
            Match your CSV columns to the required fields. Required fields are marked with *.
        </p>

        <div class="space-y-4">
            <!-- Recording Name (Required) -->
            <div class="grid grid-cols-3 gap-4 items-center p-4 bg-gray-50 rounded-lg">
                <div>
                    <label class="text-sm font-medium text-gray-900">
                        Recording Filename *
                    </label>
                    <p class="text-xs text-gray-500 mt-1">
                        Column containing the recording filename
                    </p>
                </div>
                <div>
                    <select 
                        v-model="localMapping.recording_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">-- Select Column --</option>
                        <option v-for="header in headers" :key="header" :value="header">
                            {{ header }}
                        </option>
                    </select>
                </div>
                <div class="text-sm text-gray-600">
                    <span class="font-mono bg-white px-2 py-1 rounded border">
                        {{ previewValue('recording_name') }}
                    </span>
                </div>
            </div>

            <!-- Ticket ID (Required) -->
            <div class="grid grid-cols-3 gap-4 items-center p-4 bg-gray-50 rounded-lg">
                <div>
                    <label class="text-sm font-medium text-gray-900">
                        Ticket ID *
                    </label>
                    <p class="text-xs text-gray-500 mt-1">
                        Unique ticket identifier
                    </p>
                </div>
                <div>
                    <select 
                        v-model="localMapping.ticket_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">-- Select Column --</option>
                        <option v-for="header in headers" :key="header" :value="header">
                            {{ header }}
                        </option>
                    </select>
                </div>
                <div class="text-sm text-gray-600">
                    <span class="font-mono bg-white px-2 py-1 rounded border">
                        {{ previewValue('ticket_id') }}
                    </span>
                </div>
            </div>

            <!-- Customer Name (Optional) -->
            <div class="grid grid-cols-3 gap-4 items-center p-4 bg-white rounded-lg border border-gray-200">
                <div>
                    <label class="text-sm font-medium text-gray-700">
                        Customer Name
                    </label>
                    <p class="text-xs text-gray-500 mt-1">
                        Optional
                    </p>
                </div>
                <div>
                    <select 
                        v-model="localMapping.customer_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Optional --</option>
                        <option v-for="header in headers" :key="header" :value="header">
                            {{ header }}
                        </option>
                    </select>
                </div>
                <div class="text-sm text-gray-600">
                    <span class="font-mono bg-white px-2 py-1 rounded border">
                        {{ previewValue('customer_name') || '-' }}
                    </span>
                </div>
            </div>

            <!-- Agent Name (Optional) -->
            <div class="grid grid-cols-3 gap-4 items-center p-4 bg-white rounded-lg border border-gray-200">
                <div>
                    <label class="text-sm font-medium text-gray-700">
                        Agent Name
                    </label>
                    <p class="text-xs text-gray-500 mt-1">
                        Optional
                    </p>
                </div>
                <div>
                    <select 
                        v-model="localMapping.agent_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Optional --</option>
                        <option v-for="header in headers" :key="header" :value="header">
                            {{ header }}
                        </option>
                    </select>
                </div>
                <div class="text-sm text-gray-600">
                    <span class="font-mono bg-white px-2 py-1 rounded border">
                        {{ previewValue('agent_name') || '-' }}
                    </span>
                </div>
            </div>

            <!-- Call Intent (Optional) -->
            <div class="grid grid-cols-3 gap-4 items-center p-4 bg-white rounded-lg border border-gray-200">
                <div>
                    <label class="text-sm font-medium text-gray-700">
                        Call Intent
                    </label>
                    <p class="text-xs text-gray-500 mt-1">
                        Purpose of the call
                    </p>
                </div>
                <div>
                    <select 
                        v-model="localMapping.call_intent"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Optional --</option>
                        <option v-for="header in headers" :key="header" :value="header">
                            {{ header }}
                        </option>
                    </select>
                </div>
                <div class="text-sm text-gray-600">
                    <span class="font-mono bg-white px-2 py-1 rounded border">
                        {{ previewValue('call_intent') || '-' }}
                    </span>
                </div>
            </div>

            <!-- Ticket URL (Optional) -->
            <div class="grid grid-cols-3 gap-4 items-center p-4 bg-white rounded-lg border border-gray-200">
                <div>
                    <label class="text-sm font-medium text-gray-700">
                        Ticket URL
                    </label>
                    <p class="text-xs text-gray-500 mt-1">
                        Link to ticket
                    </p>
                </div>
                <div>
                    <select 
                        v-model="localMapping.ticket_url"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Optional --</option>
                        <option v-for="header in headers" :key="header" :value="header">
                            {{ header }}
                        </option>
                    </select>
                </div>
                <div class="text-sm text-gray-600 truncate">
                    <span class="font-mono bg-white px-2 py-1 rounded border text-xs">
                        {{ previewValue('ticket_url') || '-' }}
                    </span>
                </div>
            </div>

            <!-- Call Outcome (Optional) -->
            <div class="grid grid-cols-3 gap-4 items-center p-4 bg-white rounded-lg border border-gray-200">
                <div>
                    <label class="text-sm font-medium text-gray-700">
                        Call Outcome
                    </label>
                    <p class="text-xs text-gray-500 mt-1">
                        Result of the call
                    </p>
                </div>
                <div>
                    <select 
                        v-model="localMapping.call_outcome"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Optional --</option>
                        <option v-for="header in headers" :key="header" :value="header">
                            {{ header }}
                        </option>
                    </select>
                </div>
                <div class="text-sm text-gray-600">
                    <span class="font-mono bg-white px-2 py-1 rounded border">
                        {{ previewValue('call_outcome') || '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-between">
            <button 
                @click="$emit('back')"
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors"
            >
                ← Back
            </button>
            <button 
                @click="handleNext"
                :disabled="!isValid"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
            >
                Next: Validate →
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

interface Props {
    headers: string[];
    previewRow: any;
    modelValue: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue', 'next', 'back']);

const localMapping = ref({ ...props.modelValue });

watch(localMapping, (newValue) => {
    emit('update:modelValue', newValue);
}, { deep: true });

const isValid = computed(() => {
    return localMapping.value.recording_name && localMapping.value.ticket_id;
});

const previewValue = (field: string) => {
    const columnName = (localMapping.value as any)[field];
    if (!columnName || !props.previewRow) return '-';
    return props.previewRow[columnName] || '-';
};

const handleNext = () => {
    if (isValid.value) {
        emit('next');
    }
};
</script>
