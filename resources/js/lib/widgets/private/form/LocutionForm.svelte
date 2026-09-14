<script>
    import { useForm } from "@inertiajs/svelte";

    import { Button, EmptyState, Modal, Section } from "@/lib/components/private/";
    import { locutionPermissions, resolvePlaceholderImage } from "@/lib/utils";
    import { locutionIcons, locutionTextures, locutionDecorations } from "@/lib/constants";

    export let programs = null;

    const can = locutionPermissions();
    let notificationModalRef;

    $: form = useForm({
        send_notification: null,
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

    function submit() {
        notificationModalRef.open();
    }

    function startLocution(sendNotification) {
        $form.send_notification = sendNotification;

        $form.post(`/panel/locution/start/${$form.program}`, {
            preserveScroll: true,
            onSuccess: () => {
                notificationModalRef.close();
                $form.reset();
            },
        });
    }
</script>

<Modal bind:this={notificationModalRef}>
    <div slot="content" class="-m-4 overflow-hidden rounded-md border border-blue-skywave/20 bg-blue-ocean font-noto-sans shadow-[0_1.25rem_4rem_rgba(0,0,0,0.25)] sm:-m-6">
        <div class="relative">
            <div class="absolute inset-x-0 top-0 h-1 bg-orange-citric" aria-hidden="true"></div>
            <div class="absolute right-0 top-0 h-20 w-20 rounded-bl-full bg-blue-skywave/10" aria-hidden="true"></div>
            <div class="relative z-10 p-4 sm:p-5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex h-6 items-center rounded-full bg-blue-marinho/55 px-3 text-[0.62rem] font-extrabold uppercase italic text-orange-citric">
                        Entrada ao vivo
                    </span>
                    <span class="inline-flex h-6 items-center rounded-full border border-blue-skywave/25 px-3 text-[0.62rem] font-extrabold uppercase italic text-blue-skywave">
                        Notificação
                    </span>
                </div>

                <div class="mt-3 grid grid-cols-[auto_minmax(0,1fr)] items-center gap-3">
                    <span class="flex size-12 items-center justify-center rounded-md border-2 border-orange-citric bg-blue-marinho">
                        <img src="/svg/alerts.svg" alt="" class="w-7 filter-orange-citric" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-xl font-extrabold uppercase italic leading-tight text-suspense-aurora">
                            Avisar a Rede Akiba?
                        </h2>
                        <p class="mt-1 text-sm font-bold text-blue-skywave">
                            Programa prestes a entrar no ar
                        </p>
                    </div>
                </div>

                <div class="mt-4 rounded-md border border-blue-skywave/15 bg-blue-marinho/35 px-4 py-3">
                    <p class="text-sm leading-relaxed text-suspense-aurora/82">
                        Deseja mandar notificações avisando que você entrará
                        <span class="font-extrabold uppercase italic text-orange-citric">ao vivo</span>
                        nas plataformas da Rede Akiba?
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <button
                        type="button"
                        class="flex min-h-10 cursor-pointer items-center justify-center gap-2 rounded-full bg-orange-citric px-4 py-2 font-noto-sans text-[0.68rem] font-extrabold uppercase italic whitespace-nowrap text-blue-marinho transition duration-200 ease-out hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora active:translate-y-0 disabled:cursor-wait disabled:opacity-70 motion-reduce:transform-none motion-reduce:transition-none"
                        disabled={$form.processing}
                        on:click={() => startLocution(true)}
                    >
                        <img src="/svg/alerts.svg" alt="" class="w-5 filter-blue-marinho" />
                        Avisar
                    </button>
                    <button
                        type="button"
                        class="group/no-alert flex min-h-10 cursor-pointer items-center justify-center gap-2 rounded-full border-2 border-orange-citric bg-transparent px-4 py-2 font-noto-sans text-[0.68rem] font-extrabold uppercase italic whitespace-nowrap text-orange-citric transition duration-200 ease-out hover:-translate-y-0.5 hover:bg-orange-citric hover:text-blue-marinho focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora active:translate-y-0 disabled:cursor-wait disabled:opacity-70 motion-reduce:transform-none motion-reduce:transition-none"
                        disabled={$form.processing}
                        on:click={() => startLocution(false)}
                    >
                        <img src="/svg/bloqued.svg" alt="" class="w-5 filter-orange-citric group-hover/no-alert:filter-blue-marinho" />
                        Não avisar
                    </button>
                </div>
            </div>
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
        <div class="w-full mt-15 rounded-sm hidden lg:flex justify-center bg-contain bg-right bg-no-repeat" style={`background-image: url('${$form.phrase.texture}'), var(--gradient-blue-ocean-cerulean);`}>  
            <div class="w-8/12 h-25 flex items-center relative">
                <img
                    src={$form.phrase.decoration?.left}
                    alt=""
                    aria-hidden="true"
                    class="w-23 absolute left-0"
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
                    class="w-23 absolute -right-10"
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5 lg:mt-12">
            <div class="grid grid-cols-3 gap-3 min-[24rem]:grid-cols-4 sm:grid-cols-5 sm:gap-5 xl:grid-cols-10">
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
                            class={["object-contain w-full h-full",
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
                        class={["cursor-pointer h-15 border-2 border-blue-ocean rounded-md disabled:cursor-not-allowed disabled:opacity-50", 
                            { "border-blue-skywave drop-shadow-[0_0_20px_rgba(0,255,200,0.3)]": $form.phrase.texture === item.url },
                            { "border-blue-ocean": $form.phrase.texture !== item.url }
                        ]}
                        disabled={item.disabled}
                        on:click={() => { $form.phrase.texture = item.url}}
                    >
                        <img 
                            src={item.url}
                            alt={item.alt}
                            aria-hidden="true"
                            class={["object-cover w-full h-full",
                                { "hidden": item.disabled },
                                { "initial": !item.disabled }
                            ]}
                        />
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
