import type { ReactNode } from "react";
import FlashMessages from "../Components/FlashMessages";

export default function GuestLayout({ children }: { children: ReactNode }) {
    return (
        <main className="guest-layout">
            <FlashMessages />
            {children}
        </main>
    );
}
