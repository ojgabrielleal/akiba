<script>
    export let title;

    import { page } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/private/";
    import { resolveDay, resolveHour } from "@/utils";

    $: ({ programs } = $page.props);
</script>

{#if programs}
    <Section {title}>
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-15 mt-10">
            {#each programs.data as program}
                {#if program.access_type === "private"}
                    <article class="w-full">
                        <div>
                            <img
                                class="w-40 mb-3"
                                src={program.image}
                                alt={program.name}
                                loading="lazy"
                            />
                            <div class="w-full rounded-md py-3 px-4 bg-suspense-aurora relative mb-2">
                                <div class="text-blue-skywave text-md font-noto-sans uppercase">
                                    <strong class="font-extrabold">
                                        Com:
                                    </strong>
                                    {program.host.nickname}
                                </div>
                                <img
                                    class="w-36 aspect-square absolute right-0 bottom-0 object-cover object-top"
                                    src={program.host.avatar}
                                    alt={program.host.nickname}
                                    loading="lazy"
                                />
                            </div>
                            {#each program.schedules as schedule}
                                <dl class="w-full rounded-md py-2 px-4 bg-suspense-aurora flex justify-between mb-2">
                                    <dt class="block text-black text-md font-noto-sans italic uppercase font-extrabold">
                                        {resolveDay(schedule.day, "short")}
                                    </dt>
                                    <dd class="block text-black font-noto-sans uppercase">
                                        {resolveHour(schedule.hour)}
                                    </dd>
                                </dl>
                            {/each}
                        </div>
                    </article>
                {/if}
            {/each}
        </div>
    </Section>
{/if}
