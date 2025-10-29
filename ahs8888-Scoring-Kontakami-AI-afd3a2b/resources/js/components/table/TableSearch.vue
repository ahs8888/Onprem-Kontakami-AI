<template>
    <form @submit.prevent="search" class="mb-3 flex min-h-[40px] items-center rounded-lg border bg-white px-3 md:w-[32%]">
        <i class="isax icon-search-normal-1 top-[13px] left-3 text-[13px]"></i>
        <input
            type="text"
            :placeholder="placeholder || 'Search'"
            id="search"
            title="Enter to search"
            autocomplete="off"
            @keyup.enter="search"
            v-model="searchValue"
            class="min-h-[40px] w-full border-0 px-3 py-[7px] text-[12px] shadow-none outline-none placeholder:text-black"
        />
        <button type="button" class="inline-block rounded-full border" v-if="searchValue" @click="clear">
            <IconClosePopup class="h-[20px] w-[20px]" />
        </button>
        <button type="submit" class="ms-3 rounded-md bg-yellow px-2 py-[4px] text-[12px] text-white">Search</button>
    </form>
</template>
<script setup lang="ts">
import { getQueryParam, removeURLParameter, routeAppendParam } from '@/composables/global-function';
import { ref } from 'vue';
import IconClosePopup from '../icon/etc/IconClosePopup.vue';

defineProps(['placeholder']);

const searchValue = ref(getQueryParam('search'));
const search = () => {
    if (searchValue.value) {
        routeAppendParam({ search: searchValue.value, page: 1 }, false);
    }
};

const clear = () => {
    removeURLParameter(['search'], false);
    searchValue.value = '';
};
</script>
