export type AboutPageProps = {
    readonly title: string;
};
export type HomePageProps = {
    readonly title: string;
    readonly para: string;
    readonly technos: Techno[];
};
export type MoveDirection = "up" | "down";
export type Techno = {
    readonly id: number;
    readonly name: string;
    readonly url: string | null;
};
export type TechnoFormPageProps = {
    readonly techno: Techno | null;
};
export type TechnoIndexPageProps = {
    readonly technos: Techno[];
    readonly page: number;
    readonly totalPages: number;
};
export type User = {
    readonly id: number;
    readonly email: string;
    readonly roles: string[];
    readonly isVerified: boolean;
};
