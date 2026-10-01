import type { ReactNode } from "react";

export default function GuestLayout({ children }: { children: ReactNode }) {
    return <main className="guest-layout">{children}</main>;
}
