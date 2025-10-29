<template>
    <nav class="bottom-navigation bottom-0 w-full overflow-auto border-t bg-yellow py-1 md:hidden">
        <ul class="menu-list flex items-center justify-center gap-10 font-krub-semibold text-[13px]">
            <li
                x-data="{ 
                    id: $id('accordion'),
                    dropdownOpen: false
                }"
                v-for="menu in menus"
                :key="menu.id"
                class="nav-item"
                :class="{
                    active: route().current(menu.active),
                }"
            >
                <Link
                    :href="menu.route || 'javascript:;'"
                    class="nav-link nav-parent flex w-full flex-col items-center gap-1 rounded-md px-4 py-2 text-white"
                    v-if="!menu.subs.length"
                >
                    <component :is="menu.icon"></component>
                    <span class="menu-name text-[10px] whitespace-nowrap">{{ menu.name }}</span>
                </Link>
                <a
                    href="javascript:;"
                    class="nav-link nav-parent flex w-full flex-col items-center gap-1 rounded-md px-4 py-2 text-white"
                    x-on:click="dropdownOpen=true"
                    x-ref="button"
                    v-else
                >
                    <component :is="menu.icon"></component>
                    <span class="menu-name text-[10px] whitespace-nowrap">{{ menu.name }}</span>
                </a>

                <ul
                    x-show="dropdownOpen"
                    x-on:click.away="dropdownOpen = false"
                    class="absolute bg-white rounded-md border mb-3"
                    x-anchor.top="$refs.button"
                    x-cloak
                    v-if="menu.subs.length"
                >
                        <li v-for="sub in menu.subs" :key="sub.id">
                            <Link
                                :href="sub.route"
                                class="nav-link rounded-md text-black text-[13px] px-3 py-2 block"
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
</template>
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
defineProps(['menus']);
</script>
