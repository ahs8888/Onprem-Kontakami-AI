<template>
    <div x-show="agentFile" class="fixed top-0 left-0 z-[99] flex h-full w-full items-center justify-center" x-cloak>
        <div
            x-show="agentFile"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click="agentFile = false"
            @click="$emit('close')"
            class="absolute inset-0 max-h-full w-full bg-[#000000b5]"
        ></div>
        <div
            x-show="agentFile"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative max-h-[95%] w-[95%] bg-white pt-4 sm:rounded-lg xl:max-w-md"
        >
            <div class="mt-[-6px] flex items-center justify-between border-b px-6 pb-2 font-krub-semibold text-[16px]">
                <h3 class="text-[15px] text-dark">{{ label }}</h3>
                <IconClosePopup class="cursor-pointer" x-on:click="agentFile = false" @click="$emit('close')" />
            </div>
            <div class="max-h-[70vh] overflow-auto bg-[#F6F9FB] py-2 pb-6 sm:rounded-lg">
                <ol class="text-[14px]">
                    <li v-for="(file, index) in files" :key="index" class="wrap-text-line border-b px-6 py-2 last:border-b-0">
                         {{ index+1 }}. {{ file }}
                    </li>
                </ol>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import IconClosePopup from '@/components/icon/etc/IconClosePopup.vue';
defineProps<{
    label: string;
    files: any;
}>();
</script>
