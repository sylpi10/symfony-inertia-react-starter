import "@inertiajs/core";

export type User = {
    id: number;
    email: string;
    roles: string[];
    isVerified: boolean;
};

declare module "@inertiajs/core" {
    export interface InertiaConfig {
        sharedPageProps: {
            auth: { user: User | null };
        };

        flashDataType: {
            success?: string;
            error?: string;
        };
    }
}
