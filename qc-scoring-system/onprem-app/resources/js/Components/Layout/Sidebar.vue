<template>
    <section
        class="w-[250px] border-r h-full bg-white fixed md:flex hidden flex-col main-sidebar z-[2]"
    >
        <nav id="head nav-sidebar-application" class="flex justify-center h-[55px] items-center">
            <Link href="" class="flex justify-center items-center">
                <YelowLogo x-show="!sidebarCollapse" class="h-[45px]" />
                <YelowLogoMini class="h-[45px]" x-show="sidebarCollapse" />
            </Link>
        </nav>

        <nav
            class="px-3 flex-1 overflow-auto main-nav pt-5"
        >
            <ul class="text-[12px] font-krub font-medium menu-list">
                <li
                    x-data="{ id: $id('accordion') }"
                    v-for="menu in menus"
                    class="nav-item mb-1"
                    :class="{
                        active: route().current(menu.active),
                    }"
                >
                    <a
                        v-if="uploadState.uploading.value"
                        class="nav-link nav-parent w-full flex items-center gap-2 py-3 pb-0"
                        x-bind:class="sidebarCollapse?'h-[48px]':''"
                        :disabled="true"
                    >
                        <component :is="menu.icon"></component>
                        <span class="menu-name">
                            <component :is="menu.iconPrimary" x-show="sidebarCollapse"></component>
                            <span>{{ menu.name }}</span>
                        </span>
                    </a>
                    <Link
                        v-else
                        :href="menu.route || 'javascript:;'"
                        class="nav-link nav-parent w-full flex items-center gap-2 py-3 pb-0"
                        x-bind:class="sidebarCollapse?'h-[48px]':''"
                        :disabled="!menu.enable"
                    >
                        <component :is="menu.icon"></component>
                        <span class="menu-name">
                            <component :is="menu.iconPrimary" x-show="sidebarCollapse"></component>
                            <span>{{ menu.name }}</span>
                        </span>
                    </Link>
                </li>
            </ul>
        </nav>
        <button
            type="button"
            class="border-2 !border-primary rounded-full w-[30px] h-[30px] flex items-center justify-center absolute bg-white right-[-15px] bottom-[70px] cursor-pointer"
            x-on:click="sidebarCollapse=!sidebarCollapse"
        >
            <i
                class="isax icon-arrow-right-3 text-[14px] text-primary transition-all"
                x-bind:class="sidebarCollapse?'':'rotate-180'"
            ></i>
        </button>
    </section>
</template>

<script setup lang="ts">
import { useUploadState } from "@/Hooks/uploadState";
import YelowLogo from "../Icon/Logo/YelowLogo.vue";
import YelowLogoMini from "../Icon/Logo/YelowLogoMini.vue";
import { Link } from "@inertiajs/vue3";

defineProps(['menus'])
const uploadState = useUploadState()
</script>
