<template>
    <AppLayout :title="row ? 'Edit Prompt' : 'Add Prompt'" :back="route('setup.agent-analysis.prompt.index')">
        <section class="mx-auto mb-[100px] rounded-md border bg-white px-5 py-4 md:max-w-[70%]">
            <form @submit.prevent="submit">
                <Input label="Prompt Name" placeholder="Enter Prompt Name" name="name" id="name" v-model="form.name" :value="form.name" maxlength="100" />
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
                <div class="fixed bottom-0 left-0 w-full border-t bg-white py-5 shadow-2xl">
                    <div class="mx-auto flex justify-end gap-2 md:max-w-[75%]">
                        <ButtonOutlineGrey :href="route('setup.agent-analysis.prompt.index')" class="px-8"> Back </ButtonOutlineGrey>
                        <ButtonYellow
                            type="submit"
                            class="px-7"
                            :loading="form.processing"
                            :disabled="
                                form.processing ||
                                !form.name ||
                                !form.scorings.length ||
                                !form.non_scorings.length ||
                                !form.scorings.every((item: any) => item.items.length) ||
                                !form.non_scorings.every((item: any) => item.items.length) ||
                                !form.scorings.every((item: any) => item.items.every((row: any) => row.point != '' && row.prompt != '')) ||
                                !form.non_scorings.every((item: any) => item.items.every((row: any) => row.point != '' && row.prompt != ''))
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
import ButtonOutlineGrey from '@/components/button/ButtonOutlineGrey.vue';
import ButtonYellow from '@/components/button/ButtonYellow.vue';
import Input from '@/components/input/Index.vue';
import FormPromptItem from '@/components/module/prompt/FormPromptItem.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps<{
    type: string;
    row?: any;
}>();
const form = useForm({
    uuid : props.row?.uuid,
    name: props.row?.name,
    scorings: [],
    non_scorings: [],
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
    });
};

const deleteItem = (type: itemType, index: number) => {
    (form[type] as any).splice(index, 1);
};
const deleteItemScoring = (type: itemType, index: number, scoring: number) => {
    (form[type] as any)[index].items.splice(scoring, 1);
};

const submit = () => {
    if (!form.processing) {
        form.post(route('setup.agent-analysis.prompt.store', props.type));
    }
};

const putEditValue = () => {
    if(props.row){
        form.scorings = props.row.scorings
        form.non_scorings = props.row.non_scorings
    }
}

onMounted(()=>{
    putEditValue()
})
</script>
