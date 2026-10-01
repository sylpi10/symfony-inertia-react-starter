export type AboutPageProps = {
    readonly title: string;
    readonly para: string;
    readonly technos: string[];
};
export type HomePageProps = {
    readonly title: string;
    readonly para: string;
    readonly technos: string[];
};
export type User = {
    readonly id: number;
    readonly email: string;
    readonly roles: string[];
    readonly isVerified: boolean;
};
