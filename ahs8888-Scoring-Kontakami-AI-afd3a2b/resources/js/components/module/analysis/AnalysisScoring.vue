<template>
    <ul class="flex flex-col gap-4">
        <li v-for="(scoring, index) in scorings" :key="index">
            <h4 class="mb-2 font-krub-bold text-[15px]">
                {{ scoring.title }}
            </h4>
            <div class="rounded-sm border bg-[#F7FAFC] px-4 py-2">
                <h5 class="font-krub-bold text-[14px]">
                    Skor : {{ scoring.valid }}/{{ scoring.total }}
                    <span class="ms-3 inline-block text-success"> {{ Math.round(scoring.percentage) }}% </span>
                </h5>
                <ul class="mt-2 flex flex-col gap-2">
                    <li
                        v-for="(selected, title) in scoring.items"
                        :key="title"
                        class="flex items-center gap-2 rounded-md bg-[#E8EDF5] px-3 py-2 font-krub-medium text-[12px]"
                        :class="{
                            'text-red': !selected,
                        }"
                    >
                        <IconValid v-if="selected" />
                        <IconInvalid v-else />
                        {{ title }}
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</template>
<script setup lang="ts">
import IconInvalid from '@/components/icon/etc/IconInvalid.vue';
import IconValid from '@/components/icon/etc/IconValid.vue';
defineProps<{
    scorings: any;
}>();
</script>
