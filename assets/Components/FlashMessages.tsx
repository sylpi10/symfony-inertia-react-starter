import { usePage } from "@inertiajs/react";

export default function FlashMessages() {
    const { flash } = usePage();

    return (
        <>
            {flash.success && (
                <p className="flash flash-success" role="status">
                    {flash.success}
                </p>
            )}
            {flash.error && (
                <p className="flash flash-error" role="alert">
                    {flash.error}
                </p>
            )}
        </>
    );
}
