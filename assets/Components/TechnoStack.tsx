import type { Techno } from "../types/generated";

export function TechnoStack({ technos }: { technos: Techno[] }) {
    return (
        <div>
            <h2>Technos:</h2>
            <ul>
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
