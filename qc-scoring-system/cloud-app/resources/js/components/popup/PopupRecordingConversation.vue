<template>
    <div x-show="conversation" class="fixed top-0 left-0 z-[99] flex h-full w-full items-center justify-center" x-cloak>
        <div
            x-show="conversation"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click="conversation = false"
            @click="$emit('close')"
            class="absolute inset-0 max-h-full w-full bg-[#000000b5]"
        ></div>
        <div
            x-show="conversation"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative max-h-[95%] w-[95%] bg-white pt-4 sm:rounded-lg xl:max-w-[60%]"
        >
            <div class="mt-[-6px] flex items-center justify-between border-b px-6 pb-2 font-krub-semibold text-[16px]">
                <h3 class="text-[15px] text-dark">{{ label }}</h3>
                <IconClosePopup class="cursor-pointer" x-on:click="conversation = false" @click="$emit('close')" />
            </div>
            <div class=" px-6 pt-2 o bg-[#F6F9FB]">
                <div class="mb-3 rounded-md border border-yellow bg-alert px-2 py-1 text-[11px]">
                    AI voice to text results cannot guarantee factual accuracy and may sometimes provide innaccurate results. Use discretion and fact
                    check before relying solely on the results.
                </div>
            </div>
            <div class="max-h-[70vh] overflow-auto bg-[#F6F9FB] px-6 py-2 pb-6 sm:rounded-lg">
                <ul class="flex flex-col gap-3">
                    <li v-for="(conversation, index) in conversations" :key="index">
                        <div
                            class="flex flex-col"
                            :class="{
                                'items-end': conversation.role == 'agent',
                            }"
                        >
                            <span class="text-[11px] text-label">
                                {{ conversation.role == 'agent' ? 'Agent' : 'Pelanggan' }}
                            </span>
                            <div
                                class="w-fit max-w-[60%] rounded-md px-3 py-2 text-[12px]"
                                :class="{
                                    'bg-[#E8EDF5]': conversation.role == 'customer',
                                    'bg-[#0D78F2] text-white': conversation.role == 'agent',
                                }"
                            >
                                {{ conversation.text }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import IconClosePopup from '../icon/etc/IconClosePopup.vue';
defineProps<{
    label: string;
    conversations: any;
}>();
</script>
