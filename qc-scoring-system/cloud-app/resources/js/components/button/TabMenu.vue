<template>
    <ul class="flex gap-3 font-medium mb-3">
        <li v-for="tab in tabs" :key="tab.id">
            <Link
                :href="tab.url"
                class="block rounded-full border bg-white px-4 py-1 text-[13px] hover:bg-yellow hover:text-white"
                v-bind:class="{
                    'bg-yellow text-white': tab.id == active,
                }"
            >
                {{ tab.label }}
            </Link>
        </li>
    </ul>
</template>
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    active: string;
    tabs : any[]
}>();

</script>
