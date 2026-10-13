import { useState } from "react";
import { AboutPageProps } from "../types/generated";

const command =
    "composer create-project sylpi10/symfony-inertia-react-starter my-app";

export default function About({ title }: AboutPageProps) {
    const [copied, setCopied] = useState(false);

    async function copy() {
        await navigator.clipboard.writeText(command);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    }

    return (
        <div className="container">
            <h1>{title}</h1>
            <hr />
            <p>
                A Symfony 8 starter with a React 19 + TypeScript front end,
                glued together by Inertia.js. You get a single-page app without
                writing an API: Symfony handles routing, security and
                validation, React handles the UI.
            </p>
            <ul>
                <li>
                    Authentication: login, registration, password reset, email
                    verification
                </li>
                <li>Forms validated by Symfony, errors shown by React</li>
                <li>An example admin CRUD, reserved to administrators</li>
                <li>TypeScript types generated from PHP DTOs</li>
                <li>Server-side rendering with Node, Vite and Sass</li>
                <li>SEO: head tags per page, sitemap and robots.txt</li>
                <li>Functional tests, a Docker dev stack and a CI</li>
            </ul>
            <p>Start a new project with:</p>
            <div className="command">
                <pre>
                    <code>{command}</code>
                </pre>
                <button type="button" onClick={copy}>
                    {copied ? "Copied!" : "Copy"}
                </button>
            </div>
            <p>
                Find it on{" "}
                <a
                    href="https://github.com/sylpi10/symfony-inertia-react-starter"
                    target="_blank"
                    rel="noreferrer"
                >
                    GitHub
                </a>{" "}
                and{" "}
                <a
                    href="https://packagist.org/packages/sylpi10/symfony-inertia-react-starter"
                    target="_blank"
                    rel="noreferrer"
                >
                    Packagist
                </a>
                .
            </p>
        </div>
    );
}
