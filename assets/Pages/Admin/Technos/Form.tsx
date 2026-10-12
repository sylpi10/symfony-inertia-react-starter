import { Link, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";
import type { TechnoFormPageProps } from "../../../types/generated";

export default function Form({ techno }: TechnoFormPageProps) {
    const form = useForm({
        name: techno?.name ?? "",
        url: techno?.url ?? "",
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        if (techno) {
            form.put(`/admin/technos/${techno.id}`);
        } else {
            form.post("/admin/technos");
        }
    }

    return (
        <div className="container">
            <form onSubmit={submit} className="auth-form">
                <h1>{techno ? `Edit "${techno.name}"` : "New techno"}</h1>

                <div className="input-wrapper">
                    <label htmlFor="name">Name</label>
                    <input
                        id="name"
                        value={form.data.name}
                        onChange={(e) => form.setData("name", e.target.value)}
                    />
                    {form.errors.name && (
                        <p className="form-error">{form.errors.name}</p>
                    )}
                </div>
                <div className="input-wrapper">
                    <label htmlFor="url">URL</label>
                    <input
                        id="url"
                        type="url"
                        value={form.data.url}
                        onChange={(e) => form.setData("url", e.target.value)}
                    />
                    {form.errors.url && (
                        <p className="form-error">{form.errors.url}</p>
                    )}
                </div>

                <button type="submit" disabled={form.processing}>
                    {techno ? "Save" : "Create"}
                </button>
                <p>
                    <Link href="/admin/technos">Back to list</Link>
                </p>
            </form>
        </div>
    );
}
