<template>
    <div class="flex justify-between items-baseline">
        <ul class="mt-[-4px] mb-3 flex gap-2 font-krub-semibold text-[14px]">
            <li v-for="(breadcrumb, index) in breadcrumbs" :key="index" class="flex items-center">
                <span v-if="!breadcrumb.route">
                    {{ breadcrumb.label }}
                </span>
                <Link :href="breadcrumb.route" v-else>
                    {{ breadcrumb.label }}
                </Link>
                <i class="isax icon-arrow-right-3 ms-2 mt-[2px] text-[15px]" v-if="index != breadcrumbs.length - 1"></i>
            </li>
        </ul>
        <div>
            <slot/>
        </div>
    </div>
</template>
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
defineProps<{
    breadcrumbs: any;
}>();
</script>
