<template>
    <div x-data="{filter:false}">
        <ButtonFilter x-on:click="filter=true" />
        <FilterPopup :reset="reset" classElement="md:max-w-lg">
            <label class="mb-1 block font-krub-semibold text-[13px] text-dark"> Created Date : </label>
            <div class="grid grid-cols-2 gap-4">
                <DatePicker name="created_at_start" label="Start date" v-model="filter.date_start" :value="filter.date_start" />
                <DatePicker name="created_at_end" label="End date" v-model="filter.date_end" :value="filter.date_end" :min="filter.date_start" />
            </div>
            <SelectMultiple :items="actionTypes" label="Action Type" v-model="filter.types" placeholder="Select Action Type"/>
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
import DatePicker from '@/components/input/DatePicker.vue';
import FilterPopup from '@/components/popup/FilterPopup.vue';
import SelectMultiple from '../input/SelectMultiple.vue';
import { removeAllUrlParameter, routeAppendParam, showAlert, validateMinimumDateRange } from '@/composables/global-function';
import { ref } from 'vue';

defineProps<{
    reset: string;
}>();

const actionTypes = [
    {
        id : 'vtt',
        value : 'Voice to text'
    },{
        id : 'rs',
        value : 'Recording Scoring'
    },{
        id : 'as',
        value : 'Agent Scoring'
    }
]
const filter = ref({
    date_start: '',
    date_end: '',
    types : []
});

const cancelButton = ref<HTMLButtonElement | null>(null);
const filterData = () => {
    const param = filter.value;

    if (!param.date_start || !param.date_end) {
        showAlert('Please select created date');
        return;
    }
    if (validateMinimumDateRange(param.date_start, param.date_end)) {
        removeAllUrlParameter();
        if (param.date_start || param.date_end) {
            const filterParam: any = {
                'filter[date_start]': param.date_start || '',
                'filter[date_end]': param.date_end || '',
                'filter[types]' : param.types || ''
            };
            routeAppendParam(filterParam, false);
        }
        cancelButton.value?.click();
    }
};
</script>
