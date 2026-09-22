import { TechnoList } from "./TechnoList";

type Props = { message: string; para: string };
type TechnosProps = { technos: string[] };

export default function Home({ message, para, technos }: Props & TechnosProps) {
    return (
        <div className="start-container">
            <h1>{message}</h1>
            <p>{para}</p>
            <TechnoList technos={technos} />
        </div>
    );
}
