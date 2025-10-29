<template>
    <div
        x-data="{
               dropdownOpen: false
      }"
    >
        <span class="mt-[4px] cursor-pointer" x-on:click="dropdownOpen=true" x-ref="button">
            <i class="isax-b icon-info-circle text-[14px] text-yellow"></i>
        </span>

        <div
            x-show="dropdownOpen"
            x-on:click.away="dropdownOpen=false"
            x-transition:enter="ease-out "
            x-transition:enter-start="-translate-y-2"
            x-transition:enter-end="translate-y-0"
            class="absolute z-1"
            x-cloak
            x-anchor="$refs.button"
        >
            <div class="mt-1 rounded-md border border-neutral-200/70 bg-white px-3 py-2 text-sm  shadow-md text-[11px] text-label max-w-[300px] leading-5">
               <slot/>
            </div>
        </div>
    </div>
</template>
