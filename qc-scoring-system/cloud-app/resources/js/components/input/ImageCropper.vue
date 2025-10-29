<template>
    <div x-show="cropImage" class="fixed top-0 left-0 z-[99] flex h-full w-full items-center justify-center" x-cloak>
        <button type="button" id="show-cropper" x-on:click="cropImage=true"></button>
        <div
            x-show="cropImage"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click="cropImage = false"
            @click="close"
            class="bg-opacity-40 absolute inset-0 max-h-full w-full bg-[#00000096]"
        ></div>
        <div
            x-show="cropImage"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative max-h-[95%] w-full bg-white sm:rounded-lg md:max-w-md"
        >
            <div class="overflow-hidden sm:rounded-lg">
                <cropper
                    v-if="url"
                    :src="url"
                    class="h-[350px] w-full bg-[#000]"
                    :stencil-props="{
                        aspectRatio: 1,
                    }"
                    image-restriction="stencil"
                    :default-size="defaultSize"
                    @change="cropImage"
                >
                </cropper>
                <div class="my-3 flex justify-center gap-4">
                    <ButtonOutlineGrey x-on:click="cropImage = false" class="w-[100px] py-2 font-krub-medium text-[12px] uppercase" @click="close">
                        Cancel
                    </ButtonOutlineGrey>
                    <ButtonYellow type="button" class="w-[100px] border-yellow uppercase" x-on:click="cropImage = false" @click="crop">
                        Crop
                    </ButtonYellow>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { randomString } from '@/composables/global-function';
import { ref } from 'vue';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import ButtonOutlineGrey from '../button/ButtonOutlineGrey.vue';
import ButtonYellow from '../button/ButtonYellow.vue';

defineProps(['url']);
const emit = defineEmits(['crop', 'cancel']);
const imageTemp: any = ref(null);
const defaultSize = (data: any) => {
    const imageSize = data.imageSize;
    return {
        width: Math.min(imageSize.height, imageSize.width),
        height: Math.min(imageSize.height, imageSize.width),
    };
};

const cropImage = (cropped: any) => {
    const base64File = cropped.canvas.toDataURL();
    const arr = base64File.split(','),
        mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[arr.length - 1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }
    imageTemp.value = {
        blob: u8arr,
        type: mime,
        preview: cropped.canvas.toDataURL(),
    };
};

const crop = () => {
    const img = imageTemp.value;
    emit('crop', {
        blob: new File([img.blob], `${randomString()}.jpeg`, {
            type: img.type,
        }),
        preview: img.preview,
    });
};

const close = () => {
    emit('cancel');
};
</script>
