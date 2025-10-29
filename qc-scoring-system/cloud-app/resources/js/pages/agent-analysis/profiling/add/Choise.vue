<template>
    <AppLayout title="Add Profiling" :back="route('setup.agent-analysis.profiling.index')">
        <section class="mx-auto px-5 pb-5 md:max-w-[60%] md:px-0" x-data="{popup:false}">
            <h1 class="mb-3 font-krub-semibold text-[15px]">Template Type</h1>
            <ul class="flex flex-col gap-3">
                <li v-for="choice in choices" :key="choice.type">
                    <label
                        :for="choice.type"
                        class="flex w-full cursor-pointer gap-3 rounded-lg border bg-white px-3 py-2"
                        :class="{
                            'border-yellow': selectedType == choice.type,
                        }"
                    >
                        <div class="border-2 border-yellow rounded-full w-fit h-fit">
                            <input
                                type="radio"
                                name="type"
                                class="h-[15px] w-[15px] cursor-pointer !border-white shadow-none outline-none checked:!border-[3px]"
                                v-model="selectedType"
                                :id="choice.type"
                                :value="choice.type"
                            />
                        </div>
                        <div class="w-full">
                            <div class="mb-[6px] flex items-center justify-between">
                                <h2 class="font-krub-semibold text-[15px]">
                                    {{ choice.label }}
                                </h2>
                                <button
                                    type="button"
                                    class="font-krub-semibold text-[12px] text-yellow underline"
                                    @click="seeImage(choice.label, choice.image)"
                                    x-on:click="popup=true"
                                >
                                    View Result
                                </button>
                            </div>
                            <p class="text-[12px] text-label">
                                {{ choice.description }}
                            </p>
                        </div>
                    </label>
                </li>
            </ul>
            <div class="mt-3 flex justify-end">
                <ButtonYellow
                    :href="selectedType ? route('setup.agent-analysis.profiling.create', selectedType) : 'javascript:;'"
                    class="px-8"
                    :class="{
                        'cursor-not-allowed !bg-[#ddd]': !selectedType,
                    }"
                >
                    Next
                </ButtonYellow>
            </div>
            <Popup :title="popup.title" class="md:max-w-4xl">
                <img :src="popup.image" :alt="popup.title" />
            </Popup>
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import Popup from '@/components/popup/Index.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

defineProps<{
    choices: any;
}>();

const selectedType = ref('');
const popup = ref({
    title: '',
    image: '',
});

const seeImage = (title: string, image: string) => {
    popup.value = {
        title: title,
        image: image,
    };
};
</script>
