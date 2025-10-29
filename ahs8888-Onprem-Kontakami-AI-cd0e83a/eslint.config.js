import prettier from 'eslint-config-prettier';
import vue from 'eslint-plugin-vue';

import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';

export default defineConfigWithVueTs(
    vue.configs['flat/essential'],
    vueTsConfigs.recommended,
    {
        ignores: ['vendor', 'node_modules', 'public', 'bootstrap/ssr', 'tailwind.config.js', 'resources/js/components/ui/*'],
    },
    {
        rules: {
            'no-var': 'off',
            '@typescript-eslint/no-unused-vars': 'off',
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
            'vue/no-reserved-component-names': 'off',
            'vue/require-v-for-key': 'off',
            'vue/no-dupe-keys': 'off',
            'vue/no-mutating-props': 'off'

        },
    },
    prettier,
);
