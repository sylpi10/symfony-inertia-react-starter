export function TechnoList({ technos }: { technos: string[] }) {
    return (
        <div>
            <ul>
                <h2>Technos:</h2>
                {technos.map((tech) => (
                    <li key={tech}>{tech}</li>
                ))}
            </ul>
        </div>
    );
}
