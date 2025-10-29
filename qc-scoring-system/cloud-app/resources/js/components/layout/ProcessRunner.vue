<template>
    <div class="fixed bottom-5 left-5 z-[31] flex w-[350px] flex-col gap-2" v-if="process">
        <div class="relative rounded-md border bg-white px-4 py-5 shadow-lg" v-for="(proc, index) in process" :key="index">
            <button type="button" class="absolute top-3 right-3" v-if="proc.status == 'done'" @click="done(proc.uuid)">
                <IconClosePopup />
            </button>
            <div
                class="flex justify-between"
                v-bind:class="{
                    'mt-5': proc.status == 'done',
                }"
            >
                <p class="font-krub-semibold text-[14px]">
                    {{ proc.label }}
                    <span v-if="proc.status == 'progress'">in progress</span>
                    <span v-if="proc.status == 'finish'">Successful</span>
                </p>
                <p class="text-[13px]">{{ proc.progress }}%</p>
            </div>
            <div class="mt-2 block h-[8px] w-full rounded-xl bg-[#DBE0E5]">
                <div class="h-[8px] rounded-xl bg-[#3943B7]" :style="`width:${proc.progress}%`"></div>
            </div>
            <span v-if="proc.status == 'done'" class="text-[11px]"> Analysis completed. View your result </span>
            <div class="flex items-center justify-center" v-if="proc.status == 'done'">
                <button
                    @click="done(proc.uuid)"
                    type="button"
                    class="mt-2 flex items-center gap-2 rounded-full bg-success px-3 py-2 text-[12px] text-white"
                >
                    <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_126_15528)">
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M18.4422 6.06719L8.44219 16.0672C8.32496 16.1845 8.16588 16.2505 8 16.2505C7.83412 16.2505 7.67504 16.1845 7.55781 16.0672L3.18281 11.6922C2.9386 11.448 2.9386 11.052 3.18281 10.8078C3.42703 10.5636 3.82297 10.5636 4.06719 10.8078L8 14.7414L17.5578 5.18281C17.802 4.9386 18.198 4.9386 18.4422 5.18281C18.6864 5.42703 18.6864 5.82297 18.4422 6.06719Z"
                                fill="white"
                            />
                        </g>
                        <defs>
                            <clipPath id="clip0_126_15528">
                                <rect width="20" height="20" fill="white" transform="translate(0.5)" />
                            </clipPath>
                        </defs>
                    </svg>

                    Analysis Done
                </button>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { socket } from '@/socket';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import IconClosePopup from '../icon/etc/IconClosePopup.vue';

const process = ref(usePage().props.process);
const interval = ref();

const listenEvent = (force = false) => {
    if (process.value.length || force) {
        if(interval.value){
            clearInterval(interval.value);
        }
        interval.value = setInterval(() => {
            axios.get(route('process.progress')).then((response) => {
                process.value = response.data;
                if (!response.data.length) {
                    clearInterval(interval.value);
                }
            });
        }, 5000);
    }
};

const done = (uuid: string) => {
    axios.post(route('process.done', uuid)).then(() => {
        process.value = process.value.filter((row) => row.uuid != uuid);
    });
};

const listenSocket = () => {
    socket.on('BROADCAST', (properties: any) => {
        const { channel } = properties;
        if (channel == 'refresh-data' && !interval.value) {
            listenEvent(true);
        }
    });
};
onMounted(() => {
    setTimeout(() => {
        listenEvent();
        listenSocket();
    }, 500);
});
onBeforeUnmount(() => {
    if (interval.value) clearInterval(interval.value);
});
</script>
