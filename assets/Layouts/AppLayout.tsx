import { Link, usePage } from "@inertiajs/react";
import { ReactNode } from "react";
import UserIcon from "../Components/Icons/UserIcon";
import FlashMessages from "../Components/FlashMessages";

const links = [
    { href: "/", label: "Home" },
    { href: "/about", label: "About" },
];

export default function AppLayout({ children }: { children: ReactNode }) {
    const { url, props } = usePage();
    const { user } = props.auth;
    return (
        <>
            <nav className="main-nav">
                <ul>
                    {links.map((link) => (
                        <li key={link.href}>
                            <Link
                                href={link.href}
                                className={
                                    url === link.href ? "active" : undefined
                                }
                            >
                                {link.label}
                            </Link>
                        </li>
                    ))}
                </ul>

                {/* Right side: depends on the authenticated user */}
                <div className="nav-user">
                    {user ? (
                        <>
                            <UserIcon />
                            <span className="nav-email">{user.email}</span>
                            <Link href="/logout" method="post" as="button">
                                Logout
                            </Link>
                        </>
                    ) : (
                        <>
                            <Link href="/login">Login</Link>
                            <Link href="/register">Register</Link>
                        </>
                    )}
                </div>
            </nav>
            <main>
                <FlashMessages />
                {children}
            </main>
        </>
    );
}
