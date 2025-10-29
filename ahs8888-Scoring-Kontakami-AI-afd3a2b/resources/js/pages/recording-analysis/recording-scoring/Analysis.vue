<template>
    <AppLayout :title="`Recording Scoring ${scoring.filename}`" :back="route('setup.recording-analysis.recording-scoring.show', analysis_uuid)">
        <section class="mx-auto rounded-lg border bg-white px-5 pb-5 md:max-w-[70%] md:px-0">
            <div class="flex items-center justify-between border-b px-6 py-3">
                <h2 class="font-krub-bold text-[15px]">
                    {{ scoring.filename }}
                </h2>
                <ButtonExport
                    :action="route('setup.recording-analysis.recording-scoring.export-analysis', [analysis_uuid, scoring_uuid])"
                    label="Export to Excel"
                />
            </div>
            <div class="flex flex-col gap-7 px-6 py-5">
                <div class="text-center">
                    <p class="mb-[-10px] text-[12px]">Recording Score</p>
                    <h2
                        class="s font-krub-bold text-[70px]"
                        v-bind:class="{
                            'text-red': scoring.avg_score <= 50,
                            'text-yellow-600': scoring.avg_score > 50 && scoring.avg_score <= 75,
                            'text-success': scoring.avg_score > 75,
                        }"
                    >
                        {{ Math.round(scoring.avg_score) }}%
                    </h2>
                </div>
                <RecordingTranscription :conversations="scoring.conversations" />
                <AnalysisScoring :scorings="scoring.scoring" />
                <AnalysisNonScoring :non-scorings="scoring.non_scoring" />
                <AnalysisSummary :summary="scoring.summary" />
            </div>
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonExport from '@/components/button/ButtonExport.vue';
import AnalysisNonScoring from '@/components/module/analysis/AnalysisNonScoring.vue';
import AnalysisScoring from '@/components/module/analysis/AnalysisScoring.vue';
import AnalysisSummary from '@/components/module/analysis/AnalysisSummary.vue';
import RecordingTranscription from '@/components/module/analysis/RecordingTranscription.vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    analysis_uuid: string;
    scoring_uuid: string;
    scoring: any;
}>();
</script>
