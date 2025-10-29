<template>
    <div>
        <h4 class="mb-2 font-krub-bold text-[15px]">Recording Transcription</h4>
        <div class="mb-3 rounded-md border border-yellow bg-alert px-2 py-1 text-[10px]">
            AI voice to text results cannot guarantee factual accuracy and may sometimes provide innaccurate results. Use discretion and fact check
            before relying solely on the results.
        </div>
        <div class="max-h-[400px] overflow-auto border bg-[#F6F9FB] px-6 py-2 pb-6 sm:rounded-lg">
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
</template>
<script setup lang="ts">
defineProps<{
    conversations: any;
}>();
</script>
