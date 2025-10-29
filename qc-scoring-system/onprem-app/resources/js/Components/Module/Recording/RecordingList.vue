<template>
    <section
        class="px-5 mx-auto pt-3 pb-5"
        x-data="{filter:false, openRowIndex: ''}"
    >
        <h2 class="mb-3 text-dark font-semibold text-sm">Recording List</h2>
        <div class="flex items-center gap-3 bg-[#3943B70D] border !border-primary px-3 py-2 rounded-sm text-primary text-xs mb-3 w-fit">
            <i class="isax-b icon-info-circle text-sm"></i>
            <span>Please note that MP4 file format (all calls made from widget) are not supported at the moment. Development is in progress.</span>
        </div>
        <div class="flex justify-between">
            <TableSearch />
            <div class="flex gap-2 items-center">
                <ButtonOutlineGrey
                    x-on:click="filter=true"
                    icon="isax icon-setting-4"
                >
                    Filter
                </ButtonOutlineGrey>
                <UploadHandler @fetchData="paginate.fetchData()" />
            </div>
            <FilterPopup
                :filter="filter"
                @filterData="filterData"
            />
        </div>

        <Table :paginate="paginate" :columns="['Date', 'Folder Name', 'Total Data', 'Status', 'Success', 'Failed', 'Action']">
            <tr
                v-for="row in paginate.data.value"
                class="cursor-pointer"
                :class="{
                    'bg-unread': !row.is_read
                }"
                @click="showDetail(row)"
            >
                <Td>{{ row.date }}</Td>
                <Td>{{ row.name }}</Td>
                <Td>{{ row.total_data }}</Td>
                <Td
                    :class="{
                        'text-success': row.status == 'Done',
                        'text-kuning': row.status == 'Progress',
                        'text-primary': row.status == 'Queue',
                    }"
                >{{ row.status }}</Td>
                <Td>{{ row.total_success }}</Td>
                <Td>{{ row.total_failed }}</Td>
                <Td @click.stop >
                    <div v-if="row.status != 'Progress'">
                        <a
                            :x-on:click="`openRowIndex = '${row.id}'`"
                            class="rotate-90 cursor-pointer inline-flex" x-ref="anchor"
                        >
                            <i class="isax icon-more"></i>
                        </a>

                        <div
                            :x-show="`openRowIndex === '${row.id}'`"
                            x-anchor="$refs.anchor"
                            x-on:click.away="openRowIndex = ''"
                            class="absolute bg-white shadow-lg border rounded-md mt-1 min-w-[120px] z-50 p-2"
                        >
                            <ul class="text-sm text-dark flex flex-col gap-2">
                                <li class="transition-all rounded-sm py-1 px-2 hover:bg-gray-100 cursor-pointer flex items-center gap-1"
                                    @click="injectRecording(row)"
                                    :class="{
                                        '!cursor-not-allowed text-[#ddd]': uploadState.uploading.value
                                    }"
                                >
                                    <i class="isax icon-folder-add"></i>
                                    <span class="text-xs">Add Recording</span>
                                </li>
                                <li class="transition-all rounded-sm py-1 px-2 hover:bg-gray-100 cursor-pointer flex items-center gap-1" @click="retryRecord(row)" v-if="row.total_failed > 0">
                                    <i class="isax icon-rotate-right"></i>
                                    <span class="text-xs">Retry</span>
                                </li>
                                <li class="transition-all rounded-sm py-1 px-2 hover:bg-gray-100 cursor-pointer text-red flex items-center gap-1" @click="showDelete(row)">
                                    <i class="isax icon-trash"></i>
                                    <span class="text-xs">Delete</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </Td>
            </tr>
        </Table>

        <div x-data="{confirmation:false}" v-if="showPopupDelete">
            <a hidden id="show-delete" x-on:click="confirmation=true"></a>
            <ConfirmationSubmit
                confirmation="Are you sure you want to delete this file?"
                @action="actionDelete"
            />
        </div>

    </section>
</template>
<script setup lang="ts">
import ButtonOutlineGrey from "@/Components/Button/ButtonOutlineGrey.vue";
import UploadHandler from "./UploadHandler.vue";
import TableSearch from "@/Components/Table/TableSearch.vue";
import Table from "@/Components/Table/Table.vue";
import Td from "@/Components/Table/Td.vue";
import FilterPopup from "./FilterPopup.vue";
import { router, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from "vue";
import {
    showAlert,
    validateMinimumDateRange,
    removeAllUrlParameter,
    routeAppendParam,
    closeFilter,
    getQueryParam,
    clickId,
    getAllQueryParameter,
    validateGreaterDateRange
} from "@/Plugins/Function/global-function";
import ConfirmationSubmit from "@/Components/Popup/ConfirmationSubmit.vue";

import { useUploadState } from "@/Hooks/uploadState";
import axios from "axios";

const uploadState = useUploadState()
const showPopupDelete = ref(true)

interface FilterType {
    created_start: string;
    created_end: string;
    status: string[];
    [key: string]: any;
}


const filter = ref<FilterType>({
    created_start: "",
    created_end: "",
    status: [],
    show_status: false
});

const form = useForm({
    id: ''
})

const props = defineProps(["paginate"]);

const filterData = () => {
    const param = filter.value;
    if (
        !param.created_start || !param.created_end
    ) {
        showAlert("Please select created date");
        return;
    }

    if (validateGreaterDateRange(param.created_start, param.created_end)) {
        var filterParam: any = {
            "filter[created_start]": param.created_start || "",
            "filter[created_end]": param.created_end || ""
        };

        param.status.forEach((status, index) => {
            filterParam[`filter[status][${index}]`] = status;
        });

        removeAllUrlParameter();
        routeAppendParam(filterParam, false);
        closeFilter();
    }

};

const showDetail = (row: any) => {
    router.visit(route("recordings.detail", row.id))
}

const actionDelete = () => {
    if (!form.processing) {
        form.post(route('api.recordings.delete-folder'), {
            onFinish: () => {
                props.paginate.fetchData()
                showPopupDelete.value = false

                setTimeout(() => {
                    showPopupDelete.value = true
                }, 100);
            }
        })
    }
}

const showDelete = (row: any) => {
    form.id = row.id

    clickId("show-delete")
}

const injectRecording = (row: any) => {
    if (!uploadState.uploading.value) {
        uploadState.injectId.value = row.id
        clickId('file-upload')
    }
}

const retryRecord = (row: any) => {
    axios.post(route('recordings.retry', row.id))
    props.paginate.fetchData()
}

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
