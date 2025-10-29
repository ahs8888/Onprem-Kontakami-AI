<template>
    <AppLayout title="Agent Analysis">
        <TabMenu active="profiling" :tabs="tabMenuAgentAnalysis()" />

        <section x-data="{confirmation:false}">
            <div class="flex justify-between">
                <TableSearch />
                <div class="flex items-center gap-2">
                    <ButtonYellow :href="route('setup.agent-analysis.profiling.choise')" icon="isax icon-add">
                        Add Profiling
                    </ButtonYellow>
                    <ButtonExport :action="route('setup.agent-analysis.profiling.export')" />
                    <FilterDateRange :reset="route('setup.agent-analysis.profiling.index')" />
                </div>
            </div>
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonExport from '@/components/button/ButtonExport.vue';
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import TabMenu from '@/components/button/TabMenu.vue';
import TableSearch from '@/components/table/TableSearch.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import FilterDateRange from '@/components/popup/FilterDateRange.vue';
import { tabMenuAgentAnalysis } from '@/util';
</script>
