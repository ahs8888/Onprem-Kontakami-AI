<template>
    <label :for="id" class="pre-text-content mb-1 block font-krub-medium text-[12px] text-dark" v-if="label">
        {{ label }}
        <span class="text-red" v-if="$attrs.required">*</span>
    </label>
    <div class="relative mb-2" x-data="{ show: false }" v-bind:class="{ 'has-error': error }">
        <i v-bind:class="icon" class="absolute top-[14px] left-4 text-[14px] text-[#A4A4A4]" v-if="icon"></i>
        <component :is="iconComponent" v-if="iconComponent" class="absolute top-[14px] left-4 h-[14px] w-[14px] text-[#A4A4A4]"></component>
        <input
            v-bind:type="show ? 'text' : 'password'"
            v-bind="$attrs"
            v-bind:class="icon || iconComponent ? 'ps-10' : ''"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            class="mb-2 min-h-[42px] w-full appearance-none rounded-lg border bg-white px-4 py-2 pe-10 text-[12px] shadow-none outline-none placeholder:text-[#615e5e]"
        />
        <i
            class="isax absolute top-[14px] right-4 cursor-pointer text-[14px]"
            v-bind:class="show ? 'icon-eye-slash' : 'icon-eye'"
            @click="show = !show"
        ></i>

        <small v-if="error" class="error-text mt-[-7px] mb-4 block text-[11px]">{{ error }}</small>
        <small class="mt-[-7px] mb-2 block text-[12px] text-[#A3A3A3]" v-if="help">{{ help }}</small>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
defineProps<{
    label?: string;
    iconComponent?: any;
    icon?: string;
    help?: string;
    error?: string;
    id?: string;
}>();

const show = ref(false);
</script>
