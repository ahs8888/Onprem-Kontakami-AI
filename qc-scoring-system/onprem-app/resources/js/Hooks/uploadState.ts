import { ref, inject, provide, Ref } from 'vue';

interface AlertState {
    title: string,
    type: '' | 'error' | 'success',
    description: string,
    folderName?: string
}

export interface uploadState {
    folderName: Ref<string>;
    progress: Ref<number>;
    uploading: Ref<boolean>;
    injectId: Ref<string>;
    alert: Ref<AlertState>
}

const key = Symbol('UploadState');

export function provideUploadState() {
    const state: uploadState = {
        folderName: ref(''),
        progress: ref(0),
        uploading: ref(false),
        injectId: ref(''),
        alert: ref({
            title: '',
            type: '',
            description: '',
            folderName: ''
        })
    };
    provide(key, state);
    return state;
}

export function useUploadState(): uploadState {
    const state = inject<uploadState>(key);
    if (!state) {
        throw new Error('uploadState is not provided');
    }
    return state;
}
