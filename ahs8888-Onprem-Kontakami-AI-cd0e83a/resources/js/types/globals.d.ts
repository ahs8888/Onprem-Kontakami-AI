import { AppPageProps } from '@/types/index';
import { AxiosInstance } from 'axios';
import {route as ziggyRoute, Config as ZiggyConfig } from 'ziggy-js';
import { Alpine } from 'alpinejs';

declare global {
    interface Window {
        axios: AxiosInstance;
        route: ziggyRoute;
        Alpine: Alpine
    }

    var route: typeof ziggyRoute;
    var $page: any
    var Ziggy: ZiggyConfig;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
        $page: any
    }
}

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps { }
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}
