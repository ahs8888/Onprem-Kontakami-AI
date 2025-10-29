<template>
    <div>
        <h4 class="mb-2 font-krub-bold text-[15px]">Summary</h4>
        <div class="flex flex-col gap-3 rounded-sm border bg-[#F7FAFC] px-4 py-2">
            <div>
                <p class="font-krub-semibold text-[13px]">
                    {{ summary.attitude_title || 'Sikap Agen & Kualitas Pelayanan' }} :
                </p>
                <p class="mt-[2px] text-[12px] whitespace-pre-line">
                    {{ summary.attitude }}
                </p>
            </div>
            <div>
                <p class="font-krub-semibold text-[13px]">
                    {{ summary.recomendation_title || 'Rekomendasi Perbaikan' }} :
                </p>
                <p class="mt-[2px] text-[12px] whitespace-pre-line">
                    {{ summary.recomendation }}
                </p>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
defineProps<{
    summary: any;
}>();
</script>
