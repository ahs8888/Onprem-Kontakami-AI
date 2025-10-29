<template>
    <div class="flex items-center gap-5">
        <Link
            v-for="tab in tabs"
            :href="tab.route"
            class="border border-[#E1E3E6] bg-white text-sm text-[#333030] rounded-full px-3 py-2"
            :class="{
                '!bg-primary !border-primary !text-white': tab.active == active
            }"
            :key="tab.active"
        >
            {{ tab.name }}
        </Link>
    </div>

</template>

<script setup lang="ts">
    import { Link } from '@inertiajs/vue3'
    import { ref } from 'vue'

    defineProps(["active"])

    const tabs = ref([
        {
            name: 'Clouds Location',
            route: route('setting.clouds-location'),
            active: 'clouds'
        },
        {
            name: 'Access Token',
            route: route('setting.access-token'),
            active: 'access-token'
        },
        {
            name: 'Personal Setting',
            route: route('setting.personal-setting'),
            active: 'personal'
        },
    ])

</script>
