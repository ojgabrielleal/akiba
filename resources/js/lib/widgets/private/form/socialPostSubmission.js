import { get, writable } from "svelte/store";

export function createSocialPostSubmission({
    form,
    resolveUrl,
    buildPayload,
    shouldReset = () => false,
    hasPersistedSocial = () => false,
}) {
    const pending = writable(false);

    function isProcessing() {
        return get(pending) || Boolean(get(form).processing);
    }

    function open(event, modal) {
        if (isProcessing()) return;

        const status = event.submitter?.value;
        if (!status) return;

        if (hasPersistedSocial()) {
            send(status, null, true);
            return;
        }

        if (status === "draft") {
            send(status);
            return;
        }

        modal.open(status);
    }

    function send(status, modal = null, preserveSocial = false) {
        pending.set(true);

        get(form)
            .transform((data) => {
                const payload = buildPayload(data, status);

                if (!preserveSocial) return payload;

                const { social, ...payloadWithoutSocial } = payload;

                return payloadWithoutSocial;
            })
            .post(resolveUrl(), {
                preserveState: true,
                forceFormData: true,
                onSuccess: () => {
                    modal?.close();

                    if (shouldReset()) {
                        get(form).reset();
                    }
                },
                onError: (errors) => {
                    const hasPostFormError = Object.keys(errors ?? {}).some((key) => (
                        key !== "social"
                        && !key.startsWith("social.")
                        && !key.startsWith("social[")
                    ));

                    if (hasPostFormError) {
                        modal?.close();
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
