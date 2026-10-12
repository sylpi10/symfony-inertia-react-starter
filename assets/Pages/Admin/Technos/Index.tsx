import { Link, router } from "@inertiajs/react";
import type { Techno, TechnoIndexPageProps } from "../../../types/generated";

function swap(list: Techno[], from: number, to: number): Techno[] {
    const next = [...list];
    [next[from], next[to]] = [next[to], next[from]];
    return next;
}

export default function Index({
    technos,
    page,
    totalPages,
}: TechnoIndexPageProps) {
    function move(index: number, direction: "up" | "down") {
        const target = direction === "up" ? index - 1 : index + 1;

        router
            .optimistic<TechnoIndexPageProps>((props) =>
                // At the page edge the neighbor is on another page: let the server answer
                target >= 0 && target < props.technos.length
                    ? { technos: swap(props.technos, index, target) }
                    : undefined,
            )
            .patch(
                `/admin/technos/${technos[index].id}/move?page=${page}`,
                { direction },
                { preserveScroll: true },
            );
    }

    function destroy(techno: Techno) {
        if (!confirm(`Delete "${techno.name}"?`)) return;
        router.delete(`/admin/technos/${techno.id}?page=${page}`, {
            preserveScroll: true,
        });
    }

    const isFirst = (index: number) => page === 1 && index === 0;
    const isLast = (index: number) =>
        page === totalPages && index === technos.length - 1;

    return (
        <div className="container">
            <div className="admin-header">
                <h1>Technos</h1>
                <Link href="/admin/technos/new">New techno</Link>
            </div>

            <table className="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>URL</th>
                        <th>Order</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    {technos.map((techno, index) => (
                        <tr key={techno.id}>
                            <td>{techno.name}</td>
                            <td>{techno.url ?? "—"}</td>
                            <td>
                                <button
                                    type="button"
                                    disabled={isFirst(index)}
                                    onClick={() => move(index, "up")}
                                    aria-label="Move up"
                                >
                                    ↑
                                </button>
                                <button
                                    type="button"
                                    disabled={isLast(index)}
                                    onClick={() => move(index, "down")}
                                    aria-label="Move down"
                                >
                                    ↓
                                </button>
                            </td>
                            <td>
                                <Link href={`/admin/technos/${techno.id}/edit`}>
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    onClick={() => destroy(techno)}
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            {totalPages > 1 && (
                <nav className="pagination">
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map(
                        (p) => (
                            <Link
                                key={p}
                                href={`/admin/technos?page=${p}`}
                                className={p === page ? "active" : undefined}
                                preserveScroll
                            >
                                {p}
                            </Link>
                        ),
                    )}
                </nav>
            )}
        </div>
    );
}
