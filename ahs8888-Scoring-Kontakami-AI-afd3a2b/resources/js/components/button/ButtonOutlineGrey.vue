<template>
    <Link v-bind="$attrs" v-bind:class="classElement" :href="href" v-if="href">
        <i v-bind:class="icon" class="text-[15px]" v-if="icon"></i>
        <slot />
    </Link>
    <button v-bind="$attrs" v-bind:class="classElement" :type="type || 'button'" v-if="!href">
        <LoadingButton v-if="$attrs.loading" />
        <i v-bind:class="icon" class="text-[15px]" v-if="icon"></i>
        <slot />
    </button>
</template>
<script lang="ts" setup>
import LoadingButton from '@/components/icon/etc/IconLoadingButton.vue';
import { Link } from '@inertiajs/vue3';

defineProps(['href', 'icon', 'type']);
const classElement =
    'border border-yellow text-yellow bg-white rounded-md py-2 px-3 flex justify-center items-center gap-1 h-fit text-center disabled:bg-[#ddd] disabled:text-[#AFAFAF] disabled:cursor-not-allowed text-[12px]';
</script>
