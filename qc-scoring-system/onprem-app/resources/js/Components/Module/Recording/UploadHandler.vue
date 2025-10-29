<template>
    <ButtonPrimary icon="isax icon-folder-add" @click="newRecord" :disabled="uploadState.uploading.value">
        New Recording
    </ButtonPrimary>

    <input type="file" webkitdirectory directory multiple hidden @change="handleFolderUpload" id="file-upload" />
</template>

<script setup lang="ts">
import { clickId, showAlert } from '@/Plugins/Function/global-function';
import ButtonPrimary from "@/Components/Button/ButtonPrimary.vue";
import { ref } from 'vue';
import { useUploadState } from '@/Hooks/uploadState';

const emit = defineEmits(["fetchData"])
const uploadState = useUploadState()

const newRecord = () => {
    uploadState.injectId.value = ''
    clickId('file-upload')
}

const handleFolderUpload = async (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (!input.files) return;

    const files = Array.from(input.files);

    if (files.length === 0) {
        return showAlert("No file exists");
    }

    if (files.length > 1000) {
        return showAlert("Max file 1000 items");
    }

    const maxSizeMB = 40;
    const maxSizeBytes = maxSizeMB * 1024 * 1024;

    // const oversizedFiles = files.filter(file => file.size > maxSizeBytes);
    // if (oversizedFiles.length > 0) {
    //     const names = oversizedFiles.map(f => f.name).join(', ');
    //     showAlert(`File(s) exceed ${maxSizeMB} MB: ${names}`);
    //     input.value = '';
    //     return;
    // }

    const allowedExt = ['mp3', 'wav', 'gsm', 'mp4'];
    const invalidFiles = files.filter(file => {
        const ext = file.name.split('.').pop()?.toLowerCase();
        return !ext || !allowedExt.includes(ext);
    });

    if (invalidFiles.length > 0) {
        const invalidNames = invalidFiles.map(f => f.name).join(', ');
        showAlert(`Invalid file format detected: ${invalidNames}`   );
        input.value = '';
        return;
    }


    const firstPath = files[0].webkitRelativePath;
    const folderName = firstPath.split('/')[0];
    uploadState.folderName.value = folderName;

    uploadState.uploading.value = true;
    uploadState.progress.value = 0;

    window.addEventListener("beforeunload", handleBeforeUnload);

    const formData = new FormData();
    formData.append('folderName', folderName);
    formData.append('injectId', uploadState.injectId.value || '');

    files.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
        formData.append(`relativePaths[${index}]`, file.webkitRelativePath);
    });

    uploadState.alert.value = {
        title: '',
        type: '',
        description: '',
        folderName: ''
    }

    try {
        await uploadBatchWithProgress(formData);
        uploadState.alert.value = {
            title: 'File Uploaded Successfully ',
            type: 'success',
            description: 'Your files have been succesfully uploaded.',
            folderName: uploadState.folderName.value
        }
        emit("fetchData");
    } catch (xhr: any) {
        let message = 'Upload failed. Please try again.';
        try {
            const response = JSON.parse(xhr.responseText);
            if (response.message) {
                message = response.message;
            }
        } catch (_) {
            // response not JSON, ignore
        }
        uploadState.alert.value = {
            title: 'File Upload Failed',
            type: 'error',
            description: 'There was an issue uploading your file. This could be due to the file size exceeding the limit, an incorrect file format, or a network error. Please check your file and try again.'
        }
    } finally {
        uploadState.uploading.value = false;
        input.value = '';

        window.removeEventListener("beforeunload", handleBeforeUnload);
    }
};


const uploadBatchWithProgress = (formData: FormData) => {
    return new Promise<void>((resolve, reject) => {
        const xhr = new XMLHttpRequest();

        xhr.upload.onprogress = (event) => {
            if (event.lengthComputable) {
                const rawProgress = (event.loaded / event.total) * 100;
                const adjustedProgress = Math.min(Math.round(rawProgress * 0.8), 80);
                uploadState.progress.value = adjustedProgress;

            }
        };

        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                simulateServerProcessingProgress().then(() => {
                    uploadState.progress.value = 100;
                    resolve();
                });
            } else {
                reject(xhr);
            }
        };

        xhr.onerror = () => reject(xhr);
        xhr.onabort = () => reject(xhr);

        xhr.open('POST', route('api.recordings.store-folder'));
        xhr.send(formData);
    });
};

const simulateServerProcessingProgress = () => {
    return new Promise<void>((done) => {
        const interval = setInterval(() => {
            if (uploadState.progress.value < 100) {
                uploadState.progress.value += 5;
            }
            if (uploadState.progress.value >= 100) {
                clearInterval(interval);
                done();
            }
        }, 200); // setiap 200ms
    });
};

const handleBeforeUnload = (e: BeforeUnloadEvent) => {
    e.preventDefault();
    e.returnValue = '';
    // Chrome & modern browser akan tampilkan alert default:
    // "Changes you made may not be saved."
}


</script>
