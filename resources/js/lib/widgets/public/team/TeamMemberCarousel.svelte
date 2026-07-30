<script>
    import { createEventDispatcher } from "svelte";

    export let members = [];
    export let selectedMember = null;

    const dispatch = createEventDispatcher();
</script>

<div class="grid grid-cols-1 items-center gap-1 sm:grid-cols-[2.5rem_minmax(0,1fr)_2.5rem] md:grid-cols-[3rem_minmax(0,1fr)_3rem] md:gap-4">
    <button
        type="button"
        aria-label="Membro anterior"
        class="hidden size-14 cursor-pointer items-center justify-center rounded-full transition hover:scale-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric motion-reduce:transform-none sm:flex md:size-18"
        on:click={() => dispatch("previous")}
    >
        <img
            src="/svg/chevron-left.svg"
            alt=""
            aria-hidden="true"
            class="size-11 filter-orange-citric md:size-14"
        />
    </button>

    <div class="mx-auto grid w-full max-w-[18rem] grid-cols-3 gap-x-4 gap-y-6 pt-3 sm:max-w-none sm:grid-cols-4 sm:gap-x-3 sm:gap-y-8 sm:pt-6 md:grid-cols-6 md:gap-y-10 md:pt-8 lg:grid-cols-7 lg:gap-x-4">
        {#each members as member}
            <button
                type="button"
                class="group/member flex w-full min-w-0 cursor-pointer flex-col items-center text-center focus-visible:outline-none"
                aria-pressed={selectedMember === member}
                on:click={() => dispatch("select", member)}
            >
                <span
                    class={[
                        "relative block size-18 overflow-hidden rounded-full bg-blue-ocean ring-2 transition duration-200 group-hover/member:scale-105 group-focus-visible/member:scale-105 motion-reduce:transform-none sm:h-14 sm:w-full sm:overflow-visible sm:rounded-sm sm:ring-0 md:h-16",
                        selectedMember === member
                            ? "ring-orange-citric"
                            : "ring-blue-skywave/35",
                    ]}
                >
                    <img
                        src={member.avatar}
                        alt={member.name}
                        class="absolute right-1/2 top-0 h-[145%] w-auto max-w-[145%] translate-x-1/2 scale-135 object-contain object-top drop-shadow-[0_0.25rem_0.35rem_rgba(0,0,20,0.45)] sm:right-0 sm:top-auto sm:bottom-0 sm:h-28 sm:max-w-[150%] sm:translate-x-0 sm:scale-100 sm:object-bottom md:h-32"
                    />
                </span>
                <span class="mt-2 block max-w-full truncate px-0.5 font-noto-sans text-[0.7rem] font-black uppercase italic leading-tight text-suspense-aurora min-[390px]:text-xs sm:text-sm">
                    {member.name}
                </span>
            </button>
        {/each}
    </div>

    <button
        type="button"
        aria-label="Próximo membro"
        class="hidden size-14 cursor-pointer items-center justify-center rounded-full transition hover:scale-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric motion-reduce:transform-none sm:flex md:size-18"
        on:click={() => dispatch("next")}
    >
        <img
            src="/svg/chevron-right.svg"
            alt=""
            aria-hidden="true"
            class="size-11 filter-orange-citric md:size-14"
        />
    </button>
</div>
