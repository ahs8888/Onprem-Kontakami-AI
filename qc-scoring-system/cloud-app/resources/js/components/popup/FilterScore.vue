<template>
    <div x-data="{filter:false}" class="mb-4">
        <ButtonFilter x-on:click="filter=true" />
        <FilterPopup :reset="reset" @close="close" classElement="md:max-w-lg">
            <Select label="Score" name="status" v-model="filter.score">
                <option value="">All</option>
                <option :selected="filter.score=='100'" value="100">&lt;=100%</option>
                <option :selected="filter.score=='75'" value="75">&lt;=75%</option>
                <option :selected="filter.score=='50'"  value="50">&lt;=50%</option>
            </Select>

            <div class="mt-4 flex justify-end gap-3">
                <button
                    ref="cancelButton"
                    x-on:click="filter = false"
                    type="button"
                    class="w-[100px] rounded-lg border bg-white py-3 font-krub-medium text-[12px]"
                >
                    Cancel
                </button>
                <ButtonYellow class="w-[100px] py-3" @click="filterData"> Submit </ButtonYellow>
            </div>
        </FilterPopup>
    </div>
</template>
<script setup lang="ts">
import ButtonFilter from '@/components/button/ButtonFilter.vue';
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import FilterPopup from '@/components/popup/FilterPopup.vue';
import { getQueryParam, removeAllUrlParameter } from '@/composables/global-function';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Select from '../input/Select.vue';

const props = defineProps<{
    reset: string;
}>();
const filter = ref({
    score: getQueryParam('filter[score]') || '',
});

const cancelButton = ref<HTMLButtonElement | null>(null);
const filterData = () => {
    const param = filter.value;

    removeAllUrlParameter();
    router.visit(props.reset, {
        method: 'get',
        data: {
            'filter[score]': param.score,
        },
    });
    cancelButton.value?.click();
};

const close = () => {
    filter.value.score = ''
}
</script>
