<template>
    <div class="flex justify-end" x-data="{dropdownOpen:false}">
        <slot />
        <ul
            x-show="dropdownOpen"
            x-on:click.away="dropdownOpen=false"
            class="dropdown-button-main absolute right-0 z-10 mt-2 w-fit rounded-md border bg-white text-left text-[11px] shadow-lg"
            v-bind:class="`${$attrs.top || 'top-5'} ${$attrs.elementClass}`"
            x-transition:enter-end="translate-y-0"
            x-anchor.bottom-end="$refs.buttonDropdown"
        >
            <slot name="items"/>
        </ul>
    </div>
</template>
