A
<template>
    <label
        :for="id"
        class="text-[12px] text-dark font-krub font-medium mb-1 block pre-text-content"
        v-bind:class="{
            'text-red': error,
        }"
        v-if="label"
    >
        {{ label }}
        <span class="text-red" v-if="$attrs.required">*</span>
    </label>
    <div
        class="relative mb-2"
        x-data="{input: $el.getAttribute('data-value')}"
        :data-value="$attrs.value || ''"
        v-bind:class="{ 'has-error': error }"
    >
        <i
            v-bind:class="icon"
            class="absolute top-[13px] left-4 text-[16px]"
            v-if="icon"
        ></i>
        <textarea
            x-model="input"
            v-bind="$attrs"
            @input="
                $emit(
                    'update:modelValue',
                    ($event.target as HTMLInputElement).value
                )
            "
            class="border rounded-lg placeholder:text-[#615e5e] px-4 text-[12px] outline-none shadow-none py-3 w-full disabled:bg-[#F3F3F3] resize-none"
            v-bind:class="{
                'border-red': error,
            }"
        >
        </textarea>
        <div class="relative">
            <small
                v-if="error"
                class="mt-[-7px] error-text mb-4 block text-[11px]"
                >{{ error }}</small
            >
            <small
                class="block mt-[-1px] text-[10px] mb-4 text-[#A3A3A3]"
                v-if="help"
                >{{ help }}</small
            >
            <div class="mb-4 absolute right-0 top-0" v-if="$attrs.maxlength">
                <p class="text-[11px] text-dark float-right">
                    <span x-text="input.length"></span>/{{ $attrs.maxlength }}
                </p>
            </div>
        </div>
    </div>
</template>
<script lang="ts">
export default {
    props: ["label", "icon", "help", "id", "error"],
};
</script>
