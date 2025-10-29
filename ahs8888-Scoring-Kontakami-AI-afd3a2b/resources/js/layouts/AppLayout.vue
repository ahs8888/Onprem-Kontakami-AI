<template>
    <Head :title="title" />
    <Sidebar :menus="menus" />
    <FlashAlert />
    <section class="app-wrapper flex min-h-screen flex-col md:ps-sidebar" x-data="{dropdownOpen:'',dropdownProfile:false}">
        <Header :title="title" :back="back" />
        <main class="flex-1 px-4 py-4">
            <slot />
            <div class="h-[65px] md:h-0"></div>
        </main>
        <!-- <BottomNavigation :menus="menus" /> -->
        <ProcessRunner />
    </section>
</template>
<script setup lang="ts">
import FlashAlert from '@/components/layout/FlashAlert.vue';
import ProcessRunner from '@/components/layout/ProcessRunner.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
// import BottomNavigation from "@/components/layout/BottomNavigation.vue";
import IconAdminRecord from '@/components/icon/menu/IconAdminRecord.vue';
import IconAdminPrompt from '@/components/icon/menu/IconAdminPrompt.vue';
import IconSetting from '@/components/icon/menu/IconSetting.vue';
import IconSetup from '@/components/icon/menu/IconSetup.vue';
import Header from '@/components/layout/Header.vue';

import { showAlert } from '@/composables/global-function';
import { joinConnectionBroadcast } from '@/socket';
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted, ref, shallowRef, watch } from 'vue';

defineProps(['title', 'back']);

const page = usePage();

const clientModules = [
    {
        id: 'setup',
        name: 'Setup',
        route: null,
        active: 'setup.*',
        icon: shallowRef(IconSetup),
        subs: [
            {
                id: 'record-analisys',
                name: 'Recording Analysis',
                route: page.props.auth.user.app != 'admin' ? route('setup.recording-analysis.prompt.index') : null,
                active: 'setup.recording-analysis.*',
            },
            {
                id: 'agent-analisys',
                name: 'Agent Analysis',
                route: page.props.auth.user.app != 'admin' ? route('setup.agent-analysis.prompt.index') : null,
                active: 'setup.agent-analysis.*',
            },
        ],
    },
    {
        id: 'setting',
        name: 'Setting',
        route: page.props.auth.user.app != 'admin' ? route('setting.cloud-location.index') : null,
        active: 'setting.*',
        icon: shallowRef(IconSetting),
        subs: [],
    },
];

const adminModules = [
    {
        id: 'admin-record',
        name: 'AI Call Data Record',
        route: page.props.auth.user.app == 'admin' ? route('admin.analysis-record.index') : null,
        active: 'admin.analysis-record.*',
        icon: shallowRef(IconAdminRecord),
        subs: [],
    }, {
        id: 'prompt-setup',
        name: 'Prompt Setup',
        route: page.props.auth.user.app == 'admin' ? route('setup.recording-analysis.prompt.index') : null,
        active: 'setup.recording-analysis.*',
        icon: shallowRef(IconAdminPrompt),
        subs: [],
    },
];

const menus = ref(page.props.auth.user.app == 'admin' ? adminModules : clientModules);

const watchAlert = () => {
    const alert = page.props.flash;
    if (alert?.success) {
        showAlert(alert.success, 'success');
    }
    if (alert?.error) {
        showAlert(alert.error);
    }
};
watch(
    () => page.props.flash,
    () => {
        watchAlert();
    },
);

onMounted(() => {
    joinConnectionBroadcast();
    watchAlert();
});
</script>
