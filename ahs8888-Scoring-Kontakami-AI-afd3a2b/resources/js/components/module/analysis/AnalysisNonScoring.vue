<template>
    <ul class="flex flex-col gap-4">
        <li v-for="(scoring, index) in nonScorings" :key="index">
            <h4 class="mb-2 font-krub-bold text-[15px]">
                {{ scoring.title }}
            </h4>
            <ul class="flex flex-col gap-2">
                <li v-for="(item, index) in scoring.items" :key="index" class="rounded-sm border bg-[#F7FAFC] px-4 py-2">
                    <div
                        class="flex items-center gap-2 font-krub-semibold text-[14px]"
                        
                    >
                        <IconValid/>
                        {{ item.title }}
                    </div>
                    <p class="text-[12px] mt-1">
                         "{{ item.sentence || 'Tidak terdeteksi' }}"
                    </p>
                </li>
            </ul>
        </li>
    </ul>
</template>
<script setup lang="ts">
import IconValid from '@/components/icon/etc/IconValid.vue';
defineProps<{
    nonScorings: any;
}>();
</script>
