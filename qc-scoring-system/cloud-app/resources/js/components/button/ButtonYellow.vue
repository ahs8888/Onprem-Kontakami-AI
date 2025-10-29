<template>
    <Link v-bind="$attrs" v-bind:class="classElement" :href="href" v-if="href">
        <i v-bind:class="icon" class="text-[15px]" v-if="icon"></i>
        <slot />
    </Link>
    <button
        v-bind="$attrs"
        v-bind:class="
            classElement + ($attrs.loading ? ' cursor-not-allowed' : '')
        "
        :type="type || 'button'"
        v-if="!href"
    >
        <LoadingButton v-if="$attrs.loading" />
        <i
            v-bind:class="icon"
            class="text-[15px]"
            v-if="icon && !$attrs.loading"
        ></i>
        <slot />
    </button>
</template>

<script lang="ts" setup>
import { Link } from "@inertiajs/vue3";
import LoadingButton from "@/components/icon/etc/IconLoadingButton.vue";

defineProps(["href", "icon", "type"])
const classElement =  "flex justify-center bg-yellow text-white px-3 font-krub-medium py-2 rounded-md text-[12px] items-center gap-1 h-fit text-center disabled:bg-[#ddd] disabled:!cursor-not-allowed";

</script>
