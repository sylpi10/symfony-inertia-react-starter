import { TechnoStack } from "../Components/TechnoStack";

type Props = { title: string; para: string };
type TechnosProps = { technos: string[] };

export default function Home({ title, para, technos }: Props & TechnosProps) {
    return (
        <div className="container">
            <h1>{title}</h1>
            <p>{para}</p>
            <TechnoStack technos={technos} />
        </div>
    );
}
