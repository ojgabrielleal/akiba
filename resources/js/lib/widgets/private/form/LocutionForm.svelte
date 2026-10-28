<script>
    import { useForm } from "@inertiajs/svelte";
    import { tick } from "svelte";

    import { Button, EmptyState, FormField, Modal, Section, TextArea, TextInput } from "@/lib/components/private/";
    import { locutionPermissions, resolvePlaceholderImage } from "@/lib/utils";
    import { locutionIcons, locutionTextures, locutionDecorations } from "@/lib/constants";

    export let programs = null;

    const can = locutionPermissions();
    let notificationModalRef;
    let socialHashtags = "";

    $: form = useForm({
        send_notification: null,
        social: {
            text: "",
            hashtags: [],
        },
        program: null,
        phrase: {
            text: null,
            icon: locutionIcons[10].url,
            decoration: {
                left: locutionDecorations[0].left,
                right: locutionDecorations[0].right,
            },
            texture: locutionTextures[0].url,
        },
    });

    $: socialHashtagTokens = socialHashtags.trim().split(/[\s,]+/u).filter(Boolean);
    $: socialTextCharacterCount = $form.social.text?.length ?? 0;
    $: socialHashtagCount = socialHashtagTokens.length;
    $: socialHashtagsAreValid = socialHashtagTokens.length >= 1
        && socialHashtagTokens.length <= 30
        && socialHashtagTokens.every((hashtag) => /^#[\p{L}\p{N}_]+$/u.test(hashtag));
    $: canStartLocution = Boolean($form.social.text?.trim()) && socialHashtagsAreValid;
    $: socialHashtagsError = $form.errors["social.hashtags"]
        ?? (socialHashtags && !socialHashtagsAreValid
            ? "Informe de 1 a 30 hashtags válidas, todas começando com #."
            : null);

    async function submit() {
        notificationModalRef.open();
        await tick();
        document.getElementById("live-social-text")?.focus();
    }

    function startLocution() {
        if (!canStartLocution) return;

        $form.send_notification = true;
        $form.social.hashtags = [...new Set(
            socialHashtagTokens
                .map((hashtag) => hashtag.replace(/^#+/, "").toLocaleLowerCase()),
        )];

        $form.post(`/panel/locution/start/${$form.program}`, {
            preserveScroll: true,
            onSuccess: () => {
                notificationModalRef.close();
                socialHashtags = "";
                $form.reset();
            },
        });
    }
</script>

<Modal bind:this={notificationModalRef} title="Finalizar entrada ao vivo" size="md" minHeight="auto">
    <div slot="content" class="font-noto-sans">
        <FormField for="live-social-text" label="Texto da publicação" error={$form.errors["social.text"]} spacing="compact">
            <span
                slot="labelAside"
                class="shrink-0 text-xs font-bold"
                class:text-red-crimson={socialTextCharacterCount > 1800}
                class:text-gray-500={socialTextCharacterCount <= 1800}
            >
                {socialTextCharacterCount}/1800
            </span>
            <TextArea
                id="live-social-text"
                variant="offcanvas"
                rows="4"
                maxlength="1800"
                required
                bind:value={$form.social.text}
                error={$form.errors["social.text"]}
                placeholder="Conte que o programa entrou no ar..."
            />
        </FormField>
        <FormField for="live-social-hashtags" label="Hashtags" help="Obrigatório: use # e separe por espaço ou vírgula." error={socialHashtagsError} spacing="none">
            <span
                slot="labelAside"
                class="shrink-0 text-xs font-bold"
                class:text-red-crimson={socialHashtagCount > 30}
                class:text-gray-500={socialHashtagCount <= 30}
            >
                {socialHashtagCount}/30 hashtags
            </span>
            <TextInput
                id="live-social-hashtags"
                variant="offcanvas"
                maxlength="2600"
                bind:value={socialHashtags}
                error={socialHashtagsError}
                describedBy="live-social-hashtags-help"
                placeholder="#radio #anime #redeakiba"
                required
            />
        </FormField>
        <div class="mt-6 flex justify-end border-t border-blue-marinho/10 pt-4">
            <Button
                type="button"
                variant="accent"
                size="compact"
                loading={$form.processing}
                disabled={$form.processing || !canStartLocution}
                on:click={startLocution}
            >
                Iniciar programa
                <img
                    src="/svg/rewindInverted.svg"
                    alt=""
                    aria-hidden="true"
                    class="w-6 filter-blue-marinho"
                />
            </Button>
        </div>
    </div>
</Modal>

<form on:submit|preventDefault={submit}>
    <Section title="Meus Programas">
        {#if programs.data.length > 0}
            <div class="flex flex-wrap justify-center gap-8 sm:gap-15 lg:gap-x-0 lg:gap-y-15">
                {#each programs.data as item}
                    <button
                        type="button"
                        aria-label={item.name}
                        class="w-60 max-w-full flex-none cursor-pointer lg:box-content lg:border-r-2 lg:border-suspense-aurora/10 lg:px-10 lg:last:border-0"
                        on:click={() => { $form.program = item.uuid; }}
                    >
                        <img
                            src={resolvePlaceholderImage(item.image, "program")}
                            alt=""
                            aria-hidden="true"
                            loading="lazy"
                            class={["block w-full transition duration-300 ease-in-out",
                                { "opacity-100 scale-100": $form.program === item.uuid }, 
                                { "opacity-50 scale-90": $form.program !== item.uuid }
                            ]}
                        />
                    </button>
                {/each}
            </div>
        {:else}
            <EmptyState
                title="Nenhum programa disponível"
                description="Os programas disponíveis para locução aparecerão aqui."
            />
        {/if}
    </Section>
    <Section title="Personalização do player">
        <div class="locution-phrase-preview relative isolate w-full mt-15 rounded-sm hidden lg:flex justify-center" style="background-image: var(--gradient-blue-ocean-cerulean);">
            <img src={$form.phrase.texture} alt="" aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 z-0 h-full w-full object-contain object-right filter-blue-skywave" />
            <div class="relative z-10 w-8/12 h-25 flex items-center">
                <img
                    src={$form.phrase.decoration?.left}
                    alt=""
                    aria-hidden="true"
                    class="w-23 absolute left-0 filter-player-decoration"
                    loading="lazy"
                />
                <input 
                    type="text"
                    class="w-9/13 h-15 ml-23 bg-blue-ocean/50 border border-blue-skywave font-noto-sans font-extrabold italic uppercase text-xl text-orange-morning rounded-md outline-none pl-4"
                    placeholder="Digite a frase de locução"
                    bind:value={$form.phrase.text}
                >
                <img
                    src={$form.phrase.icon}
                    alt="Icone da locução"
                    class="w-35 h-35 absolute bottom-0 right-0 "
                />
                <img
                    src={$form.phrase.decoration?.right}
                    alt=""
                    aria-hidden="true"
                    class="w-23 absolute -right-10 filter-player-decoration"
                    loading="lazy"
                />
            </div>
        </div>
        <input 
            type="text"
            class="w-full block lg:hidden h-15 bg-blue-ocean/50 border border-blue-skywave font-noto-sans font-extrabold italic uppercase text-xl text-orange-morning rounded-md outline-none pl-4"
            placeholder="Digite a frase de locução"
            bind:value={$form.phrase.text}
        >
        <div class="grid grid-cols-1 gap-y-5 mt-5 lg:mt-12 lg:grid-cols-2 lg:gap-x-16 xl:gap-x-24">
            <div class="grid content-start grid-cols-[repeat(auto-fit,3.75rem)] gap-2 lg:grid-cols-[repeat(9,3.75rem)]">
                {#each locutionDecorations as item}
                    <button
                        type="button"
                        class={["cursor-pointer w-15 h-15 border-2 border-blue-ocean rounded-md disabled:cursor-not-allowed disabled:opacity-50", 
                            { "border-blue-skywave drop-shadow-[0_0_20px_rgba(0,255,200,0.3)]": $form.phrase.decoration?.left === item.left && $form.phrase.decoration?.right === item.right },
                            { "border-blue-ocean": !($form.phrase.decoration?.left === item.left && $form.phrase.decoration?.right === item.right) }
                        ]}
                        disabled={item.disabled}
                        on:click={() => { $form.phrase.decoration = { left: item.left, right: item.right } }}
                    >
                        <img 
                            src={item.icon}
                            alt={item.alt}
                            aria-hidden="true"
                            class={["object-contain w-full h-full filter-player-decoration",
                                { "hidden": item.disabled },
                                { "initial": !item.disabled }
                            ]}
                        />
                    </button>
                {/each}
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                {#each locutionTextures as item}
                    <button
                        type="button"
                        aria-label={`Selecionar textura ${item.alt}`}
                        class={["cursor-pointer h-15 border-2 border-blue-ocean rounded-md disabled:cursor-not-allowed disabled:opacity-50", 
                            { "border-blue-skywave drop-shadow-[0_0_20px_rgba(0,255,200,0.3)]": $form.phrase.texture === item.url },
                            { "border-blue-ocean": $form.phrase.texture !== item.url }
                        ]}
                        disabled={item.disabled}
                        on:click={() => { $form.phrase.texture = item.url}}
                    >
                        <span
                            aria-hidden="true"
                            class={["block h-full w-full bg-blue-skywave [mask-repeat:no-repeat] [mask-position:center] [mask-size:cover] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:cover]",
                                { "hidden": item.disabled },
                                { "initial": !item.disabled }
                            ]}
                            style={`mask-image: url("${item.url}"); -webkit-mask-image: url("${item.url}");`}
                        ></span>
                    </button>
                {/each}
            </div>
        </div>
        <div class="mt-16 grid grid-cols-2 gap-x-3 gap-y-16 min-[24rem]:grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:mt-23 lg:grid-cols-9 lg:gap-y-18">
            {#each locutionIcons as item, index}
                <button
                    type="button"
                    aria-label={`Icon ${index}`}
                    class="w-full h-7 flex cursor-pointer items-end justify-end rounded-sm bg-blue-ocean"
                    on:click={() => { $form.phrase.icon = item.url; }}
                >
                    <img
                        src={item.url}
                        alt=""
                        aria-hidden="true"
                        class="w-20 aspect-square"
                        loading="lazy"
                    />
                </button>
            {/each}
        </div>
        {#if can.start}
            <div class="flex justify-center mt-10">
                <Button
                    type="submit"
                    variant="accent"
                    shape="pill"
                    loading={$form.processing}
                >
                    Iniciar programa
                    <img 
                        src="/svg/rewindInverted.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-blue-marinho"
                    />
                </Button>
            </div>
        {/if}
    </Section>
</form>
