<template>
    <div x-show="confirmation" class="fixed top-0 left-0 z-[32] flex h-screen w-screen items-center justify-center" x-cloak>
        <div
            x-show="confirmation"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click="confirmation = false"
            class="absolute inset-0 h-full w-full bg-[#000000b5]"
        ></div>
        <div
            x-show="confirmation"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full overflow-hidden bg-white sm:max-w-sm sm:rounded-2xl"
        >
            <div class="flex flex-col items-center">
               <div class="border-b w-full flex justify-end px-3 py-2">
                    <button type="button" x-on:click="confirmation = false">
                         <IconClosePopup/>
                    </button>
               </div>
                <p class="py-7 px-3 text-center font-krub-medium text-[14px] text-dark">
                    <span v-html="confirmation"></span>
                </p>
                <div class="flex w-full justify-end bg-[#E7E6E8] gap-4 px-4 py-2">
                    <button ref="cancelButton" x-on:click="confirmation = false" class="w-[100px] py-2 font-krub-medium text-[12px] bg-white !border rounded-lg">
                        Cancel
                    </button>

                    <ButtonYellow
                        type="button"
                        @click="confirmAction"
                        :loading="progres"
                        :disabled="progres"
                        class="w-[100px] border-yellow"
                    >
                        Submit
                    </ButtonYellow>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import ButtonYellow from '../button/ButtonYellow.vue';
import IconClosePopup from '../icon/etc/IconClosePopup.vue';

const emit = defineEmits(['action']);
defineProps<{
    confirmation: string;
    type?: string;
}>();

const progres = ref(false);
const cancelButton = ref<HTMLButtonElement | null>(null);

const confirmAction = () => {
    progres.value = true;
    emit('action', () => {
        progres.value = false;
        cancelButton.value?.click();
    });
};
</script>
