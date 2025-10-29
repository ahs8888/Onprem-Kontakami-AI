<template>
    <div>
        <slot name="header" />
        <div class="table-main main-datatable overflow-auto rounded-xl border">
            <table class="w-full table-auto border-collapse text-sm">
                <thead>
                    <tr class="bg-[#F4F6FA]">
                        <Th v-for="(col, colIndex) in columns" :key="colIndex">{{ col }}</Th>
                    </tr>
                </thead>
                <slot name="tbody" />
                <tbody class="bg-white">
                    <slot v-if="!paginate?.loading.value" />
                    <tr v-if="!paginate?.loading.value && !paginate?.data?.value?.length && paginate">
                        <Td :colspan="columns?.length + 1" class="py-10 text-center">
                            <div class="flex flex-col items-center justify-center gap-5">
                                <span class="mb-4 font-krub-semibold text-[13px] text-dark"> No data found</span>
                            </div>
                        </Td>
                    </tr>
                </tbody>
                <tbody v-if="paginate?.loading.value">
                    <tr v-for="n in 5" :key="n">
                        <Td class="pe-0" v-if="checked !== undefined">
                            <span class="inline-block h-[10px] min-w-[20px] rounded-md bg-[#dddddda8]"></span>
                        </Td>
                        <Td v-for="(col, x) in columns" :key="x">
                            <span class="inline-block h-[10px] w-full min-w-[20px] rounded-md bg-[#dddddda8]"></span>
                        </Td>
                    </tr>
                </tbody>
            </table>
            <Pagination
                class="sticky left-0 rounded-xl rounded-tl-none rounded-tr-none"
                :information="paginate.information.value"
                @next="paginate.next()"
                @prev="paginate.prev()"
                @setLimit="paginate.changeLimit"
                v-if="paginate"
            />
        </div>
    </div>
</template>
<script setup lang="ts">
import Pagination from './Pagination.vue';
import Td from './Td.vue';
import Th from './Th.vue';

defineProps<{
    columns: Array<any>;
    checked?: any;
    paginate?: any;
}>();
</script>
