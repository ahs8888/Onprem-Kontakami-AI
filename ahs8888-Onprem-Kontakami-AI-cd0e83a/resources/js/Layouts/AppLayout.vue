<template>
    <Head :title="title" />
    <Sidebar :menus="menus" />
    <FlashAlert />
    <section class="app-wrapper md:ps-[250px] flex flex-col h-full">
        <Header :title="title" />
        <main class="flex-1">
            <slot />
            <div class="md:h-0 h-[65px]"></div>
        </main>
        <BottomNavigation :menus="menus" />
    </section>

    <UploadProgress />
</template>
<script setup lang="ts">
import Sidebar from "@/Components/Layout/Sidebar.vue";
import FlashAlert from "@/Components/Layout/FlashAlert.vue";
import BottomNavigation from "@/Components/Layout/BottomNavigation.vue";
import Header from "@/Components/Layout/Header.vue";
import { showAlert } from "@/Plugins/Function/global-function";
import { ref, shallowRef, watch, onMounted } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import IcMenuRecording from "@/Components/Icon/Menu/icMenuRecording.vue";
import IcMenuSetting from "@/Components/Icon/Menu/icMenuSetting.vue";
import IcMenuTicket from "@/Components/Icon/Menu/icMenuTicket.vue";
import UploadProgress from "@/Components/Layout/UploadProgress.vue";
import { provideUploadState } from "@/Hooks/uploadState";
import IcMenuRecordingActive from "@/Components/Icon/Menu/icMenuRecordingActive.vue";
import IcMenuSettingActive from "@/Components/Icon/Menu/icMenuSettingActive.vue";
import IcMenuTicketActive from "@/Components/Icon/Menu/icMenuTicketActive.vue";
const uploadState = provideUploadState();


defineProps(["title"]);


const menus = ref([
    {
        name: "Recording",
        route: route("recordings.index"),
        active: "recordings.*",
        icon: shallowRef(IcMenuRecording),
        iconPrimary: shallowRef(IcMenuRecordingActive),
        enable: true,
        show: true,
    },
    {
        name: "Ticket Import",
        route: route("ticket-import.index"),
        active: "ticket-import.*",
        icon: shallowRef(IcMenuTicket),
        iconPrimary: shallowRef(IcMenuTicketActive),
        enable: true,
        show: true,
    },
    {
        name: "Setting",
        route: route("setting.clouds-location"),
        active: "setting.*",
        icon: shallowRef(IcMenuSetting),
        iconPrimary: shallowRef(IcMenuSettingActive),
        enable: true,
        show: true,
    }
]);

const page = usePage();

const watchAlert = () => {
    const alert = page.props.flash;
    if (alert?.success) {
        showAlert(alert.success, "success");
    }
    if (alert?.error) {
        showAlert(alert.error);
    }
};
watch(
    () => page.props.flash,
    (newValue, oldValue) => {
        watchAlert();
    }
);

onMounted(() => {
    watchAlert();
});
</script>
