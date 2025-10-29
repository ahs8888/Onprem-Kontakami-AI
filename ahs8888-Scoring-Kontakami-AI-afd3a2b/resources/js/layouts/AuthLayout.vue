<template>
    <main
        class="bg-auth h-full flex flex-col items-center justify-center md:py-6 md:px-6 py-2 px-2 min-h-screen"
    >
        <Head :title="title" />
        <div class="md:w-[250px] w-[70%] mb-3">
            <img :src="ASSET('/img/kontakami-logo.png')" alt="w-full">
        </div>
        <slot />
        <div class="fixed bottom-[-30px] left-[-40px]">
            <AuthLayerLeft />
        </div>
        <div class="fixed bottom-[-40px] right-[-20px] md:block hidden">
            <AuthLayerRight />
        </div>
        <p class="text-[11px] text-dark  bottom-3 mt-14">
            © 2024, <a class="underline" target="_blank">Kontakami AI</a>. All Rights Reserved
        </p>
    </main>
</template>
<script setup lang="ts">
import AuthLayerLeft from "@/components/icon/etc/AuthLayerLeft.vue";
import AuthLayerRight from "@/components/icon/etc/AuthLayerRight.vue";
import { Head } from "@inertiajs/vue3";
import { ASSET } from '@/composables/global-function';

defineProps(["title"]);
</script>
