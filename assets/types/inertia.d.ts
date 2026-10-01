import "@inertiajs/core";
import type { User } from "./generated";

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
