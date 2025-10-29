<template>
    <section
        class="w-sidebar border-r h-full bg-white fixed md:flex hiddenxx flex-col main-sidebar z-10"
    >
        <nav id="head nav-sidebar-application" class="h-[60px] flex items-center justify-center">
            <Link href="" class="py-3 pb-1 flex justify-center">
                <KontakamiLogo x-show="!sidebarCollapse" />
                <KontakamiLogoMini class="mb-3 mt-2 h-[30px]" x-show="sidebarCollapse" />
            </Link>
        </nav>

        <nav
            class="px-3 flex-1 overflow-auto main-nav bg-yellow pt-3 h-full"
            x-data="{ 
               activeAccordion: '', 
               setActiveAccordion(id) { 
                    this.activeAccordion = (this.activeAccordion == id) ? '' : id 
               } 
            }"
        >
            <ul class="text-[13px] font-krub-semibold menu-list">
                <li
                    x-data="{ id: $id('accordion'),show : false  }"
                    v-for="menu in menus"
                    :key="menu.id"
                    class="nav-item mb-1"
                    :class="{
                        active: route().current(menu.active),
                        'has-submenu': menu.subs.length,
                    }"
                >
                    <Link
                        :href="menu.route || 'javascript:;'"
                        class="nav-link nav-parent w-full flex items-center gap-2 py-3 px-3 rounded-md text-white h-[40px]"
                        v-if="!menu.subs.length"
                    >
                        <component :is="menu.icon"></component>
                        <span class="menu-name">{{ menu.name }}</span>
                    </Link>
                    <a
                        href="javascript:;"
                        class="nav-link nav-parent w-full flex items-center gap-2 py-3 px-3 rounded-md text-white h-[40px]"
                        x-bind:class="show || (activeAccordion==='' && $el.getAttribute('data-active')==='true') ? 'sub-show' : ''"
                        :data-active="route().current(menu.active)"
                        x-on:click="show=!show"
                        v-else
                    >
                        <component :is="menu.icon"></component>
                        <span class="menu-name">{{ menu.name }}</span>
                    </a>
                    <ul
                        class="submenu collapse"
                        :data-active="route().current(menu.active)"
                        v-if="menu.subs.length"
                        x-bind:class="show || (activeAccordion==='' && $el.getAttribute('data-active')==='true') ? 'show' : ''"
                        x-cloak
                    >
                        <li v-for="sub in menu.subs" :key="sub.id">
                            <Link
                                :href="sub.route"
                                class="nav-link text-white rounded-md"
                                :class="{ active: route().current(sub.active) }"
                                :id="sub.active"
                            >
                                {{ sub.name }}
                            </Link>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <button
            type="button"
            class="border-2 border-yellow rounded-full w-[30px] h-[30px] flex items-center justify-center absolute bg-white right-[-15px] bottom-[70px] button-collapse-sidebar"
            x-on:click="sidebarCollapse=!sidebarCollapse"
        >
            <i
                class="isax icon-arrow-right-3 text-[18px] text-yellow transition-all"
                x-bind:class="sidebarCollapse?'':'rotate-180'"
            ></i>
        </button>
    </section>
</template>

<script setup lang="ts">
import KontakamiLogo from "../icon/logo/KontakamiLogo.vue";
import KontakamiLogoMini from "../icon/logo/KontakamiLogoMini.vue";
import { Link } from "@inertiajs/vue3";

defineProps(['menus'])
</script>
