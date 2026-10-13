import "@inertiajs/core";
import type { SeoDto, User } from "./generated";

declare module "@inertiajs/core" {
    export interface InertiaConfig {
        sharedPageProps: {
            auth: { user: User | null };
            // Every page has one: the controller's, or the default shared by InertiaShareListener
            seo: SeoDto;
        };
        flashDataType: {
            success?: string;
            error?: string;
        };
    }
}
