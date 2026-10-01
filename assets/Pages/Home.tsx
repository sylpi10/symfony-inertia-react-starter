import { TechnoStack } from "../Components/TechnoStack";
import type { HomePageProps } from "../types/generated";

export default function Home({ title, para, technos }: HomePageProps) {
    return (
        <div className="container">
            <h1>{title}</h1>
            <p>{para}</p>
            <TechnoStack technos={technos} />
        </div>
    );
}
