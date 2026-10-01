import type { Techno } from "../types/generated";

export function TechnoStack({ technos }: { technos: Techno[] }) {
    return (
        <div className="techno-stack">
            <ul className="techno-list">
                {technos.map((techno) => (
                    <li key={techno.id}>
                        {techno.url ? (
                            <a
                                href={techno.url}
                                target="_blank"
                                rel="noreferrer"
                            >
                                {techno.name}
                            </a>
                        ) : (
                            techno.name
                        )}
                    </li>
                ))}
            </ul>
        </div>
    );
}
