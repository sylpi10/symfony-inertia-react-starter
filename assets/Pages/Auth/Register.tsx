import { Link, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";

export default function Register() {
    const form = useForm({
        email: "",
        password: "",
        passwordConfirmation: "",
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post("/register", {
            onFinish: () => form.reset("password", "passwordConfirmation"),
        });
    }

    return (
        <div className="container">
            <form onSubmit={submit} className="auth-form">
                <h1>Create an account</h1>

                <div className="input-wrapper">
                    <label htmlFor="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        autoComplete="email"
                        value={form.data.email}
                        onChange={(e) => form.setData("email", e.target.value)}
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
                        autoComplete="new-password"
                        value={form.data.password}
                        onChange={(e) =>
                            form.setData("password", e.target.value)
                        }
                    />
                    {form.errors.password && (
                        <p className="form-error">{form.errors.password}</p>
                    )}
                </div>
                <div className="input-wrapper">
                    <label htmlFor="passwordConfirmation">
                        Confirm password
                    </label>
                    <input
                        id="passwordConfirmation"
                        type="password"
                        autoComplete="new-password"
                        value={form.data.passwordConfirmation}
                        onChange={(e) =>
                            form.setData("passwordConfirmation", e.target.value)
                        }
                    />
                    {form.errors.passwordConfirmation && (
                        <p className="form-error">
                            {form.errors.passwordConfirmation}
                        </p>
                    )}
                </div>
                <button type="submit" disabled={form.processing}>
                    Create account
                </button>

                <p>
                    Already have an account? <Link href="/login">Sign in</Link>
                </p>
            </form>
        </div>
    );
}
