<template>
    <label
        :for="id"
        class="text-[12px] text-dark font-krub font-medium mb-1 block pre-text-content"
        v-if="label"
        v-bind:class="{
            'text-red': error,
        }"
    >
        {{ label }}
        <span class="text-red" v-if="$attrs.required">*</span>
    </label>
    <small
        class="block mt-[-5px] mb-2 text-[10px] text-[#7B7B7B]"
        v-if="labelHelp"
        v-html="labelHelp"
    ></small>
    <div
        class="relative mb-2"
        x-data="{input: $el.getAttribute('data-value')}"
        :data-value="$attrs.value || ''"
        v-bind:class="error ? 'has-error' : '' + $attrs.name"
    >
        <i
            v-bind:class="icon"
            class="absolute top-[14px] left-4 text-[14px] text-[#A4A4A4]"
            v-if="icon"
        ></i>
        <span
            v-if="isFileFilled"
            class="border text-[#615e5e] px-4 text-[12px] min-h-[42px] pre-text-content w-full flex items-center justify-between rounded-lg"
        >
            <a
                :href="file"
                target="_blank"
                class="underline text-blue pre-text-content max-w-[95%] py-1"
            >
                {{ getFileName() }}
            </a>
            <button type="button" @click="isFileFilled = false">
                <IconClosePopup class="w-[20px] h-[20px]" />
            </button>
        </span>
        <div v-else class="border rounded-lg flex items-center mb-2">
            <input
                x-model="input"
                v-bind:class="icon ? 'ps-10' : ''"
                @input=""
                @change="changeFile($event)"
                v-bind="$attrs"
                type="file"
                class="border rounded-lg placeholder:text-[#615e5e] px-4 text-[12px] min-h-[42px] outline-none shadow-none py-2 w-full"
            />
            <a :href="preview" v-if="preview" target="_blank" class="px-3">
                <i class="isax  icon-eye text-[15px]"></i>
            </a>
        </div>
        <small
            v-if="error"
            class="mt-[-7px] error-text mb-4 block text-[11px]"
            >{{ error }}</small
        >
        <small
            class="block mt-[-7px] text-[10px] mb-4 text-[#A3A3A3]"
            v-if="help"
            >{{ help }}</small
        >

        <div
            class="mb-4 mt-[-6px]"
            v-if="$attrs.maxlength && hideLength == undefined"
        >
            <p class="text-[10px] text-[#666666] float-right">
                {{ modelValue?.length }}
                /{{ $attrs.maxlength }}
            </p>
        </div>
    </div>
</template>

<script lang="ts" setup>
import IconClosePopup from "../Icon/Etc/IconClosePopup.vue";
import { ref, onMounted } from "vue";
const props = defineProps([
    "modelValue",
    "label",
    "icon",
    "help",
    "error",
    "id",
    "hideLength",
    "labelHelp",
    "file",
]);
const emit = defineEmits(["update:modelValue"])

const isFileFilled = ref(false);
const preview  : any= ref(null)

const isHasValue = () => {
    isFileFilled.value = props.file && typeof props.file !== "object";
};

const getFileName = () => {
    var value = props.file;
    if (value) {
        value = value.split("/");
        const length = value.length;
        if (length) {
            return value[length - 1];
        }
    }
    return "-";
};
const changeFile = (event: any) => {
    var fileValue = event.target.files[0];
    if (fileValue) {
        if (fileValue.size / 1000000 > Number(5)) {
            alert(`File size cannot be more than 5MB`);
            event.target.value = "";
            preview.value = null
        }else{
            preview.value = URL.createObjectURL(event.target.files[0])
            emit('update:modelValue', (event.target as any).files[0])
        }
    }
};

onMounted(() => {
    isHasValue();
});
</script>
