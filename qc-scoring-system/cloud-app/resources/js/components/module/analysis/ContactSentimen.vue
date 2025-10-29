<template>
    <div>
        <h2 class="mb-2 font-krub-bold text-[15px]">Contact Sentiment</h2>
        <div className="w-full">
            <div className="relative h-4 w-full bg-gradient-to-r from-red-500 via-orange-400 via-yellow-400 to-lime-500 to-green-500 rounded-full">
                <div
                    className="absolute top-1/2 w-8 h-8 bg-white rounded-full border-3 border-[#3943B7] shadow-md transform -translate-x-1/2 -translate-y-1/2"
                    :style="`left: ${config.position}`"
                ></div>
            </div>
            <div className="flex justify-between text-xs text-slate-500 mt-2">
                <span>Very Negative</span>
                <span>Neutral</span>
                <span>Very Positive</span>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
const props = defineProps(['sentiment']);

const sentimentConfig : any = {
    'Sangat Positif': { position: '95%' },
    Positif: { position: '75%' },
    Netral: { position: '50%' },
    Negatif: { position: '25%' },
    'Sangat Negatif': { position: '5%' },
};

const config = sentimentConfig[props.sentiment] || sentimentConfig['Netral'];
</script>
