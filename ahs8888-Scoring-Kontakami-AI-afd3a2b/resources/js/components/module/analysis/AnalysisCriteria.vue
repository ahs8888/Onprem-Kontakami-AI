<template>
    <div>
        <h2 class="mb-2 font-krub-bold text-[15px]">
            {{ type == 'scoring' ? 'Criteria with Scoring' : 'Criteria without Scoring' }}
        </h2>
        <div
            class="overflow-auto rounded-md border bg-[#F6F9FB] px-4 py-3 text-[13px]"
            v-bind:class="{
                'h-[57vh]': page == 'agent_scoring',
                'h-[320px]': page != 'agent_scoring',
            }"
        >
            <div v-if="!criterias.length" class="flex h-full items-center justify-center font-krub-bold text-[15px]">No Data</div>
            <ul class="flex flex-col gap-1" v-else>
                <li v-for="(item, index) in criterias" :key="index">
                    <div class="mb-3 border-b pb-1">
                        <b class="text-[13px]">{{ item.title }} </b>
                    </div>
                    <div class="pl-4">
                        <table class="mb-3 w-full text-[12px]">
                            <tr class="border-b text-[#666978]">
                                <td class="px-2 pb-1">Criteria</td>
                                <td class="px-2 pb-1 text-end">{{ type == 'scoring' ? 'Scoring' : '' }}</td>
                            </tr>
                            <tr v-for="(row, i) in item.items" :key="i" class="border-b font-krub-medium text-[#181C32]">
                                <td class="px-2 py-1">
                                    {{ row.title }}
                                </td>
                                <td class="px-2 py-1 text-end">
                                    <div v-if="type == 'scoring'">{{ row.score }}{{ row.unit }} / {{ row.max_score }}{{ row.unit }}</div>
                                    <div v-else>
                                        <i class="isax icon-tick-circle text-[15px] font-bold " v-if="row.value"></i>
                                        <i class="isax icon-close-circle text-[15px] font-bold" v-else></i>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
<script setup lang="ts">
defineProps(['criterias', 'type', 'page']);
</script>
