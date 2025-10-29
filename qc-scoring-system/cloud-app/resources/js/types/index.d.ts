import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: Auth;
    ziggy: Config & { location: string };
    website: string
    flash: {
        error?: string;
        success?: string;
    },
    process: {
        uuid: string
        progress: string
        type: string
        label: string
        status: string
    }[]
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar: string;
    code: string
    phone_number: string
    phone_code: string
    company: string
    app: string
}

export type BreadcrumbItemType = BreadcrumbItem;
