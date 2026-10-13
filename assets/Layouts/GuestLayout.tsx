import { usePage } from "@inertiajs/react";
import type { ReactNode } from "react";
import FlashMessages from "../Components/FlashMessages";
import Seo from "../Components/Seo";

export default function GuestLayout({ children }: { children: ReactNode }) {
    const { seo } = usePage().props;

    return (
        <main className="guest-layout">
            <Seo {...seo} />
            <FlashMessages />
            {children}
        </main>
    );
}
