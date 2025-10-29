<template>
    <div class="fixed bottom-8 right-8 flex flex-col gap-4">
        <div class="shadow-progress p-4 rounded-lg text-dark flex flex-col gap-2 min-w-md relative z-[99] bg-white" v-if="uploadState.uploading.value">
            <p class="font-semibold">Uploading ...</p>
            <div class="flex gap-2 items-center justify-between">
                <p class="text-xs truncate">{{ uploadState.folderName.value }}</p>
                <p class="text-xs">{{ uploadState.progress.value }}%</p>
            </div>
            <div
                class="w-full h-2 bg-[#DBE0E5] rounded-full overflow-hidden"
            >
                <div
                    class="bg-primary h-full rounded-full progress-animated"
                    :style="`width: ${uploadState.progress.value}%`"
                ></div>
            </div>
        </div>

        <div class="shadow-progress p-4 rounded-lg text-dark flex flex-col gap-2 min-w-md relative z-[99] bg-white" v-if="remaining > 0">
            <p class="font-semibold">Converting to Text ...</p>
            <div class="flex gap-2 items-center justify-between">
                <p class="text-xs truncate">Folder Remaining</p>
                <p class="text-xs">{{ remaining }}</p>
            </div>

            <div class="w-full h-2 bg-[#DBE0E5] rounded-full overflow-hidden relative">
                <div class="absolute top-0 left-0 h-full bg-primary rounded-full animate-loading-stripe" style="width: 50%"></div>
            </div>
        </div>

        <div class="shadow-progress p-4 rounded-lg text-dark flex flex-col gap-2 min-w-md relative z-[99] bg-white max-w-md" v-if="uploadState.alert.value.title">
            <IconClosePopup
                class="cursor-pointer ms-auto"
                @click="doneUpload"
            />
            <p class="font-semibold">{{ uploadState.alert.value.title }}</p>
            <div class="flex gap-2 items-center justify-between" v-if="uploadState.alert.value.type == 'success'">
                <p class="text-xs truncate">{{ uploadState.alert.value.folderName }}</p>
                <p class="text-xs">100%</p>
            </div>

            <div class="w-full h-2 bg-[#DBE0E5] rounded-full overflow-hidden relative" v-if="uploadState.alert.value.type == 'success'">
                <div
                    class="bg-primary h-full rounded-full"
                    :style="`width: 100%`"
                ></div>
            </div>
            <p class="text-xs">{{ uploadState.alert.value.description }}</p>

            <div class="text-[#EE0000] text-sm bg-[#DBE8F2] rounded-full px-4 py-2 mx-auto mt-2 cursor-pointer" v-if="uploadState.alert.value.type == 'error'" @click="reuploadFile">
                Retry Upload
            </div>

            <div class="text-dark text-sm bg-[#DBE8F2] rounded-full px-4 py-2 mx-auto mt-2 cursor-pointer flex items-center gap-2" v-if="uploadState.alert.value.type == 'success'" @click="doneUpload">
                <i class="isax icon-tick-circle"></i>
                <span>Done</span>
            </div>
        </div>
    </div>

</template>

<script setup lang="ts">
    import { useUploadState } from '@/Hooks/uploadState';
    import { ref, onMounted, onUnmounted } from 'vue'
    import axios from 'axios'
    import IconClosePopup from '../Icon/Etc/IconClosePopup.vue';
    import { clickId } from '@/Plugins/Function/global-function';


    const uploadState = useUploadState();

    const remaining = ref(0)
    let intervalId: any = null

    const fetchProgress = () => {
        axios.post(route('transcript.remaining')).then(res => {
            const currentRemaining = remaining.value
            remaining.value = res.data.remaining
            if (currentRemaining != 0 && remaining.value == 0 && !uploadState.uploading.value) {
                window.location.reload()
            }
        })
    }

    onMounted(() => {
        fetchProgress()
        intervalId = setInterval(fetchProgress, 5000)
    })

    onUnmounted(() => {
        clearInterval(intervalId)
    })

    const reuploadFile = () => {
        doneUpload()
        clickId('file-upload')
    }

    const doneUpload = () => {
        uploadState.injectId.value = ''
        uploadState.alert.value = {
            title: '',
            type: '',
            description: '',
            folderName: ''
        }
    }

</script>
