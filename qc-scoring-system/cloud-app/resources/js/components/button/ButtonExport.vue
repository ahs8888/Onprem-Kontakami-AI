<template>
    <button
        type="button"
        class="flex h-fit items-center justify-center rounded-md border border-success bg-white px-4 py-2 text-[12px] text-success disabled:!cursor-not-allowed disabled:bg-[#ddd]"
        :disabled="loading"
        @click="exportData"
    >
        <IconLoadingButton v-if="loading" />
        {{ label || 'Export' }}
    </button>
</template>
<script setup lang="ts">
import { getAllQueryParamToPost, showAlert } from '@/composables/global-function';
import axios from 'axios';
import { ref } from 'vue';
import IconLoadingButton from '../icon/etc/IconLoadingButton.vue';

const props = defineProps<{
    action: string;
    label?: string;
}>();
const loading = ref(false);

const exportData = () => {
    if (!loading.value) {
        loading.value = true;
        try {
            axios({
                method: 'post',
                url: props.action,
                data: getAllQueryParamToPost(),
                responseType: 'blob',
            })
                .then((result) => {
                    if (result.headers.filename) {
                        const blob = new Blob([result.data], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        });
                        const blobURL = URL.createObjectURL(blob);
                        const anchor = document.createElement('a');
                        anchor.href = blobURL;
                        anchor.download = result.headers.filename;
                        anchor.click();
                        URL.revokeObjectURL(blobURL);
                    } else {
                        showAlert('Failed to download data');
                    }
                    loading.value = false;
                })
                .catch((error) => {
                    console.log(error);
                    loading.value = false;
                    showAlert('Failed to download data');
                });
        } catch (err) {
            console.log(err);
            loading.value = false;
            showAlert('Failed to download data');
        }
    }
};
</script>
