<template>
    <AppLayout title="Recording List">
        <RecordingTranscript :data="data" :detail="detail" />
    </AppLayout>
</template>
<script setup lang="ts">
import RecordingTranscript from "@/Components/Module/Recording/RecordingTranscript.vue";
import AppLayout from "@/Layouts/AppLayout.vue"

const props = defineProps(["data", "detail"])
</script>
