<template>
    <header
        class="bg-white w-full border-b p-4 py-3 flex justify-between sticky top-0 z-[51]"
        v-if="user"
        x-data="{confirmation:false}"
    >
        <div v-if="title">
            <p class="text-sm font-semibold mb-0">{{ title }}</p>
        </div>
        <div>
            <!-- <AppLogoSm class="md:hidden w-[35px] h-[35px]" /> -->
            <YelowLogoMini class="md:hidden w-[35px] h-[35px]" />
        </div>
        <ul class="flex gap-2 items-center">
            <li>
                <span class="border-l border-darks"></span>
            </li>
            <li x-data="{ dropdownProfile: false }">
                <button
                    type="button"
                    class="flex text-end gap-2 items-center"
                    x-ref="dropdownProfile"
                >
                    <div class="ps-5">
                        <b class="text-[12px] line-clamp-2" id="header-user-name">
                            {{ user.name }}
                        </b>
                    </div>
                    <img
                        :src="user.avatar"
                        :alt="user.name"
                        id="header-user-profile"
                        class="w-[34px] h-[30px] rounded-full object-cover border cursor-pointer"
                        x-on:click="dropdownProfile=true"
                    />
                    <i class="isax icon-arrow-down-1 cursor-pointer text-[12px] ms-[-4px]" x-on:click="dropdownProfile=true"></i>
                </button>
                <div
                    x-show="dropdownProfile"
                    x-clock
                    x-on:click.away="dropdownProfile=false"
                    x-anchor.bottom-end="$refs.dropdownProfile"
                    class="absolute z-50 mt-3 w-fit whitespace-nowrap"
                >
                    <div
                        class="py-1 bg-white border rounded-md shadow-sm border-neutral-200/70 w-fit min-w-[130px] text-neutral-700"
                    >
                        <ul
                            class="font-krub font-semibold text-[13px] px-1 min-w-[100px]"
                        >
                            <li>
                                <button
                                    type="button"
                                    x-on:click="confirmation=true"
                                    class="text-red text-[12px] w-full flex gap-2 items-center px-3 py-[7px] rounded-md hover:bg-[#E6E9EF] cursor-pointer"
                                >
                                    <i
                                        class="isax-b icon-logout rotate-180 text-[15px]"
                                    ></i>
                                    <span>Logout</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>

        <ConfirmationSubmit
            confirmation="Are you sure you want to logout?"
            @action="logout"
        />
    </header>
</template>

<script setup lang="ts">
import ConfirmationSubmit from "@/Components/Popup/ConfirmationSubmit.vue";
import YelowLogoMini from "../Icon/Logo/YelowLogoMini.vue";
import { Link, router, usePage } from "@inertiajs/vue3";

const user = usePage().props.auth?.user;

defineProps(["title"])

const logout = () => {
    router.get(route("auth.logout"));
};
</script>
