<script>
    export let title;

    import { router, page } from "@inertiajs/svelte";
    import { Section, Offcanvas } from "@/ui/components/private";
    import { PollForm } from "@/ui/widgets/private";
    import { pollPermissions } from "@/utils";

    $: ({ polls } = $page.props);

    $:console.log(polls);

    let can = pollPermissions();

    let offcanvasRef;
    let pollSelected;
    $: offcanvasTitle = pollSelected ? 'Atualizar enquete' : 'Cadastrar enquete'
    
    let actions = [
        {
            title: "Criar",
            icon: "/svg/plus.svg",
            permission: can.create,
            onClick: () => offcanvasRef.show()
        },
    ]; 

    let votedPolls = JSON.parse(localStorage.getItem("akiba_poll_voted")) ?? [];
    const submitVote = (event) => {
        const formData = new FormData(event.target);
        const option = formData.get("option");

        router.post(`/panel/media/poll/vote/${option}`, {}, {
                preserveScroll: true,
                onSuccess: () => {
                    votedPolls.push(option);

                    localStorage.setItem(
                        "akiba_poll_voted",
                        JSON.stringify(votedPolls)
                    );
                },
            });
    };
</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <PollForm {identifier} {close} />
    </div>
</Offcanvas>

{#if polls}
    <Section {title} {actions}>
        <div class={["grid gap-5", 
            {"grid-cols-1": polls.data.length === 1 },
            {"grid-cols-1 lg:grid-cols-2": polls.data.length === 2 },
            {"sm:grid-cols-2 lg:grid-cols-3": polls.data.length >= 3 },
        ]}>
            {#each polls.data as item}
                {@const isVoted = votedPolls.includes(item.uuid)}
                <form on:submit|preventDefault={submitVote} class={`${isVoted ? 'opacity-50 pointer-none' : null}`}>
                    <div class="w-full bg-gradient-blue-cerulean-glow p-4 rounded-md">
                        <h2 class={["text-center text-orange-morning font-noto-sans font-extrabold uppercase italic", 
                            {"text-2xl": polls.data.length === 1}, 
                            {"text-xl": polls.data.length >=2}
                        ]}>
                            {item.question}
                        </h2>
                        <div class={["flex",
                            {"flex-row gap-20 justify-center my-8": polls.data.length === 1},
                            {"flex-col gap-5 justify-start my-5": polls.data.length >= 2} 
                        ]}>
                            {#each item.options as options}
                                <div class="w-30 flex gap-2">
                                    <input
                                        id={options.uuid}
                                        name="option"
                                        type="radio"
                                        value={options.uuid}
                                        class={["shrink-0", 
                                            {"w-5 h-5 mt-2": polls.data.length === 1},
                                            {"w-4 h-4 mt-[0.4rem]": polls.data.length >=2}
                                        ]}
                                    />
                                    <div>
                                        <label for={options.uuid} class={["fonto-noto-sans font-bold text-suspense-aurora uppercase italic", 
                                            {"text-xl": polls.data.length === 1}, 
                                            {"text-lg": polls.data.length >=2}
                                        ]}>
                                            {options.option}
                                        </label>
                                        <div class="relative flex items-center w-30 h-3 bg-black rounded-full px-2 select-none mt-1">
                                        <div class="h-1 bg-orange-500 rounded-sm" style={`width: ${(options.votes / item.total_votes) * 100}%`}></div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                        {#if polls.data.length === 1}
                            <div class="flex justify-between">
                                <div class="font-noso-sans font-bold italic uppercase">
                                    <span class="font-extrabold text-suspense-aurora text-3xl">
                                        {item.total_votes}
                                    </span>
                                    <span class="text-orange-morning text-sm">
                                        Votos
                                    </span>
                                </div>
                                <div class="flex gap-2 items-center">
                                    <span class="font-noto-sans font-normal text-orange-morning text-sm">
                                        ** Vote com sabedoria, após confirmar, o voto não pode ser mudado e você não pode votar novamente**
                                    </span>
                                    <button
                                        aria-label="votar"
                                        type="submit"
                                        class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-blue-marinho py-2 px-6 rounded-full bg-orange-citric"
                                    >
                                        Votar
                                    </button>
                                </div>
                            </div>
                        {:else}
                            <div class="flex justify-between my-2">
                                <div class="font-noso-sans font-bold italic uppercase">
                                    <span class="font-extrabold text-suspense-aurora text-3xl">
                                        {item.total_votes}
                                    </span>
                                    <span class="text-orange-morning text-sm">
                                        Votos
                                    </span>
                                </div>
                                <button
                                    aria-label="votar"
                                    type="submit"
                                    class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-blue-marinho py-2 px-6 rounded-full bg-orange-citric"
                                >
                                    Votar
                                </button>
                            </div>
                            <span class="font-noto-sans font-normal text-orange-morning text-sm">
                                ** Vote com sabedoria, após confirmar, o voto não pode ser mudado e você não pode votar novamente**
                            </span>
                        {/if}
                    </div>
                </form>
            {/each}
        </div>
    </Section>
{/if}
