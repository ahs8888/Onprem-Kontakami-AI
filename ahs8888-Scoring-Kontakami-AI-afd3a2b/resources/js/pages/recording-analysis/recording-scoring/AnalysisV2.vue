<template>
    <AppLayout :title="`Recording Scoring ${scoring.filename}`" :back="route('setup.recording-analysis.recording-scoring.show', analysis_uuid)">
        <section class="mx-auto rounded-lg border bg-white px-5 pb-5 md:px-0">
            <div class="md:flex items-center justify-between border-b md:px-6 py-3">
                <h2 class="font-krub-bold text-[15px]">
                    {{ scoring.filename }}
                </h2>
                <ButtonExport
                    :action="route('setup.recording-analysis.recording-scoring.export-analysis', [analysis_uuid, scoring_uuid])"
                    label="Export to Excel"
                />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-7 md:px-6 py-5">
                <div>
                    <h2 class="mb-2 font-krub-bold text-[15px]">Summary</h2>
                    <div class="max-h-[300px] overflow-auto rounded-md border bg-[#F6F9FB] px-4 py-3 text-[13px]">
                        <ul class="flex flex-col gap-2">
                            <li v-for="(summary, index) in scoring.summary" :key="index">
                                <b>{{ summary.title }} :</b>
                                <p class="text-[12px] whitespace-pre-line">{{ summary.result }}</p>
                            </li>
                        </ul>
                    </div>

                    <RecordingTranscription class="mt-5" :conversations="scoring.conversations" />
                </div>
                <div>
                    <ContactSentimen :sentiment="scoring.sentiment" class="mb-9"/>
                    <AnalysisCriteria :criterias="scoring.scoring" type="scoring" class="mb-6"/>
                    <AnalysisCriteria :criterias="scoring.non_scoring" type="non_scoring" />
                </div>
            </div>
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonExport from '@/components/button/ButtonExport.vue';
import AnalysisCriteria from '@/components/module/analysis/AnalysisCriteria.vue';
import ContactSentimen from '@/components/module/analysis/ContactSentimen.vue';
import RecordingTranscription from '@/components/module/analysis/RecordingTranscription.vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    analysis_uuid: string;
    scoring_uuid: string;
    scoring: any;
}>();
</script>
