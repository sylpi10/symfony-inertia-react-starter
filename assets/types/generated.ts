export type AboutPageProps = {
    readonly title: string;
    readonly para: string;
    readonly technos: Techno[];
};
export type HomePageProps = {
    readonly title: string;
    readonly para: string;
    readonly technos: Techno[];
};
export type Techno = {
    readonly id: number;
    readonly name: string;
    readonly url: string | null;
};
export type User = {
    readonly id: number;
    readonly email: string;
    readonly roles: string[];
    readonly isVerified: boolean;
};
