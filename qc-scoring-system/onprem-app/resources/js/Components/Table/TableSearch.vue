<template>
    <form
        @submit.prevent="search"
        class="relative md:w-[30%] border rounded-lg ps-8 px-3 outline-none shadow-none py-[4px] mb-3 bg-white h-[40px] flex items-center gap-2"
    >
        <i
            class="isax icon-search-normal-1 absolute top-[13px] left-3 text-[13px]"
        ></i>
        <input
            type="text"
            :placeholder="placeholder || 'Search'"
            id="table-search"
            title="Enter to search"
            @keyup.enter="search"
            v-model="searchText"
            class="outline-none border-0 w-full ring-0 h-[36px] placeholder:text-black text-[12px]"
        />
        <div>
            <IconClose class="cursor-pointer" @click="closeSearch" v-if="searchText" />
        </div>
        <div>
            <ButtonPrimary
            class="!h-[26px] !py-0 flex items-center justify-center text-[10px] !px-2"
            @click="searchButton"
        >
            Search
        </ButtonPrimary>
        </div>
    </form>
</template>
<script setup lang="ts">
import {
    routeAppendParam,
    getQueryParam,
} from "@/Plugins/Function/global-function";
import ButtonPrimary from "../Button/ButtonPrimary.vue";
import IconClose from "../Icon/Etc/IconClose.vue";
import { ref } from "vue";

defineProps(["placeholder"]);


const searchText = ref<any>(getQueryParam('search') || '')
const search = (event: any) => {
    searchText.value = event.target.value
    routeAppendParam({ search: event.target.value }, false);
};

const closeSearch = () => {
    searchText.value = '';
    (document.getElementById('table-search') as HTMLInputElement).value = ''
    routeAppendParam({ search: '' }, false);
}

const searchButton = () => {
    routeAppendParam({ search: searchText.value }, false);
}

</script>
