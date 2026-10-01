import { AboutPageProps } from "../types/generated";

export default function About({ title }: AboutPageProps) {
    return (
        <div className="container">
            <h1>{title}</h1>
            <hr />
            <p>
                <p>
                    A Symfony 8 starter with a React 19 + TypeScript front end,
                    glued together by Inertia.js. You get a single-page app
                    without writing an API: Symfony handles routing, security
                    and validation, React handles the UI.
                </p>
                <ul>
                    <li>
                        Authentication: login, registration, password reset,
                        email verification
                    </li>
                    <li>Forms validated by Symfony, errors shown by React</li>
                    <li>TypeScript types generated from PHP DTOs</li>
                    <li>Server-side rendering with Node, Vite and Sass</li>
                </ul>
            </p>
            <p>
                Go to{" "}
                <a
                    href="https://github.com/sylpi10/symfony-inertia-react-starter"
                    target="_blank"
                    rel="noreferrer"
                >
                    Github
                </a>
            </p>
        </div>
    );
}
