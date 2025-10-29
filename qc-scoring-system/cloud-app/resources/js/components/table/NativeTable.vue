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
                    <slot />
                </tbody>
            </table>
        </div>
    </div>
</template>
<script setup lang="ts">
import Th from './Th.vue';
defineProps<{
    columns: Array<any>;
}>();
</script>
