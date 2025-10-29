<template>
    <AppLayout title="Import Tickets">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Import Tickets</h1>
                <p class="mt-2 text-gray-600">
                    Link recordings to tickets by importing CSV or Excel file
                </p>
                
                <!-- Help Section -->
                <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="isax icon-info-circle text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">How it works</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Upload your CSV or Excel file containing ticket information</li>
                                    <li>Map your file columns to system fields</li>
                                    <li>Review validation results and matched recordings</li>
                                    <li>Import and link recordings to tickets</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div 
                        v-for="step in steps" 
                        :key="step.number"
                        class="flex items-center flex-1"
                    >
                        <div class="flex items-center">
                            <div 
                                class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-colors"
                                :class="{
                                    'bg-blue-600 border-blue-600 text-white': currentStep >= step.number,
                                    'border-gray-300 text-gray-400': currentStep < step.number
                                }"
                            >
                                <i v-if="currentStep > step.number" class="isax icon-tick-circle text-xl"></i>
                                <span v-else class="font-semibold">{{ step.number }}</span>
                            </div>
                            <div class="ml-3">
                                <div 
                                    class="text-sm font-medium"
                                    :class="{
                                        'text-blue-600': currentStep >= step.number,
                                        'text-gray-400': currentStep < step.number
                                    }"
                                >
                                    {{ step.label }}
                                </div>
                            </div>
                        </div>
                        <div 
                            v-if="step.number < 4"
                            class="flex-1 h-0.5 mx-4"
                            :class="{
                                'bg-blue-600': currentStep > step.number,
                                'bg-gray-300': currentStep <= step.number
                            }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <!-- Step 1: Upload File -->
                <div v-if="currentStep === 1">
                    <h2 class="text-2xl font-bold mb-4">Step 1: Upload CSV/Excel File</h2>
                    <p class="text-gray-600 mb-6">
                        Upload a CSV or Excel file containing ticket information with recording filenames.
                    </p>

                    <!-- Upload Zone -->
                    <div 
                        class="border-2 border-dashed rounded-lg p-12 text-center cursor-pointer transition-colors"
                        :class="{
                            'border-blue-500 bg-blue-50': isDragging,
                            'border-gray-300 hover:border-gray-400': !isDragging
                        }"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop"
                        @click="$refs.fileInput.click()"
                    >
                        <input 
                            type="file" 
                            ref="fileInput"
                            @change="handleFileSelect" 
                            accept=".csv,.xlsx,.xls"
                            class="hidden"
                        />
                        
                        <i class="isax icon-document-upload text-6xl text-gray-400 mb-4"></i>
                        
                        <div v-if="!uploadedFile">
                            <p class="text-lg font-medium text-gray-700 mb-2">
                                Drop your file here or click to browse
                            </p>
                            <p class="text-sm text-gray-500">
                                Supports CSV, XLSX, XLS files (max 10MB)
                            </p>
                        </div>
                        
                        <div v-else class="space-y-2">
                            <p class="text-lg font-medium text-green-600">
                                <i class="isax icon-document-text mr-2"></i>
                                {{ uploadedFile.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ formatFileSize(uploadedFile.size) }}
                            </p>
                            <button 
                                @click.stop="removeFile"
                                class="mt-2 text-sm text-red-600 hover:text-red-700"
                            >
                                Remove file
                            </button>
                        </div>
                    </div>

                    <!-- Parsing Status -->
                    <div v-if="parsing" class="mt-6">
                        <div class="flex items-center justify-center space-x-3">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            <span class="text-gray-600">Parsing file...</span>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="isax icon-info-circle text-red-600 text-xl mr-3"></i>
                            <div class="flex-1">
                                <h4 class="font-medium text-red-800">Error</h4>
                                <p class="text-sm text-red-600 mt-1">{{ errorMessage }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 flex justify-end">
                        <button 
                            @click="parseFile"
                            :disabled="!uploadedFile || parsing"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ parsing ? 'Parsing...' : 'Next: Map Columns' }}
                        </button>
                    </div>
                </div>

                <!-- Step 2: Map Columns -->
                <div v-if="currentStep === 2">
                    <ColumnMapper
                        :headers="csvHeaders"
                        :previewRow="csvData[0]"
                        v-model="columnMapping"
                        @next="validateMapping"
                        @back="currentStep = 1"
                    />
                </div>

                <!-- Step 3: Validate Results -->
                <div v-if="currentStep === 3">
                    <ValidationResults
                        :results="validationResults"
                        @import="performImport"
                        @back="currentStep = 2"
                    />
                </div>

                <!-- Step 4: Import Complete -->
                <div v-if="currentStep === 4">
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-6">
                            <i class="isax icon-tick-circle text-5xl text-green-600"></i>
                        </div>
                        
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">
                            Import Complete!
                        </h2>
                        
                        <div class="space-y-2 mb-8">
                            <p class="text-lg text-gray-600">
                                <span class="font-semibold text-green-600">{{ importResult.linked_count }}</span> 
                                recordings linked successfully
                            </p>
                            <p v-if="importResult.failed_count > 0" class="text-lg text-gray-600">
                                <span class="font-semibold text-red-600">{{ importResult.failed_count }}</span> 
                                failed to link
                            </p>
                        </div>

                        <div v-if="importResult.errors && importResult.errors.length > 0" class="mb-8 text-left">
                            <details class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <summary class="cursor-pointer font-medium text-red-800">
                                    View Error Details ({{ importResult.errors.length }})
                                </summary>
                                <ul class="mt-3 space-y-1 text-sm text-red-600">
                                    <li v-for="(error, index) in importResult.errors" :key="index">
                                        • {{ error }}
                                    </li>
                                </ul>
                            </details>
                        </div>

                        <div class="flex justify-center space-x-4">
                            <a 
                                :href="route('recording.index')"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
                            >
                                View Recordings
                            </a>
                            <button 
                                @click="resetImport"
                                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors"
                            >
                                Import More
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ColumnMapper from './Components/ColumnMapper.vue';
import ValidationResults from './Components/ValidationResults.vue';

