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
        <div
            class="border rounded-md overflow-hidden"
            v-bind:class="{ 'border-red': error }"
        >
            <Vue3Signature
                ref="signature"
                :sigOption="state.option"
                :h="'200px'"
                class="h-[300px]"
            ></Vue3Signature>
        </div>
        <div class="flex flex-col">
            <small
                v-if="error"
                class=" error-text  text-[11px]"
                >{{ error }}</small
            >
            <span class="text-[11px] text-[#6b6868]">
                Please sign inside the box and click Save to proceed
            </span>
        </div>
        <div class="flex justify-end gap-2">
            <button
                type="button"
                class="bg-primary text-white text-[12px] px-[7px] py-[3px] rounded-md mt-2 disabled:bg-[#ddd] disabled:cursor-not-allowed"
                @click="reset"
            >
                Reset
            </button>

            <button
                type="button"
                class="bg-primary text-white text-[12px] px-[7px] py-[3px] rounded-md mt-2 disabled:bg-[#ddd] disabled:cursor-not-allowed"
                @click="save"
            >
                {{ labelSave }}
            </button>
        </div>
    </div>
</template>
<script lang="ts" setup>
import Vue3Signature from "vue3-signature";
import { ref, reactive } from "vue";
import { showAlert, base64ToFile } from "@/Plugins/Function/global-function";

defineProps(["label", "icon", "help", "id", "error"]);
const emit = defineEmits(["update:modelValue"]);
const state = reactive({
    count: 0,
    option: {
        penColor: "rgb(0, 0, 0)",
        backgroundColor: "rgb(255,255,255)",
    },
});

const signature: any = ref(null);
const labelSave = ref("Save");

const reset = () => {
    signature.value?.clear();
    emit("update:modelValue", null);
};

const save = () => {
    if (signature.value?.isEmpty()) {
        showAlert("Please draw signature first");
    } else {
        labelSave.value = "Saved !";
        const base64Image = signature.value.save("image/jpg");
        const file = base64ToFile(base64Image, "signature.jpg");
        emit("update:modelValue", file);
        setTimeout(() => {
            labelSave.value = "Save";
        }, 1000);
    }
};
</script>
