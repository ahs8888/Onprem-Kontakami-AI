<template>
    <main class="bg-[#F6F7FB]">
        <Head :title="title" />
        <header class="bg-white border-b px-5 py-3 sticky top-0 z-10">
            <Link :href="route('ticket.index')">
               <YelowLogoMini />
            </Link>
        </header>
        <section>
            <slot />
        </section>
    </main>
</template>
<script setup lang="ts">
import YelowLogoMini from "@/Components/Icon/Logo/YelowLogoMini.vue";
import { Head,Link } from "@inertiajs/vue3";

defineProps(["title"]);
</script>
