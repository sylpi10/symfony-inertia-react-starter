import { Link, usePage } from "@inertiajs/react";
import { ReactNode } from "react";

const links = [
    { href: "/", label: "Home" },
    { href: "/about", label: "About" },
];

export default function Layout({ children }: { children: ReactNode }) {
    const { url } = usePage();
    return (
        <>
            <nav className="main-nav">
                {links.map((link) => (
                    <Link
                        key={link.href}
                        href={link.href}
                        className={url === link.href ? "active" : undefined}
                    >
                        {link.label}
                    </Link>
                ))}
            </nav>
            <main>{children}</main>
        </>
    );
}
