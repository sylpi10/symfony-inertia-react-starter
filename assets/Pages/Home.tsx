import { TechnoStack } from "../Components/TechnoStack";
import type { HomePageProps } from "../types/generated";
import symfonyLogo from "../images/sf.webp";
import reactLogo from "../images/react.webp";
import inertiaLogo from "../images/inertia-v3-featured.webp";

export default function Home({ title, para, technos }: HomePageProps) {
    return (
        <div className="container">
            <h1>{title}</h1>
            <p>{para}</p>
            <hr />
            <div className="logo-wrapper">
                <img src={symfonyLogo} alt="Symfony" />
                <img src={reactLogo} alt="React" />
                <img src={inertiaLogo} alt="Inertia.js" />
            </div>
            <TechnoStack technos={technos} />
        </div>
    );
}
