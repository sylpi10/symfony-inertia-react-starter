import { useForm } from "@inertiajs/react";
import type { FormEvent } from "react";

type Props = { lastEmail: string };

export default function Login({ lastEmail }: Props) {
    const form = useForm({
        email: lastEmail,
        password: "",
        remember: false,
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post("/login", {
            forceFormData: true,
            onFinish: () => form.reset("password"),
        });
    }

    return (
        <div className="container">
            <form onSubmit={submit} className="auth-form">
                <h1>Sign in</h1>
                <div className="input-wrapper">
                    <label htmlFor="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        autoComplete="username"
                        value={form.data.email}
                        onChange={(e) => form.setData("email", e.target.value)}
                        required
                    />
                    {form.errors.email && (
                        <p className="form-error">{form.errors.email}</p>
                    )}
                </div>
                <div className="input-wrapper">
                    <label htmlFor="password">Password</label>
                    <input
                        id="password"
                        type="password"
                        autoComplete="current-password"
                        value={form.data.password}
                        onChange={(e) =>
                            form.setData("password", e.target.value)
                        }
                        required
                    />
                </div>
                <label className="checkbox">
                    <input
                        type="checkbox"
                        checked={form.data.remember}
                        onChange={(e) =>
                            form.setData("remember", e.target.checked)
                        }
                    />
                    Remember me
                </label>

                <button type="submit" disabled={form.processing}>
                    Sign in
                </button>
            </form>
        </div>
    );
}
