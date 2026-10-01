import { Link } from "@inertiajs/react";

type Props = { status: number };

const messages: Record<number, { title: string; description: string }> = {
    403: {
        title: "Forbidden",
        description: "You don't have access to this page.",
    },
    404: {
        title: "Page not found",
        description: "Sorry, the page you are looking for doesn't exist.",
    },
    500: {
        title: "Server error",
        description:
            "Something went wrong on our side. Please try again later.",
    },
    503: {
        title: "Service unavailable",
        description: "We're doing some maintenance. Please check back soon.",
    },
};

export default function ErrorPage({ status }: Props) {
    const { title, description } = messages[status] ?? messages[500];

    return (
        <div className="container">
            <div className="error-page">
                <h1>
                    {status} · {title}
                </h1>
                <p>{description}</p>
                <Link href="/">Back to home</Link>
            </div>
        </div>
    );
}
