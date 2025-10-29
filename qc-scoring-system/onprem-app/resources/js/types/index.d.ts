import type { Config } from 'ziggy-js';

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    auth: {
        user: User
    };
    flash: {
        error?: string;
        success?: string;
    }
    ziggy: Config & { location: string };
};

export interface User {
    id: number;
    name: string;
    username: string;
    avatar?: string;
}

