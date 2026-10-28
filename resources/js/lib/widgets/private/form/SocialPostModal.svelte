<script>
    import { createEventDispatcher, tick } from "svelte";

    import {
        Button,
        FormField,
        Modal,
        TextArea,
        TextInput,
    } from "@/lib/components/private";
    import { errorFor } from "./postFormErrors.js";

    export let value = null;
    export let errors = {};
    export let processing = false;

    const dispatch = createEventDispatcher();
    const emptySocial = () => ({
        text: "",
        hashtags: [],
        channels: { instagram: true },
    });

    let modalRef;
    let status = "revision";
    let modalTitle = "Finalizar envio para avaliação";
    let actionLabel = "Enviar para avaliação";
    let social = emptySocial();
    let hashtagsText = "";
    let confirmationAttempted = false;

    $: serverTextError = errorFor(errors, ["social.text", "social[text]", "social"]);
    $: serverHashtagsError = errorFor(errors, [
        "social.hashtags",
        "social[hashtags]",
        ...Object.keys(errors).filter((key) => key.startsWith("social.hashtags.")),
    ]);
    $: characterCount = social.text?.length ?? 0;
    $: hashtagTokens = hashtagInputTokens(hashtagsText);
    $: hashtagCount = hashtagTokens.length;
    $: hashtagsAreValid = hashtagTokens.every((hashtag) => /^#[\p{L}\p{N}_]+$/u.test(hashtag));
    $: textError = serverTextError
        ?? (confirmationAttempted && status !== "draft" && !social.text?.trim()
            ? "Informe o texto da publicação."
            : null);
    $: hashtagsError = serverHashtagsError
        ?? (!hashtagsAreValid ? "Comece cada hashtag com # e separe por espaço ou vírgula." : null)
        ?? (confirmationAttempted && hashtagCount > 30 ? "Informe no máximo 30 hashtags." : null);
    $: actionVariant = status === "draft" ? "success" : (status === "published" ? "publish" : "accent");
    $: canConfirm = status === "draft"
        ? hashtagCount <= 30 && hashtagsAreValid
        : Boolean(social.text?.trim()) && hashtagCount <= 30 && hashtagsAreValid;

    function hashtagInputTokens(input = "") {
        return input.trim() === ""
            ? []
            : input.trim().split(/[\s,]+/u).filter(Boolean);
    }

    function parseHashtags(input = "") {
        return [...new Set(hashtagInputTokens(input).map((hashtag) => hashtag.toLocaleLowerCase()))];
    }

    export async function open(nextStatus) {
        status = nextStatus;
        modalTitle = status === "draft"
            ? "Finalizar rascunho"
            : status === "published"
                ? "Finalizar publicação"
                : "Finalizar envio para avaliação";
        actionLabel = status === "draft"
            ? "Salvar como rascunho"
            : status === "published"
                ? "Publicar"
                : "Enviar para avaliação";
        social = {
            ...emptySocial(),
            ...(value ?? {}),
            channels: {
                instagram: true,
                ...(value?.channels ?? {}),
            },
        };
        hashtagsText = (social.hashtags ?? []).map((hashtag) => `#${hashtag}`).join(" ");
        confirmationAttempted = false;
        modalRef.open();

        await tick();
        document.getElementById("social-post-text")?.focus();
    }

    export function close() {
        modalRef.close();
    }

    function confirm() {
        confirmationAttempted = true;
        if (!canConfirm) return;

        dispatch("confirm", {
            status,
            social: {
                text: social.text?.trim() ?? "",
                hashtags: parseHashtags(hashtagsText),
                channels: {
                    instagram: Boolean(social.channels.instagram),
                },
            },
        });
    }
</script>

<Modal bind:this={modalRef} title={modalTitle} size="md" minHeight="auto">
    <div slot="content" class="font-noto-sans">
        <FormField for="social-post-text" label="Texto da publicação" error={textError} spacing="compact">
            <span
                slot="labelAside"
                class="shrink-0 text-xs font-bold"
                class:text-red-crimson={characterCount > 1800}
                class:text-gray-500={characterCount <= 1800}
            >
                {characterCount}/1800
            </span>
            <TextArea
                id="social-post-text"
                rows="7"
                maxlength="1800"
                required
                variant="offcanvas"
                bind:value={social.text}
                error={textError}
                class="min-h-40"
                placeholder="Escreva a chamada que será publicada nas redes sociais..."
            />
        </FormField>

        <FormField
            for="social-post-hashtags"
            label="Hashtags"
            help="Separe por espaço ou vírgula. Toda hashtag deve começar com #."
            error={hashtagsError}
            spacing="compact"
        >
            <span
                slot="labelAside"
                class="shrink-0 text-xs font-bold"
                class:text-red-crimson={hashtagCount > 30}
                class:text-gray-500={hashtagCount <= 30}
            >
                {hashtagCount}/30 hashtags
            </span>
            <TextInput
                id="social-post-hashtags"
                type="text"
                maxlength="2600"
                variant="offcanvas"
                bind:value={hashtagsText}
                error={hashtagsError}
                describedBy="social-post-hashtags-help"
                placeholder="#anime #redeakiba"
            />
        </FormField>

        <div class="mt-6 flex justify-end border-t border-blue-marinho/10 pt-4">
            <Button
                type="button"
                variant={actionVariant}
                size="compact"
                loading={processing}
                disabled={processing}
                on:click={confirm}
            >
                {actionLabel}
            </Button>
        </div>
    </div>
</Modal>
