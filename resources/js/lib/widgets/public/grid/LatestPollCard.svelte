<script>
    import { router } from "@inertiajs/svelte";

    import { AuthGuard, Button } from "@/lib/components/public";

    export let poll = null;
    export let oauth = {};

    let selectedOption = null;
    let voting = false;

    const optionPercent = (pollItem, option) => pollItem?.total_votes ? (option.votes / pollItem.total_votes) * 100 : 0;

    function submitVote() {
        if (!selectedOption || voting || poll?.has_voted) return;

        voting = true;
        router.post(`/poll/option/${selectedOption}/vote`, {}, {
            preserveScroll: true,
            onFinish: () => {
                voting = false;
            },
        });
    }
</script>

{#if poll}
    <form
        on:submit|preventDefault={submitVote}
        class={[
            "public-default-gradient w-full rounded-md bg-gradient-blue-cerulean-glow px-4 py-5 sm:px-6 lg:px-8",
            poll.has_voted && "pointer-events-none opacity-50",
        ]}
    >
        <h2 class="text-center font-noto-sans text-xl font-extrabold uppercase italic text-orange-morning lg:text-2xl">
            {poll.question}
        </h2>
        <div class="my-7 grid gap-5 sm:grid-cols-2 lg:my-12 xl:grid-cols-4">
            {#each poll.options as option (option.uuid)}
                <div class="grid min-w-0 grid-cols-[1.25rem_minmax(0,1fr)] gap-2">
                    <input
                        id={option.uuid}
                        bind:group={selectedOption}
                        name="option"
                        type="radio"
                        value={option.uuid}
                        class="mt-1 h-5 w-5 cursor-pointer accent-orange-citric"
                    />
                    <div class="min-w-0">
                        <label for={option.uuid} title={option.option} class="block max-w-full break-words font-noto-sans text-base font-bold uppercase italic leading-tight text-suspense-aurora sm:text-lg">
                            {option.option}
                        </label>
                        <div class="relative mt-2 flex h-3.5 w-full select-none items-center rounded-full bg-black px-2">
                            <div
                                class={[
                                    "h-1.5 rounded-sm bg-orange-500",
                                    optionPercent(poll, option) > 0 ? "min-w-8" : "",
                                ]}
                                style={`width: ${optionPercent(poll, option)}%`}
                            ></div>
                        </div>
                    </div>
                </div>
            {/each}
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 md:flex-nowrap">
            <AuthGuard
                {oauth}
                compact
                buttonLabel="Entre para votar"
                filters="filter-blue-night"
                containerClass="order-1 md:order-3"
                buttonClass="text-blue-night"
            >
                <Button
                    aria-label="votar"
                    type="submit"
                    variant="primary"
                    shape="pill"
                    class="order-1 md:order-3"
                    loading={voting}
                    disabled={!selectedOption || poll.has_voted}
                >
                    {selectedOption ? "Confirmar seu voto" : "Votar"}
                </Button>
            </AuthGuard>
            <div class="order-2 font-noto-sans font-bold uppercase italic md:order-1">
                <span class="text-3xl font-extrabold text-suspense-aurora">
                    {poll.total_votes}
                </span>
                <span class="text-sm text-orange-morning">
                    Votos
                </span>
            </div>
            <span class="order-3 w-full font-noto-sans text-sm font-normal uppercase italic text-orange-morning md:order-2 md:ml-auto md:w-auto">
                ** Vote com sabedoria, após confirmar, o voto não pode ser mudado e você não pode votar novamente**
            </span>
        </div>
    </form>
{/if}
