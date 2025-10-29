<template>
    <AppLayout title="Detail Agent Scoring" :back="route('setup.recording-analysis.agent-scoring.show', scoring_uuid)">
        <section class="mx-auto rounded-lg border bg-white px-5 pb-5 md:px-0">
            <div class="items-center justify-between border-b py-3 md:flex md:px-6">
                <h2 class="font-krub-bold text-[15px]">{{ scoring.agent }} - {{ scoring.spv }}</h2>
                <ButtonExport
                    :action="route('setup.recording-analysis.agent-scoring.export-analysis', [scoring_uuid, item_uuid])"
                    label="Export to Excel"
                />
            </div>
            <div class="grid grid-cols-1 gap-7 py-5 md:grid-cols-2 md:px-6">
                <div>
                    <h2 class="mb-2 font-krub-bold text-[15px]">Summary</h2>
                    <div class="max-h-[70vh] overflow-auto rounded-md border bg-[#F6F9FB] px-4 py-3 text-[13px]">
                        <ul class="flex flex-col gap-2">
                            <li v-for="(summary, index) in scoring.summary" :key="index">
                                <b>{{ summary.title }} :</b>
                                <p class="text-[12px] whitespace-pre-line">{{ summary.result }}</p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div>
                    <ContactSentimen :sentiment="scoring.sentiment" class="mb-9"/>
                    <AnalysisCriteria :criterias="scoring.scoring" type="scoring" page="agent_scoring" class="mb-6"/>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonExport from '@/components/button/ButtonExport.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ContactSentimen from '@/components/module/analysis/ContactSentimen.vue';
import AnalysisCriteria from '@/components/module/analysis/AnalysisCriteria.vue';

defineProps<{
    scoring_uuid: string;
    item_uuid: string;
    scoring: any;
}>();
</script>
