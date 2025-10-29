<template>
    <main
        class="bg-auth h-full flex flex-col items-center justify-center md:py-6 md:px-6 py-2 px-2 min-h-screen"
    >
        <Head :title="title" />
        <div class="md:w-[250px] w-[70%] mb-3">
            <AppLogoLg class="w-full" />
        </div>
        <slot />
        <div class="fixed bottom-[-30px] left-[-40px]">
            <AuthLayerLeft />
        </div>
        <div class="fixed bottom-[-40px] right-[-20px] md:block hidden">
            <AuthLayerRight />
        </div>
        <p class="text-[11px] text-dark fixed bottom-3">
            © 2024, <span class="underline">Kontakami AI</span>. All Rights Reserved
        </p>
    </main>
</template>
<script setup lang="ts">
import AuthLayerLeft from "@/Components/Icon/Etc/AuthLayerLeft.vue";
import AuthLayerRight from "@/Components/Icon/Etc/AuthLayerRight.vue";
import AppLogoLg from "@/Components/Icon/Logo/AppLogoLg.vue";
import { Head } from "@inertiajs/vue3";

defineProps(["title"]);
</script>
