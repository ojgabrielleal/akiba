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
        artwork_cover: null,
        channels: { instagram: true, facebook: true },
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
    $: serverArtworkCoverError = errorFor(errors, [
        "social.artwork_cover",
        "social[artwork_cover]",
    ]);
    $: characterCount = social.text?.length ?? 0;
    $: hashtagTokens = hashtagInputTokens(hashtagsText);
    $: hashtagCount = hashtagTokens.length;
    $: hashtagsAreValid = hashtagTokens.every((hashtag) => /^#[\p{L}\p{N}_]+$/u.test(hashtag));
    $: textError = serverTextError
        ?? (confirmationAttempted && status !== "draft" && !social.text?.trim()
            ? "Informe o texto."
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
                facebook: true,
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
                artwork_cover: social.artwork_cover ?? null,
                channels: {
                    instagram: Boolean(social.channels.instagram),
                    facebook: Boolean(social.channels.facebook),
                },
            },
        });
    }
</script>

<Modal bind:this={modalRef} title={modalTitle} size="md" minHeight="auto">
    <div slot="content" class="font-noto-sans">
        <FormField for="social-post-text" label="Texto" error={textError} spacing="compact">
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

        <FormField
            for="social-artwork-cover"
            label="Substituir imagem do banner"
            error={serverArtworkCoverError}
            spacing="compact"
        >
            <input
                id="social-artwork-cover"
                name="social-artwork-cover"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="w-full min-w-0 cursor-pointer rounded-lg border border-blue-marinho/20 bg-white px-3 py-2 font-noto-sans text-sm text-gray-700 outline-none file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-orange-citric file:px-4 file:py-2 file:font-bold file:text-blue-night hover:file:brightness-105 focus:border-blue-marinho/40 focus:ring-2 focus:ring-blue-marinho/10"
                class:border-red-crimson={serverArtworkCoverError}
                aria-invalid={serverArtworkCoverError ? "true" : undefined}
                aria-describedby={serverArtworkCoverError ? "social-artwork-cover-error" : "social-artwork-cover-help"}
                on:change={(event) => (social.artwork_cover = event.currentTarget.files?.[0] ?? null)}
            />
            {#if !serverArtworkCoverError}
                <div id="social-artwork-cover-help" class="mt-1 font-noto-sans text-sm text-gray-400">
                    <span class="block">Opcional. Caso não queira usar a imagem de capa.</span>
                    <span class="block">Tamanho mínimo: 1200×400.</span>
                </div>
            {/if}
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
