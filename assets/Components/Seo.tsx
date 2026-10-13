import { Head } from "@inertiajs/react";
import type { SeoDto } from "../types/generated";

// Keep in sync with templates/_seo.html.twig, the server-side fallback when SSR is off.
export default function Seo({
    title,
    description,
    canonical,
    image,
    noindex,
}: SeoDto) {
    return (
        <Head>
            <title>{title}</title>
            {description && <meta name="description" content={description} />}
            {canonical && <link rel="canonical" href={canonical} />}
            {noindex && <meta name="robots" content="noindex" />}
            <meta property="og:type" content="website" />
            <meta property="og:title" content={title} />
            {description && (
                <meta property="og:description" content={description} />
            )}
            {canonical && <meta property="og:url" content={canonical} />}
            {image && <meta property="og:image" content={image} />}
            <meta
                name="twitter:card"
                content={image ? "summary_large_image" : "summary"}
            />
        </Head>
    );
}
