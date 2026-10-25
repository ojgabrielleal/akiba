import { get, writable } from "svelte/store";

export function createSocialPostSubmission({
    form,
    resolveUrl,
    buildPayload,
    shouldReset = () => false,
}) {
    const pending = writable(false);

    function isProcessing() {
        return get(pending) || Boolean(get(form).processing);
    }

    function open(event, modal) {
        if (isProcessing()) return;

        const status = event.submitter?.value;
        if (!status) return;

        if (status === "draft") {
            send(status);
            return;
        }

        modal.open(status);
    }

    function send(status, modal = null) {
        pending.set(true);

        get(form)
            .transform((data) => buildPayload(data, status))
            .post(resolveUrl(), {
                preserveState: true,
                forceFormData: true,
                onSuccess: () => {
                    modal?.close();

                    if (shouldReset()) {
                        get(form).reset();
                    }
                },
                onFinish: () => pending.set(false),
            });
    }

    function confirm(event, modal) {
        if (isProcessing()) return;

        const { social, status } = event.detail;
        get(form).setStore("social", social);
        send(status, modal);
    }

    return { confirm, open, pending };
}
