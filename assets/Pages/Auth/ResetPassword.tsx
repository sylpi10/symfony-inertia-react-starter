import { useForm } from "@inertiajs/react";
import type { FormEvent } from "react";

export default function ResetPassword() {
    const form = useForm({ password: "", passwordConfirmation: "" });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post("/reset-password/reset", {
            onFinish: () => form.reset(),
        });
    }

    return (
        <div className="container">
            <form onSubmit={submit} className="auth-form">
                <h1>Choose a new password</h1>

                <div className="input-wrapper">
                    <label htmlFor="password">New password</label>
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
                    Reset password
                </button>
            </form>
        </div>
    );
}