const currentStep = ref(1);
const uploadedFile = ref<File | null>(null);
const isDragging = ref(false);
const parsing = ref(false);
const errorMessage = ref('');

const csvHeaders = ref<string[]>([]);
const csvData = ref<any[]>([]);
const columnMapping = ref({
    recording_name: '',
    ticket_id: '',
    customer_name: '',
    agent_name: '',
    call_intent: '',
    ticket_url: '',
    call_outcome: ''
});

const validationResults = ref<any[]>([]);
const importResult = ref<any>({});

const steps = [
    { number: 1, label: 'Upload File' },
    { number: 2, label: 'Map Columns' },
    { number: 3, label: 'Validate' },
    { number: 4, label: 'Complete' }
];

const handleFileSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        uploadedFile.value = input.files[0];
        errorMessage.value = '';
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
        uploadedFile.value = event.dataTransfer.files[0];
        errorMessage.value = '';
    }
};

const removeFile = () => {
    uploadedFile.value = null;
    csvHeaders.value = [];
    csvData.value = [];
    errorMessage.value = '';
};

const parseFile = async () => {
    if (!uploadedFile.value) return;

    parsing.value = true;
    errorMessage.value = '';

    const formData = new FormData();
    formData.append('file', uploadedFile.value);

    try {
        const response = await fetch(route('ticket-import.parse'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        const data = await response.json();

        if (data.success) {
            csvHeaders.value = data.headers;
            csvData.value = data.rows;
            
            // Auto-detect columns
            autoDetectColumns();
            
            currentStep.value = 2;
        } else {
            errorMessage.value = data.message || 'Failed to parse file';
        }
    } catch (error: any) {
        errorMessage.value = error.message || 'An error occurred while parsing the file';
    } finally {
        parsing.value = false;
    }
};

const autoDetectColumns = () => {
    const variations: { [key: string]: string[] } = {
        recording_name: ['recording_name', 'recording_filename', 'filename', 'file', 'recording', 'audio_file'],
        ticket_id: ['ticket_id', 'ticket', 'id', 'ticket_number', 'case_id'],
        customer_name: ['customer_name', 'customer', 'client_name', 'client', 'name'],
        agent_name: ['agent_name', 'agent', 'staff_name', 'staff', 'employee'],
        call_intent: ['call_intent', 'intent', 'purpose', 'reason', 'type'],
        ticket_url: ['ticket_url', 'url', 'link', 'ticket_link'],
        call_outcome: ['call_outcome', 'outcome', 'result', 'status', 'resolution']
    };

    for (const [field, fieldVariations] of Object.entries(variations)) {
        for (const variation of fieldVariations) {
            const found = csvHeaders.value.find(h => 
                h.toLowerCase().includes(variation.toLowerCase())
            );
            if (found) {
                (columnMapping.value as any)[field] = found;
                break;
            }
        }
    }
};

const validateMapping = async () => {
    try {
        const response = await fetch(route('ticket-import.validate'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                column_mapping: columnMapping.value,
                csv_data: csvData.value
            })
        });

        const data = await response.json();

        if (data.success) {
            validationResults.value = data.results;
            currentStep.value = 3;
        } else {
            errorMessage.value = data.message || 'Validation failed';
        }
    } catch (error: any) {
        errorMessage.value = error.message || 'An error occurred during validation';
    }
};

const performImport = async () => {
    const matchedRecords = validationResults.value.filter(r => r.status === 'matched');

    try {
        const response = await fetch(route('ticket-import.bulk-link'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                records: matchedRecords,
                column_mapping: columnMapping.value
            })
        });

        const data = await response.json();

        if (data.success) {
            importResult.value = data;
            currentStep.value = 4;
        } else {
            errorMessage.value = data.message || 'Import failed';
        }
    } catch (error: any) {
        errorMessage.value = error.message || 'An error occurred during import';
    }
};

const resetImport = () => {
    currentStep.value = 1;
    uploadedFile.value = null;
    csvHeaders.value = [];
    csvData.value = [];
    validationResults.value = [];
    importResult.value = {};
    errorMessage.value = '';
};

const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
};
</script>
