<template>
    <input
        type="number"
        autocomplete="off"
        class="border h-[60px] w-[60px] rounded-2xl outline-none text-center shadow-none font-krub font-semibold text-[23px]"
        maxlength="1"
        name="otp[]"
        @keypress="isNumber($event)"
        @copy="disableCopyPaste"
        @paste="disableCopyPaste"
        @input="
            inputOtp($event);
            $emit(
                'update:modelValue',
                ($event.target as HTMLInputElement).value
            );
        "
        v-bind="$attrs"
    />
</template>

<script setup lang="ts">
import { isNumber, disableCopyPaste } from "@/Plugins/Function/global-function";

const inputOtp = (event: any) => {
    if (event.target.value) {
        const value = event.target.value.toString().charAt(0);
        event.target.value = value;
        if (event.target.nextElementSibling)
            event.target.nextElementSibling.focus();
    }
};
</script>
