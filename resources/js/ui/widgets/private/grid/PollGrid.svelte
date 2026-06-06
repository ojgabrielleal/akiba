<script>
    import { router, page } from "@inertiajs/svelte";
    import { Section, Offcanvas } from "@/ui/components/private";
    import { PollForm } from "@/ui/widgets/private";
    import { pollPermissions } from "@/utils";

    $: ({ polls } = $page.props);

    let can = pollPermissions();

    let offcanvasRef;
    let identifier;

    let storageVotedPolls = JSON.parse(localStorage.getItem("voted") || "[]");

    const submitVote = (event, item) => {
        const form = event.target;
        const formData = new FormData(form);
        const option = formData.get("option");

        router.post(`/panel/media/poll/vote/${option}`, {}, {
                preserveScroll: true,
                onSuccess: () => {
                    localStorage.setItem(
                        "voted",
                        JSON.stringify(storageVotedPolls),
                    );
                    storageVotedPolls.push(item.uuid);
                    storageVotedPolls = storageVotedPolls;
                },
            });
    };

    const requestDeactivatePoll = (poll) => {
        router.delete(`/panel/media/poll/${poll}`, {}, {
                preserveScroll: true,
            });
    };
</script>

<Offcanvas bind:this={offcanvasRef} title={identifier ? "Atualizar enquete" : "Cadastrar enquete"}>
    <div slot="content" let:close>
        <PollForm {identifier} {close} />
    </div>
</Offcanvas>

{#if polls}
    <Section title="Enquetes">
        {#if can.create || can.update}
            <div class="flex justify-center">
                <button type="button" class="cursor-pointer text-suspense-aurora text-xl font-noto-sans font-extrabold uppercase italic rounded-sm py-1 px-3 bg-orange-amber" on:click={() => { offcanvasRef.open(); identifier = null; }}>
                    Criar enquete
                </button>
            </div>
        {/if}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-10">
            {#each polls.data as item}
                {@const alreadyVoted = storageVotedPolls.includes(item.uuid)}
                <form on:submit|preventDefault={(event)>
                        submitVote(event, item)}
                    class="flex flex-col justify-between gap-5 bg-blue-skywave p-5 rounded-md"
                >
                    <div class="text-suspense-aurora text-xl text-start font-noto-sans font-extrabold">
                        {item.question}
                    </div>
                    <div class="flex flex-col gap-3">
                        {#each item.options as optitem}
                            <div class="inline-flex items-center">
                                <div class="relative flex items-center cursor-pointer">
                                    <input
                                        id={optitem.uuid}
                                        name="option"
                                        type="radio"
                                        class="peer h-5 w-5 cursor-pointer appearance-none rounded-full bg-suspense-aurora"
                                        value={optitem.uuid}
                                        disabled={alreadyVoted}
                                        required
                                    />
                                    <div class="absolute bg-blue-skywave w-2/4 h-2/4 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></div>
                                </div>
                                <label for={optitem.uuid} class="w-full ml-2 flex justify-between text-suspense-aurora text-md font-noto-sans cursor-pointer">
                                    <div>
                                        {optitem.option}
                                    </div>
                                    <div>
                                        {optitem.votes}
                                    </div>
                                </label>
                            </div>
                        {/each}
                    </div>
                    <div class="flex justify-between">
                        {#if can.vote.create}
                            <button
                                type="submit"
                                class="cursor-pointer text-suspense-aurora text-md font-noto-sans font-extrabold uppercase italic rounded-sm py-1 px-3 bg-orange-amber disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled={alreadyVoted}
                            >
                                Votar
                            </button>
                        {/if}
                        <div class="flex gap-3">
                            {#if can.show_button_update}
                                <button
                                    type="button"
                                    aria-label="Editar"
                                    class="cursor-pointer"
                                    on:click={() => { offcanvasRef.open(); identifier = item.uuid; }}
                                >
                                    <img
                                        src="/svg/edit.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-5 filter-suspense-aurora"
                                        loading="lazy"
                                    />
                                </button>
                            {/if}
                            {#if can.deactivate}
                                <button
                                    type="button"
                                    class="cursor-pointer"
                                    aria-label="Desativar"
                                    on:click={requestDeactivatePoll(item.uuid)}
                                >
                                    <img
                                        src="/svg/trash.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-5 filter-suspense-aurora"
                                        loading="lazy"
                                    />
                                </button>
                            {/if}
                        </div>
                    </div>
                </form>
            {/each}
        </div>
    </Section>
{/if}
