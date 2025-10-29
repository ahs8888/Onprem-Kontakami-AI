<template>
    <div
        :data-checked="modelValue"
        x-data="{ switchOn: $el.getAttribute('data-checked')==='true'?true:false }"
        class="flex items-center justify-center space-x-2"
    >
        <button
            x-ref="switchButton"
            type="button"
            x-on:click="switchOn = !switchOn"
            @click="$emit('update:modelValue', !modelValue);$emit('change',$event)"
            :data-value="modelValue"
            x-bind:class="switchOn ? 'bg-[#3943B7]' : 'bg-neutral-200'"
            class="relative inline-flex h-6 py-0.5 focus:outline-none rounded-full w-10"
            x-cloak
        >
            <span
                x-bind:class="
                switchOn
                    ? 'translate-x-[18px]'
                    : 'translate-x-0.5'
                "
                class="w-5 h-5 duration-200 ease-in-out bg-white rounded-full shadow-md"
            ></span>
        </button>
    </div>
</template>
<script setup lang="ts">
defineProps<{
    modelValue: any;
}>();
</script>
