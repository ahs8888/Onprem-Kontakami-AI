<template>
    <Filter class="md:max-w-lg overflow-auto" :reset="route('recordings.detail', id)" @close="resetInput">
        <div class="max-h-[65vh] overflow-auto -mx-6 px-6">
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

const props = defineProps(["filter", "id"]);

const status = ref([
    "All",
    "Success",
    "Progress",
    "Failed",
]);


const resetInput = () => {
    const queries = getAllQueryParameter()
    let status: any = []

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
            }
        }
    })
    props.filter.status = status
    props.filter.show_status = false

    setTimeout(() => {
        props.filter.show_status = true
    }, 10)
}


</script>
