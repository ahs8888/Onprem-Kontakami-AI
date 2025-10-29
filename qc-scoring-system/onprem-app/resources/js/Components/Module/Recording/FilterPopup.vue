<template>
    <Filter class="md:max-w-lg overflow-auto" :reset="route('recordings.index')" @close="resetInput">
        <div class="max-h-[65vh] overflow-auto -mx-6 px-6">
            <label class="text-[13px] text-dark font-krub-semibold mb-1 block">
                Created Date :
                <span class="text-red">*</span>
            </label>
            <div class="grid grid-cols-2 gap-4">
                <DatePicker
                    name="created_start"
                    label="Start date"
                    required
                    v-model="filter.created_start"
                    :value="filter.created_start"
                />
                <DatePicker
                    name="created_end"
                    label="End date"
                    required
                    v-model="filter.created_end"
                    :value="filter.created_end"
                    :min="filter.created_start"
                />
            </div>

            <MultipleSelect
                label="Status"
                placeholder="Choose Status"
                :items="status.map((row:any)=>{
                    return {
                        id : row,
                        value : row
                    }
                })"
                v-model="filter.status"
                :selected="filter.status"
                v-if="filter.show_status"
            />

        </div>

        <div class="flex gap-3 justify-end mt-4">
            <ButtonOutlineGrey
                class="w-[100px] py-3"
                x-on:click="filter=false"
                id="cancel-filter"
                @click="resetInput"
            >
                Cancel
            </ButtonOutlineGrey>
            <ButtonPrimary
                class="w-[100px] py-3"
                type="button"
                @click="$emit('filterData')"
            >
                Submit
            </ButtonPrimary>
        </div>
    </Filter>
</template>
<script setup lang="ts">
import Filter from "../../Popup/Filter.vue";
import ButtonOutlineGrey from "@/Components/Button/ButtonOutlineGrey.vue";
import ButtonPrimary from "@/Components/Button/ButtonPrimary.vue";
import DatePicker from "@/Components/Input/DatePicker.vue";
import MultipleSelect from "@/Components/Input/Select/MultipleSelect.vue";
import { getAllQueryParameter } from "@/Plugins/Function/global-function";
import { ref } from "vue";

const props = defineProps(["filter"]);

const status = ref([
    "All",
    "Queue",
    "Progress",
    "Done"
]);

const resetInput = () => {
    const queries = getAllQueryParameter()
    let status: any = []
    props.filter.created_start = ''
    props.filter.created_end = ''

    Object.entries(queries).forEach(([key, value]) => {
        if (key.startsWith("filter[")) {
            const inside = key.match(/filter\[(.*?)\]/g);
            if (!inside) return;

            const mainKey = inside[0].replace("filter[", "").replace("]", "");

            if (key.includes("[0]") || key.includes("[1]") || key.includes("[2]") || key.includes("[3]")) {
                if (!Array.isArray(props.filter[mainKey])) {
                    status = [];
                }
                status.push(value);
            } else {
                props.filter[mainKey] = value;
            }
        }
    })
    props.filter.status = status
    setTimeout(() => {
        props.filter.show_status = false
        setTimeout(() => {
            props.filter.show_status = true
        }, 10);
    }, 400)
}

</script>
