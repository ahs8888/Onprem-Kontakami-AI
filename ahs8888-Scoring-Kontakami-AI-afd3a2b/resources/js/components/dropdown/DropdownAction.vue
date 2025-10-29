<template>
    <div class="flex justify-end" x-data="{id:$id('dropdown_item')}">
        <button type="button"  x-on:click="dropdownOpen==id?dropdownOpen='' : dropdownOpen=id;dropdownProfile=false" x-ref="buttonDotDropdown" class="ps-3">
            <DotDropdown class="rel w-[15px] cursor-pointer" />
        </button>
        <ul
            x-show="dropdownOpen==id"
            x-on:click.away="dropdownOpen=''"
            class="dropdown-button-main absolute right-0 z-10 w-fit rounded-md border bg-white text-left text-[11px] mt-2 shadow-lg"
            v-bind:class="`${$attrs.top || 'top-5'} ${$attrs.elementClass}`"
            x-transition:enter-end="translate-y-0"
            x-anchor.bottom-start="$refs.buttonDotDropdown"
        >
            <slot />
        </ul>
    </div>
</template>
<script setup lang="ts">
import DotDropdown from '../icon/etc/DotDropdown.vue';
</script>
