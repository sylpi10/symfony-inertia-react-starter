import { TechnoStack } from "../components/TechnoStack";

type Props = { title: string; para: string };
type TechnosProps = { technos: string[] };
export default function About({ title, para, technos }: Props & TechnosProps) {
    return (
        <div className="container">
            <h1>{title}</h1>
            <p>{para}</p>
            <TechnoStack technos={technos} />
        </div>
    );
}
