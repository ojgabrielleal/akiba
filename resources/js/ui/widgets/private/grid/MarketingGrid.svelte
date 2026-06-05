<script>
    import { page, router } from "@inertiajs/svelte";
    import { Section, Offcanvas } from "@/ui/components/private";
    import { MarketingForm } from "@/ui/widgets/private";
    import { repositoryPermissions } from "@/utils";

    $: ({ repositories } = $page.props);

    let can = repositoryPermissions();

    let offCanvasRef;
    let identifier;

    let tutorials;
    let packages;
    let softwares;

    $: if (repositories) {
        tutorials = repositories.data.filter(
            (item) => item.type === "tutorial",
        );
        packages = repositories.data.filter((item) => item.type === "package");
        softwares = repositories.data.filter(
            (item) => item.type === "software",
        );
    }

    const requestDeactivateRepository = (repository) => {
        router.delete(
            `/panel/marketing/repository/${repository}`,
            {},
            { preserveScroll: true },
        );
    };
</script>

<Offcanvas bind:this={offCanvasRef} title={identifier ? "Atualizar arquivo" : "Cadastrar arquivo"}>
    <div slot="content" let:close>
        <MarketingForm {identifier} {close} />
    </div>
</Offcanvas>

{#if repositories}
    <Section title="Tutoriais">
        <div class="mb-10 grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4">
            {#each tutorials as item}
                <article class="w-full bg-blue-skywave relative">
                    <a
                        title=""
                        aria-label=""
                        href={item.url}
                        target="_blank"
                    >
                        <img
                            src={item.image}
                            alt={item.name}
                            class="w-full h-48 object-cover aspect-square"
                            loading="lazy"
                        />
                        <div class="p-2 text-suspense-aurora text-center font-noto-sans font-light">
                            {item.name}
                        </div>
                    </a>
                </article>
            {/each}
        </div>
    </Section>

    <Section title="Instaladores">
        <div class="mb-10 grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4">
            {#each softwares as item}
                <article class="w-full bg-blue-skywave relative">
                    <a
                        title=""
                        aria-label=""
                        href={item.url}
                        target="_blank"
                    >
                        <img
                            src={item.image}
                            alt={item.name}
                            class="w-full h-48 object-cover aspect-square"
                            loading="lazy"
                        />
                        <div class="p-2 text-suspense-aurora text-center font-noto-sans font-light">
                            {item.name}
                        </div>
                    </a>
                </article>
            {/each}
        </div>
    </Section>

    <Section title="Pacotes e Modelos">
        <div class="mb-10 grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4">
            {#each packages as item}
                <article class="w-full bg-blue-skywave relative">
                    <a
                        title=""
                        aria-label=""
                        href={item.url}
                        target="_blank"
                    >
                        <img
                            src={item.image}
                            alt={item.name}
                            class="w-full h-48 object-cover aspect-square"
                            loading="lazy"
                        />
                        <div class="p-2 text-suspense-aurora text-center font-noto-sans font-light">
                            {item.name}
                        </div>
                    </a>
                </article>
            {/each}
        </div>
    </Section>
{/if}

<Section title="Todos os conteúdos">
    {#if can.create}
        <div class="flex justify-center mt-5 mb-10">
            <button type="button" class="cursor-pointer w-full lg:w-auto py-2 px-6 border-4 border-solid border-orange-amber rounded-xl text-orange-amber text-xl font-extrabold font-noto-sans italic uppercase" onclick={() => { offCanvasRef.open(); identifier = null; }}>
                Upar conteúdo
            </button>
        </div>
    {/if}
    {#if repositories}
        <div class="mb-20 grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-x-4 gap-y-20">
            {#each repositories.data as item}
                <article class="w-full bg-blue-skywave relative">
                    <a
                        title=""
                        aria-label=""
                        href={item.url}
                        target="_blank"
                    >
                        <img
                            src={item.image}
                            alt={item.name}
                            class="w-full h-48 object-cover aspect-square"
                            loading="lazy"
                        />
                        <div class="p-2 text-suspense-aurora text-center font-noto-sans font-light">
                            {item.name}
                        </div>
                    </a>
                    <div class="absolute -bottom-9 right-0 flex flex-row gap-4">
                        {#if can.show_button_create}
                            <button type="button"
                                class="cursor-pointer"
                                aria-label="editar"
                                onclick={() => { offCanvasRef.open(); identifier = item.uuid; }}
                            >
                                <img
                                    src="/svg/edit.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-5 filter-blue-skywave"
                                    loading="lazy"
                                />
                            </button>
                        {/if}
                        {#if can.deactivate}
                            <button type="button"
                                aria-label="remover"
                                class="cursor-pointer"
                                onclick={() => requestDeactivateRepository(item.uuid)}
                            >
                                <img
                                    src="/svg/trash.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-5 filter-red-crimson"
                                    loading="lazy"
                                />
                            </button>
                        {/if}
                    </div>
                </article>
            {/each}
        </div>
    {/if}
</Section>
