<template>
     <label
         :for="id"
         class="text-[12px] text-dark font-krub-medium mb-1 block  pre-text-content"
         v-if="label"
     >
         {{ label }}
         <span class="text-red" v-if="$attrs.required">*</span>
     </label>
     <div
         class="relative mb-2"
         x-data="{input: $el.getAttribute('data-value')}"
         :data-value="$attrs.value || ''"
         v-bind:class="{ 'has-error': error }"
     >
         <select
             @change="
                 $emit('setCode', ($event.target as HTMLInputElement).value)
             "
             class="absolute text-[12px] w-fit pe-5 px-1 ms-8x ms-2 border-0 mt-[1px] outline-none shadow-none disabled:bg-[#F3F4F6]"
             v-bind:disabled="disabled || false"
         >
             <option value="62" :selected="code === '62'">+62</option>
             <option
                 v-for="row in codes"
                 :key="row.dial_code"
                 :value="row.dial_code"
                 :selected="code === row.dial_code"
             >
                 +{{ row.dial_code }}
             </option>
         </select>
         <input
             type="number"
             x-model="input"
             v-bind="$attrs"
             @keydown="isNumber($event)"
             @input="
                 validateInput($event.target),
                     $emit(
                         'update:modelValue',
                         ($event.target as HTMLInputElement).value
                     )
             "
             v-bind:disabled="disabled || false"
             class="ps-[65px] border rounded-lg placeholder:text-[#615e5e] px-4 text-[12px] min-h-[42px] shadow-none outline-none py-2 w-full mb-2 disabled:bg-[#F3F4F6]"
         />
         <small
             v-if="error"
             class="mt-[-7px] error-text mb-4 block text-[11px]"
             >{{ error }}</small
         >
         <small
             class="block mt-[-7px] text-[10px] mb-4 text-[#A3A3A3]"
             v-if="help"
             >{{ help }}</small
         >
     </div>
 </template>
 
 <script lang="ts" setup>
 const props = defineProps<{
     label?: string;
     icon?: string;
     help?: string;
     error?: string;
     id?: string;
     code?: string;
     codes: Array<any>;
     disabled?: boolean;
     maxlength?: any;
 }>();
 
 const isNumber = (evt: any) => {
     const charCode = evt.which ? evt.which : evt.keyCode;
     if (
         charCode > 31 &&
         (charCode < 48 || charCode > 57) &&
         [46, 44, 43, 45, 101, 69, 188, 189, 187, 190].includes(charCode)
     ) {
         evt.preventDefault();
     }
     if (props.maxlength && ![8, 17, 65].includes(charCode)) {
         if (evt.target.value.length >= Number(props.maxlength)) {
             evt.preventDefault();
         }
     }
     return true;
 };
 
 const validateInput = (input: any) => {
     const value = input.value;
     if (props.maxlength) {
         input.value = value.slice(0, props.maxlength);
     }
 };
 </script>
 