import "@inertiajs/core";

export type User = {
    id: number;
    email: string;
    roles: string[];
};

declare module "@inertiajs/core" {
    export interface InertiaConfig {
        sharedPageProps: {
            auth: { user: User | null };
        };
    }
}
