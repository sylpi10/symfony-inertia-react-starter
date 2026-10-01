import { Link } from "@inertiajs/react";

type Props = { expiresInMinutes: number };

export default function CheckEmail({ expiresInMinutes }: Props) {
    return (
        <div className="container">
            <div className="auth-form">
                <h1>Check your email</h1>
                <p>
                    If an account matches your email, a reset link has been
                    sent. It will expire in {expiresInMinutes} minutes.
                </p>
                <p>
                    If you don't receive it, check your spam folder or try
                    again.
                </p>
                <p>
                    <Link href="/reset-password">Request a new link</Link>
                </p>
            </div>
        </div>
    );
}
