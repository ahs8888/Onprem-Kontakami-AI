<template>
    <header class="sticky top-0 z-30 flex h-[60px] w-full justify-between border-b bg-white p-4 py-4" v-if="user" x-data="{confirmation:false}">
        <div class="flex w-full items-center gap-2">
            <KontakamiLogoMini class="h-[35px] w-[35px] md:hidden" />
            <div class="flex items-center gap-2">
                <Link :href="back" v-if="back" class="flex items-center gap-2 font-krub-bold text-[15px] md:text-[17px]">
                    <i class="isax icon-arrow-left text-[20px]"></i>
                    {{ title }}
                </Link>
                <h1 class="font-krub-bold text-[15px] md:text-[17px]" v-else>
                    {{ title }}
                </h1>
            </div>
        </div>
        <ul class="flex items-center gap-2">
            <li>
                <button type="button" class="flex !cursor-default items-center gap-2 text-end" x-ref="dropdownProfile">
                    <div class="w-[150px]">
                        <b class="line-clamp-2 text-[12px]" id="header-user-name">
                            {{ user.name }}
                        </b>
                    </div>
                    <img
                        :src="user.avatar"
                        :alt="user.name"
                        id="header-user-profile"
                        class="h-[30px] w-[34px] cursor-pointer rounded-full border object-cover"
                        x-on:click="dropdownProfile=true"
                    />
                    <i class="isax icon-arrow-down-1 ms-[-4px] cursor-pointer text-[12px]" x-on:click="dropdownProfile=true"></i>
                </button>
                <div
                    x-show="dropdownProfile"
                    x-clock
                    x-on:click.away="dropdownProfile=false"
                    x-anchor.bottom-end="$refs.dropdownProfile"
                    class="absolute z-2 mt-3 w-fit whitespace-nowrap"
                >
                    <div class="w-fit min-w-[130px] rounded-md border border-neutral-200/70 bg-white py-1 text-neutral-700 shadow-sm">
                        <ul class="min-w-[100px] px-1 font-krub-semibold text-[13px]">
                            <li v-if="user.app != 'admin'">
                                <Link
                                    :href="route('setting.personal.index')"
                                    class="relative flex items-center gap-2 rounded-md px-3 py-[7px] text-[12px] text-dark hover:bg-[#E6E9EF]"
                                >
                                    <i class="isax-b icon-user text-[15px]"></i>
                                    <span>Account</span>
                                </Link>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    x-on:click="confirmation=true"
                                    class="flex w-full items-center gap-2 rounded-md px-3 py-[7px] text-[12px] text-red hover:bg-[#E6E9EF]"
                                >
                                    <i class="isax-b icon-logout rotate-180 text-[15px]"></i>
                                    <span>Logout</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>

        <Confirmation confirmation="Are you sure you want to logout?" @action="logout" />
    </header>
</template>

<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import KontakamiLogoMini from '../icon/logo/KontakamiLogoMini.vue';
import Confirmation from '../popup/Confirmation.vue';

defineProps<{
    title: string;
    back?: string;
}>();
const user = usePage().props.auth?.user;

const logout = () => {
    if (user.app == 'admin') {
        router.get(route('admin.auth.logout'));
    } else {
        router.get(route('auth.logout'));
    }
};
</script>
