<script>
    import { router } from "@inertiajs/svelte";

    import { Button, EmptyState, GridList, IconButton, Offcanvas, Section } from "@/lib/components/private";
    import { PollForm } from "@/lib/widgets/private";
    import { pollPermissions, resolveStatusBackground } from "@/lib/utils";

    export let title;
    export let polls = null;
    export let latestPoll = null;

    const can = pollPermissions();
    let offcanvasRef;
    let pollSelected;
    let voting = false;

    $: offcanvasTitle = pollSelected ? 'Atualizar enquete' : 'Cadastrar enquete'
    
    let actions = [
        {
            title: "Criar",
            icon: "/svg/plus.svg",
            permission: can.create,
            onClick: () => {
                pollSelected = null;
                offcanvasRef.open();
            }
        },
    ]; 

    function requestDeactivate(poll) {
        router.patch(`/panel/media/poll/${poll.uuid}/deactivate`, {}, {
            preserveScroll: true,
        });
    }

    function submitVote(event) {
        const formData = new FormData(event.target);
        const option = formData.get("option");

        router.post(`/panel/media/poll/option/${option}/vote`, {}, {
            preserveScroll: true,
            preserveState: false,
            onStart: () => voting = true,
            onFinish: () => voting = false,
        });
    }

    function optionPercent(option) {
        if (!latestPoll.data.total_votes) return 0;

        return (option.votes / latestPoll.data.total_votes) * 100;
    }
</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <PollForm {pollSelected} {close} />
    </div>
</Offcanvas>

{#if polls}
    <Section {title} {actions}>
        {#if latestPoll}
            <form on:submit|preventDefault={submitVote} class={`${latestPoll.data.has_voted ? 'opacity-50 pointer-events-none' : null}`}>
                <div class="w-full bg-gradient-blue-cerulean-glow p-4 rounded-md">
                    <h2 class="text-center text-orange-morning text-xl lg:text-2xl font-noto-sans font-extrabold uppercase italic">
                        {latestPoll.data.question}
                    </h2>
                    <div class="flex flex-col lg:flex-row gap-5 lg:gap-8 justify-center mt-5 mb-10 lg:my-13">
                        {#each latestPoll.data.options as item}
                            <div class="w-full lg:w-44 flex gap-2">
                                <input
                                    id={item.uuid}
                                    name="option"
                                    type="radio"
                                    value={item.uuid}
                                    class="shrink-0 w-5 h-5 mt-2"
                                />
                                <div class="min-w-0">
                                    <label for={item.uuid} title={item.option} class="block max-w-full fonto-noto-sans font-bold text-suspense-aurora text-lg leading-5 uppercase italic">
                                        {item.option}
                                    </label>
                                    <div class="relative flex items-center min-w-30 w-full h-3.5 bg-black rounded-full px-2 select-none mt-1">
                                        <div
                                            class={[
                                                "h-1.5 bg-orange-500 rounded-sm",
                                                optionPercent(item) > 0 ? "min-w-8" : "",
                                            ]}
                                            style={`width: ${optionPercent(item)}%`}
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        {/each}
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-3 md:flex-nowrap">
                        <Button
                            aria-label="votar"
                            type="submit"
                            variant="accent"
                            shape="pill"
                            class="order-1 md:order-3"
                            loading={voting}
                        >
                            Votar
                        </Button>
                        <div class="font-noto-sans font-bold italic uppercase order-2 md:order-1">
                            <span class="font-extrabold text-suspense-aurora text-3xl">
                                {latestPoll.data.total_votes}
                            </span>
                            <span class="text-orange-morning text-sm">
                                Votos
                            </span>
                        </div>
                        <span class="w-full font-noto-sans font-normal text-orange-morning text-sm order-3 md:order-2 md:w-auto md:ml-auto">
                            ** Vote com sabedoria, após confirmar, o voto não pode ser mudado e você não pode votar novamente**
                        </span>
                    </div>
                </div>
            </form>
        {/if}

        {#if polls.data.length > 0}
        <GridList as="div" preset="content" class="mt-5">
            {#each polls.data as item}
                <div class="w-full h-40 bg-blue-ocean rounded-md overflow-hidden relative">
                    <article>
                        <div class="p-3">
                            <h3 class="font-noto-sans font-normal text-md text-suspense-aurora uppercase">
                                {item.question}
                            </h3>
                        </div>
                        <div class={`grid grid-cols-[0.4fr_1fr_0.6fr] items-center absolute bottom-0 w-full py-1 px-2 ${resolveStatusBackground(item)}`}>
                            <div class="flex items-center gap-1 font-noto-sans font-extrabold italic uppercase text-md text-suspense-aurora truncate">
                                <img
                                    src="/svg/votes.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-5 filter-suspense-aurora"
                                    loading="lazy"
                                />
                                {item.total_votes ?? 0}
                            </div>
                            <div></div>
                            <div class="flex gap-1 justify-end">
                                {#if can.deactivate}
                                    <IconButton
                                        variant="trash"
                                        label="Desativar"
                                        size="sm"
                                        surface="dark"
                                        on:click={() => requestDeactivate(item)}
                                    />
                                {/if}
                                {#if can.update}
                                    <IconButton
                                        variant="edit"
                                        label="Atualizar"
                                        size="sm"
                                        surface="dark"
                                        on:click={() => {
                                                pollSelected = item;
                                                offcanvasRef.open();
                                            }}
                                    />
                                {/if}
                            </div>
                        </div>
                    </article>
                </div>
            {/each}
        </GridList>
        {:else}
            <EmptyState
                class="mt-5"
                title="Nenhuma enquete encontrada"
                description="As enquetes cadastradas aparecerão aqui."
            />
        {/if}
    </Section>
{/if}
