<template>
    <div class="border-b py-4" x-data="{cropImage :false}">
        <h2 class="font-krub-semibold text-[14px]">Profile Picture</h2>
        <div class="flex flex-col items-center gap-4 md:flex-row">
            <div class="relative h-[120px] w-[120px]">
                <img :src="avatar" :alt="user.name" class="h-full w-full rounded-full border object-cover p-1" />
                <div class="absolute top-0 left-0 flex h-full w-full items-center justify-center rounded-full bg-[#00000027]" v-if="loading">
                    <IconLoadingButton />
                </div>
            </div>
            <div class="flex flex-col items-center text-center md:items-start md:text-start">
                <button
                    type="button"
                    class="mb-3 flex w-fit items-center gap-2 rounded-md border border-yellow bg-[#F5F5FB] px-3 py-2 text-[12px] text-yellow"
                    @click="input?.click()"
                >
                    <i class="isax icon-edit-2"></i>
                    Change Image
                </button>
                <p class="text-[10px] text-dark md:text-[13px]">
                    File size: 5,000,000 bytes (5 Megabytes) maximum. <br />
                    Allowed file extensions: .JPG .JPEG .PNG
                </p>
            </div>
        </div>

        <ImageCropper :url="imageUrlTemp" @crop="croppedImage" @cancel="resetInput" />
        <input type="file" ref="input" accept=".jpg,.png,.jpeg" class="hidden" id="input-file-profile" @change="changeFile($event)" />
    </div>
</template>

<script setup lang="ts">
import IconLoadingButton from '@/components/icon/etc/IconLoadingButton.vue';
import ImageCropper from '@/components/input/ImageCropper.vue';
import { compressImage, randomString, showAlert } from '@/composables/global-function';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const user = usePage().props.auth?.user;

const emit = defineEmits(['update:modelValue', 'setAvatar']);
const input = ref<HTMLInputElement | null>(null);
const file: any = ref(null);
const avatar: any = ref(user.avatar);
const imageUrlTemp = ref('');
const loading = ref(false);

const changeFile = (event: any) => {
    const fileValue = event.target.files[0];
    if (fileValue) {
        const allowedTypes = ['image/jpeg','image/jpg', 'image/png'];

        if (fileValue.size / 1000000 > Number(5)) {
            showAlert(`File size cannot be more than 5mb`);
            event.target.value = '';
            file.value = '';
            imageUrlTemp.value = '';
        } else if (!allowedTypes.includes(fileValue.type)) {
            showAlert(`File must be valid image format`);
            event.target.value = '';
            file.value = '';
            imageUrlTemp.value = '';
        } else {
            compressImage(fileValue, 350, 350, 'jpeg', 0.5, function (compressed: any) {
                const fileCompressed = new File([compressed], `${randomString()}.jpeg`, {
                    type: 'image/jpeg',
                });
                file.value = fileCompressed;
                imageUrlTemp.value = compressed;
                document.getElementById('show-cropper')?.click();
            });
        }
    }
};
const croppedImage = (image: any) => {
    emit('update:modelValue', image.blob);
    emit('setAvatar', image.preview);
    avatar.value = image.preview;
};

const resetInput = () => {
    file.value = '';
    (document.getElementById('input-file-profile') as HTMLInputElement).value = '';
};
</script>
