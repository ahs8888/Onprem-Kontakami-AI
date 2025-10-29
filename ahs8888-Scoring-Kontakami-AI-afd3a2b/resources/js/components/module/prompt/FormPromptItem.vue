<template>
    <div class="rounded-md px-4 py-3">
        <div class="flex items-center justify-between">
            <h2 class="font-krub-semibold text-[14px]">
                {{ label }}
            </h2>
            <ButtonAddWhite label="Add Prompt" @click="$emit('add', type)" />
        </div>
        <div class="mt-3 rounded-md bg-white px-3 py-2" v-if="!items.length">
            <h3 class="my-5 text-center font-krub-bold text-[18px]">No data found</h3>
        </div>
        <ul v-else class="mt-3 flex flex-col gap-3">
            <li v-for="(item, index) in items" :key="index" class="relative rounded-md bg-white px-5 py-4">
                <ButtonIconDelete class="absolute top-3 right-5" @click="$emit('deleteItem', type, index)" />
                <Input
                    label="Name"
                    placeholder="Enter Name"
                    :name="`item_name_${index}`"
                    :id="`item_name_${index}`"
                    v-model="item.name"
                    :value="items[index].name"
                    maxlength="100"
                />
                <div class="mt-7 flex justify-end">
                    <ButtonAddWhite label="Add Item" @click="$emit('addItemScoring', type, index)" />
                </div>
                <ul class="mt-3 flex flex-col gap-3" v-if="item?.items.length">
                    <li v-for="(scoring, x) in item.items" :key="x" class="relative rounded-md border bg-[#E8EDF5] px-5 py-4">
                        <ButtonIconDelete class="absolute top-3 right-5" @click="$emit('deleteItemScoring', type, index, x)" />
                        <Input
                            :label="type == 'scorings' ? 'Scoring Subject' : 'Non-Scoring Subject'"
                            placeholder="Enter Scoring Subject"
                            :name="`scoring_point_${index}_${x}`"
                            :id="`scoring_point_${index}_${x}`"
                            v-model="scoring.point"
                            :value="items[index].items[x].point"
                            maxlength="100"
                        >
                            <template #label>
                                <TooltipInformation v-if="type == 'scorings'">
                                    The point (or subject) within the conversation that you need to put into score
                                </TooltipInformation>
                            </template>
                        </Input>
                        <div class="mb-3" v-if="type == 'scorings'">
                            <label class="mb-1 flex items-center gap-1 font-krub-medium text-[12px] text-dark">
                                Scoring Value
                                <TooltipInformation v-if="type == 'scorings'">
                                    Scoring weight to assess conversations. The default value is 1-5 and you can change the value as you like
                                </TooltipInformation>
                            </label>
                            <div class="flex items-center gap-3">
                                <div class="flex flex-col">
                                    <input
                                        @keypress="isInputNumber($event)"
                                        type="number"
                                        :name="`scoring_point_score${index}_${x}`"
                                        :id="`scoring_point_score${index}_${x}`"
                                        v-model="scoring.score"
                                        max="100"
                                        class="min-h-[42px] w-[70px] rounded-lg border px-4 py-2 text-[12px] shadow-none outline-none placeholder:text-[#615e5e]"
                                    />
                                    <span class="text-[11px]"> Min Value </span>
                                </div>
                                <span class="mt-[-15px]">
                                    -
                                </span>
                                <div class="flex flex-col">
                                    <input
                                        @keypress="isInputNumber($event)"
                                        type="number"
                                        :name="`scoring_point_max_${index}_${x}`"
                                        :id="`scoring_point_max_${index}_${x}`"
                                        v-model="scoring.score_max"
                                        max="100"
                                        class="min-h-[42px] w-[70px] rounded-lg border px-4 py-2 text-[12px] shadow-none outline-none placeholder:text-[#615e5e]"
                                    />
                                    <span class="text-[11px]"> Max Value </span>
                                </div>
                                <div class="flex flex-col">
                                    <input
                                        :name="`scoring_point_satuann_${index}_${x}`"
                                        :id="`scoring_point_satuann_${index}_${x}`"
                                        v-model="scoring.unit"
                                        max="25"
                                        maxlength="25"
                                        class="min-h-[42px] w-[170px] rounded-lg border px-4 py-2 text-[12px] shadow-none outline-none placeholder:text-[#615e5e]"
                                    />
                                    <span class="text-[11px]"> Unit </span>
                                </div>
                            </div>
                        </div>
                        <Textarea
                            label="Prompt"
                            placeholder="Enter Prompt"
                            :name="`scoring_point_prompt_${index}_${x}`"
                            :id="`scoring_point_prompt_${index}_${x}`"
                            v-model="scoring.prompt"
                            :value="items[index].items[x].prompt"
                            maxlength="10000"
                            rows="10"
                        >
                            <template #label>
                                <TooltipInformation v-if="type == 'scorings'">
                                    Create effective prompts, focus on clarity, specificity, and providing enough context for the AI to understand
                                    your desired output. Structure your prompts as you would ask a person.
                                </TooltipInformation>
                            </template>
                        </Textarea>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</template>
<script setup lang="ts">
import ButtonAddWhite from '@/components/button/ButtonAddWhite.vue';
import ButtonIconDelete from '@/components/button/ButtonIconDelete.vue';
import TooltipInformation from '@/components/etc/TooltipInformation.vue';
import Input from '@/components/input/Index.vue';
import Textarea from '@/components/input/Textarea.vue';
import { isInputNumber } from '@/composables/global-function';
defineProps<{
    label: string;
    items: any;
    type: string;
}>();

defineEmits(['add', 'addItemScoring', 'deleteItem', 'deleteItemScoring']);
</script>
