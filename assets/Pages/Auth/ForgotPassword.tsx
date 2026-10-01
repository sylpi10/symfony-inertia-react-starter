import { Link, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";

export default function ForgotPassword() {
    const form = useForm({ email: "" });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post("/reset-password");
    }

    return (
        <div className="container">
            <form onSubmit={submit} className="auth-form">
                <h1>Forgot your password?</h1>
                <p>Enter your email and we'll send you a link to reset it.</p>

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
                <button type="submit" disabled={form.processing}>
                    Send reset link
                </button>

                <p>
                    <Link href="/login">Back to sign in</Link>
                </p>
            </form>
        </div>
    );
}
