<template>
    <section
        class="px-5 mx-auto pt-3 pb-5"
        x-data="{filter:false}"
    >
        <div class="flex mb-3 gap-2 items-center">
            <Link :href="route('recordings.index')">
                <h2 class="text-dark underline font-semibold text-sm">Recording List</h2>
            </Link>
            <i class="isax icon-arrow-right-3 text-dark"></i>
            <Link :href="route('recordings.detail', data.id)">
                <h2 class="text-dark underline font-semibold text-sm">{{ data.folder_name }}</h2>
            </Link>
            <i class="isax icon-arrow-right-3 text-dark"></i>
            <h2 class="text-dark font-semibold text-sm">{{ detail.name }}</h2>
        </div>

        <div class="bg-white border p-6 rounded-lg whitespace-pre-line text-sm leading-8 flex flex-col gap-3 mb-20">
            <div
                v-for="(item, index) in parsedTranscript"
                :key="index"
                class="flex items-start gap-2 flex-col max-w-[60%]"
                :class="{
                    '!items-end ms-auto': item.role == 'agent'
                }"
            >
                <p
                    class="font-semibold capitalize text-xs"
                    :class="{
                        'text-primary': item.role === 'agent',
                        'text-dark-grey': item.role === 'customer' || item.role === 'unknown'
                    }"
                >
                    {{ item.name }}
                </p>
                <div
                    class="rounded-2xl py-2 px-3 text-sm leading-6"
                    :class="{
                        'bg-[#ddd] text-dark': item.role === 'customer' || item.role === 'unknown',
                        'bg-primary text-white': item.role === 'agent'
                    }"
                >
                    {{ item.content }}
                </div>
            </div>
        </div>
    </section>
</template>
<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";
const props = defineProps(["detail", "data"]);

const parsedTranscript = computed(() => {
    return props.detail.transcript
        .split('\n')
        .filter((line: any) => line.trim() !== '')
        .map((line: any) => {
        const match = line.match(/^(\w+):\s*(.+)$/)
        if (!match) return null

        const [, nameRaw, content] = match
        const name = nameRaw.trim()
        const lower = name.toLowerCase()

        const role = lower === 'agent' ? 'agent'
                    : lower === 'customer' ? 'customer'
                    : 'unknown'

        return { name, role, content }
        })
        .filter(Boolean)
})
</script>
