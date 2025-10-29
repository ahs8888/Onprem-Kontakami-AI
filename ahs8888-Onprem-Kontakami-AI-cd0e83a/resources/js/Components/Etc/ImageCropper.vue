<template>
     <div
         x-show="cropImage"
         class="fixed top-0 left-0 z-[99] flex items-center justify-center h-full w-full"
         x-cloak
     >
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
             class="absolute inset-0 w-full max-h-full bg-black opacity-40"
         ></div>
         <div
             x-show="cropImage"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative w-full max-h-[95%] bg-white sm:rounded-lg md:max-w-md"
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
                 <div class="flex justify-center gap-4 my-3">
                     <ButtonOutlineGrey
                         x-on:click="cropImage = false"
                         class="font-krub font-medium text-[12px] py-2 w-[100px] uppercase"
                         @click="close"
                     >
                         Cancel
                     </ButtonOutlineGrey>
                     <ButtonPrimary
                         type="button"
                         class="w-[100px] uppercase border-primary"
                         x-on:click="cropImage = false"
                         @click="crop"
                     >
                         Crop
                     </ButtonPrimary>
                 </div>
             </div>
         </div>
     </div>
 </template>

 <script lang="ts" setup>
 import "vue-advanced-cropper/dist/style.css";
 import { Cropper } from "vue-advanced-cropper";
 import ButtonOutlineGrey from "../Button/ButtonOutlineGrey.vue";
 import ButtonPrimary from "../Button/ButtonPrimary.vue";
 import { randomString } from "@/Plugins/Function/global-function";
 import { ref } from "vue";

 defineProps(["url"]);
 const emit = defineEmits(["crop","cancel"]);
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
     var arr = base64File.split(","),
         mime = arr[0].match(/:(.*?);/)[1],
         bstr = atob(arr[arr.length - 1]),
         n = bstr.length,
         u8arr = new Uint8Array(n);
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
     emit("crop", {
         blob: new File([img.blob], `${randomString()}.jpeg`, {
             type: img.type,
         }),
         preview: img.preview,
     });
 };

 const close = () => {
     emit('cancel')
 }
 </script>
