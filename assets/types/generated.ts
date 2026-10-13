export type AboutPageProps = {
    readonly title: string;
    readonly seo: SeoDto;
};
export type HomePageProps = {
    readonly title: string;
    readonly para: string;
    readonly technos: Techno[];
    readonly seo: SeoDto;
};
export type MoveDirection = "up" | "down";
export type SeoDto = {
    readonly title: string;
    readonly description: string | null;
    readonly canonical: string | null;
    readonly image: string | null;
    readonly noindex: boolean;
};
export type Techno = {
    readonly id: number;
    readonly name: string;
    readonly url: string | null;
};
export type TechnoFormPageProps = {
    readonly techno: Techno | null;
    readonly seo: SeoDto;
};
export type TechnoIndexPageProps = {
    readonly technos: Techno[];
    readonly page: number;
    readonly totalPages: number;
    readonly seo: SeoDto;
};
export type User = {
    readonly id: number;
    readonly email: string;
    readonly roles: string[];
    readonly isVerified: boolean;
};
