<template>
    <section
        class="px-5 mx-auto pt-3 pb-5"
        x-data="{filter:false}"
    >
        <div class="flex mb-3 gap-2 items-center">
            <Link :href="route('recordings.index')">
                <h2 class="text-dark font-semibold text-sm">Recording List</h2>
            </Link>
            <i class="isax icon-arrow-right-3 text-dark"></i>
            <h2 class="text-dark font-semibold text-sm">{{ data.folder_name }}</h2>
        </div>
        <div class="flex justify-end mb-3">
            <div class="flex gap-2 items-center">
                <ButtonOutlineGrey
                    x-on:click="filter=true"
                    icon="isax icon-setting-4"
                >
                    Filter
                </ButtonOutlineGrey>
            </div>
            <FilterPopupDetail
                :filter="filter"
                @filterData="filterData"
                :id="data.id"
            />
        </div>
        <Table :paginate="paginate" :columns="['File Name', 'Status']">
            <tr v-for="row in paginate.data.value">
                <Td>{{ row.name }}</Td>
                <Td
                    :class="{
                        'text-online': row.status == 'Success',
                        'text-kuning': row.status == 'Progress',
                        'text-red': row.status == 'Failed',
                    }"
                >{{ row.status }}</Td>
                <!-- <Td>
                    <div>
                        <Link :href="route('recordings.transcript', { id: data.id, detailId: row.id })" class="underline text-primary" v-if="row.is_transcript">
                            View
                        </Link>
                        <span v-else>-</span>
                    </div>
                </Td> -->
            </tr>
        </Table>

    </section>
</template>
<script setup lang="ts">
import Table from "@/Components/Table/Table.vue";
import Td from "@/Components/Table/Td.vue";
import { Link } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import FilterPopupDetail from "./FilterPopupDetail.vue";
import ButtonOutlineGrey from "@/Components/Button/ButtonOutlineGrey.vue";
import {
    removeAllUrlParameter,
    routeAppendParam,
    closeFilter,
    getAllQueryParameter
} from "@/Plugins/Function/global-function";
defineProps(["paginate", "data"]);

interface FilterType {
    status: string[];
    [key: string]: any;
}

const filter = ref<FilterType>({
    status: [],
    show_status: false
});

const filterData = () => {
    const param = filter.value;
    var filterParam: any = {}

    param.status.forEach((status, index) => {
        filterParam[`filter[status][${index}]`] = status;
    });

    removeAllUrlParameter();
    routeAppendParam(filterParam, false);
    closeFilter();
};

onMounted(() => {
    const queries = getAllQueryParameter()

    Object.entries(queries).forEach(([key, value]) => {
        if (key.startsWith("filter[")) {
            const inside = key.match(/filter\[(.*?)\]/g);
            if (!inside) return;

            const mainKey = inside[0].replace("filter[", "").replace("]", "");

            if (key.includes("[0]") || key.includes("[1]") || key.includes("[2]") || key.includes("[3]")) {
                if (!Array.isArray(filter.value[mainKey])) {
                    filter.value[mainKey] = [];
                }
                filter.value[mainKey].push(value);
            } else {
                filter.value[mainKey] = value;
            }
        }
    })

    filter.value.show_status = true
})
</script>
