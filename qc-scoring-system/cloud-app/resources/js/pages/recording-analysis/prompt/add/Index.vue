<template>
    <AppLayout :title="row ? 'Edit Prompt' : 'Add Prompt'" :back="route('setup.recording-analysis.prompt.choise')">
        <section class="mx-auto mb-[100px] rounded-md border bg-white px-5 py-4 md:max-w-[70%]">
            <form @submit.prevent="submit">
                <div
                    v-if="is_admin"
                    :class="{
                        'pointer-events-none cursor-not-allowed': row,
                    }"
                >
                    <SelectSearch
                        label="Choose Company"
                        placeholder="Choose Company"
                        v-model="form.company_user_id"
                        :value="form?.company_user_id"
                        :items="
                            companies.map((row: any) => {
                                return {
                                    label: row.company,
                                    value: row.id,
                                };
                            })
                        "
                    />
                </div>
                <Input
                    label="Prompt Name"
                    placeholder="Enter Prompt Name"
                    name="name"
                    id="name"
                    v-model="form.name"
                    :value="form.name"
                    maxlength="100"
                />
                <FormPromptItem
                    label="Prompts for Scoring"
                    class="mb-4 bg-[#E1EEDA]"
                    type="scorings"
                    :items="form.scorings"
                    @add="addItem"
                    @addItemScoring="addItemScoring"
                    @deleteItem="deleteItem"
                    @deleteItemScoring="deleteItemScoring"
                />
                <FormPromptItem
                    label="Prompts for Non-Scoring"
                    class="bg-[#F7E6D3]"
                    type="non_scorings"
                    :items="form.non_scorings"
                    @add="addItem"
                    @addItemScoring="addItemScoring"
                    @deleteItem="deleteItem"
                    @deleteItemScoring="deleteItemScoring"
                />
                <div class="mt-3 rounded-md bg-[#D1F2F9] px-4 py-3">
                    <div class="flex items-center justify-between">
                        <h2 class="font-krub-semibold text-[14px]">Summary</h2>
                        <ButtonAddWhite label="Add Item" @click="addSummary" />
                    </div>
                    <div class="mt-3 rounded-md bg-white px-3 py-2" v-if="!form.summary.length">
                        <h3 class="my-5 text-center font-krub-bold text-[18px]">No data found</h3>
                    </div>
                    <ul class="mt-3 flex flex-col gap-3">
                        <li class="relative rounded-md border bg-[#E8EDF5] px-5 py-4" v-for="(summary, index) in form.summary as any" :key="index">
                            <ButtonIconDelete class="absolute top-3 right-5" @click="deleteSummary(index)" />
                            <Input
                                label="Name"
                                placeholder="Enter Name"
                                name="attitude_name"
                                id="attitude_name"
                                v-model="summary.point"
                                :value="summary.point"
                                maxlength="100"
                            />
                            <Textarea
                                label="Prompt"
                                placeholder="Enter Prompt"
                                name="attitude_prompt"
                                id="attitude_prompt"
                                v-model="summary.prompt"
                                :value="summary.prompt"
                                maxlength="10000"
                                rows="10"
                            />
                        </li>
                    </ul>
                </div>
                <div class="fixed bottom-0 left-0 z-[2] w-full border-t bg-white py-5 shadow-2xl">
                    <div class="mx-auto flex justify-end gap-2 md:max-w-[75%]">
                        <ButtonOutlineGrey :href="route('setup.recording-analysis.prompt.choise')" class="px-8"> Back </ButtonOutlineGrey>
                        <ButtonYellow
                            type="submit"
                            class="px-7"
                            :loading="form.processing"
                            :disabled="
                                form.processing ||
                                isEmptyValue(form.name) ||
                                !form.scorings.length ||
                                !form.summary.length ||
                                // !form.non_scorings.length ||
                                !form.scorings.every((item: any) => !isEmptyValue(item.name)) ||
                                !form.non_scorings.every((item: any) => !isEmptyValue(item.name)) ||
                                !form.scorings.every((item: any) => item.items.length) ||
                                !form.non_scorings.every((item: any) => item.items.length) ||
                                !form.scorings.every((item: any) =>
                                    item.items.every(
                                        (row: any) =>
                                            !isEmptyValue(row.point) &&
                                            !isEmptyValue(row.prompt) &&
                                            !isEmptyValue(row.score) &&
                                            !isEmptyValue(row.score_max),
                                    ),
                                ) ||
                                !form.non_scorings.every((item: any) =>
                                    item.items.every((row: any) => !isEmptyValue(row.point) && !isEmptyValue(row.prompt)),
                                ) ||
                                !form.summary.every((item: any) => !isEmptyValue(item.point)) ||
                                !form.summary.every((item: any) => !isEmptyValue(item.prompt)) ||
                                (is_admin && !form.company_user_id)
                            "
                        >
                            Submit
                        </ButtonYellow>
                    </div>
                </div>
            </form>
        </section>
    </AppLayout>
</template>
<script setup lang="ts">
import ButtonAddWhite from '@/components/button/ButtonAddWhite.vue';
import ButtonIconDelete from '@/components/button/ButtonIconDelete.vue';
import ButtonOutlineGrey from '@/components/button/ButtonOutlineGrey.vue';
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import Input from '@/components/input/Index.vue';
import SelectSearch from '@/components/input/SelectSearch.vue';
import Textarea from '@/components/input/Textarea.vue';
import FormPromptItem from '@/components/module/prompt/FormPromptItem.vue';
import { isEmptyValue } from '@/composables/global-function';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps<{
    type: string;
    row?: any;
    companies: any;
    is_admin: boolean;
}>();
const form = useForm({
    company_user_id: null,
    uuid: props.row?.uuid,
    name: props.row?.name,
    scorings: [],
    non_scorings: [],
    summary: [],
});
type itemType = 'scorings' | 'non_scorings';

const addItem = (type: itemType) => {
    (form[type] as any).push({
        name: '',
        items: [],
    });
};

const addItemScoring = (type: itemType, index: number) => {
    (form[type] as any)[index].items.push({
        point: '',
        prompt: '',
        score: 1,
        score_max: 5,
    });
};

const deleteItem = (type: itemType, index: number) => {
    (form[type] as any).splice(index, 1);
};

const deleteItemScoring = (type: itemType, index: number, scoring: number) => {
    (form[type] as any)[index].items.splice(scoring, 1);
};

const addSummary = () => {
    (form.summary as any).push({
        point: '',
        prompt: '',
    });
};

const deleteSummary = (index: number) => {
    form.summary.splice(index, 1);
};
const submit = () => {
    if (!form.processing) {
        form.post(route('setup.recording-analysis.prompt.store', props.type));
    }
};

const putEditValue = () => {
    if (props.row) {
        form.scorings = props.row.scorings.map((row: any) => {
            row.items = row.items.map((item: any) => {
                item.score = item.score || '1';
                item.score_max = item.score_max || '5';
                item.unit = item.unit || '';
                return item;
            });
            return row;
        });
        form.non_scorings = props.row.non_scorings;
        if (props.row.summary || props.row.summaries ) {
            let summary = props.row.summaries || props.row.summary;
            const isV2 = Array.isArray(summary);
            if (!isV2) {
                summary = Object.values(summary).map((item: any) => ({
                    point: item.name,
                    prompt: item.prompt,
                }));
            }
            form.summary = summary;
        }
        form.company_user_id = props.row.user_id;
    }
};

onMounted(() => {
    putEditValue();
});
</script>
