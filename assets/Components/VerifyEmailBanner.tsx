import { Link, usePage } from "@inertiajs/react";

export default function VerifyEmailBanner() {
    const { user } = usePage().props.auth;

    if (!user || user.isVerified) {
        return null;
    }

    return (
        <div className="verify-email-banner" role="status">
            Please confirm your email address.{" "}
            <Link href="/verify/resend" method="post" as="button">
                Resend the link
            </Link>
        </div>
    );
}
