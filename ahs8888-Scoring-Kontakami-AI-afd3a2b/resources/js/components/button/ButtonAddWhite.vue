<template>
    <button
        type="button"
        class="flex items-center justify-center gap-2 rounded-md border border-yellow bg-white px-2 py-[5px] text-[12px] text-yellow"
    >
        <i class="isax icon-add text-[14px]"></i>
        {{ label }}
    </button>
</template>
<script setup lang="ts">
defineProps<{
     label : string
}>()
</script>