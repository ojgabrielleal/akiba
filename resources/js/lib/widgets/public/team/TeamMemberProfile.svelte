<script>
    import { socialIcons, userPreferences } from "@/lib/constants";

    export let member = null;

    const getSocialIcon = (key) =>
        socialIcons.find((socialIcon) => socialIcon.key === key);

    const formatPreference = (preference) =>
        userPreferences.find((option) => option.value === preference)?.name ?? preference;
</script>

{#if member}
    <article class="mt-10 md:mt-28">
        <div class="relative rounded-md bg-gradient-blue-ocean-skywave">
            <img
                src={member.cover}
                alt=""
                aria-hidden="true"
                class="absolute bottom-0 left-0 hidden h-48 w-auto max-w-[11rem] object-contain object-left-bottom drop-shadow-[0_0.35rem_0.45rem_rgba(0,0,20,0.45)] md:block"
            />
            <div class="min-h-32 px-5 py-6 md:ml-36 md:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="mt-1">
                        <h2 class="font-noto-sans text-3xl font-black uppercase italic text-suspense-aurora sm:text-4xl md:text-5xl">
                            {member.name}
                        </h2>
                        <p class="mt-1 font-noto-sans text-sm font-black uppercase italic text-suspense-aurora">
                            ({member.fullName})
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-3 sm:justify-end">
                        {#each Object.entries(member.socials) as [key, address]}
                            {@const socialIcon = getSocialIcon(key)}
                            {#if socialIcon}
                                <a
                                    href={address}
                                    target="_blank"
                                    rel="noreferrer"
                                    aria-label={socialIcon.name}
                                    class="flex size-11 items-center justify-center transition hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric motion-reduce:transform-none"
                                >
                                    <img
                                        src={socialIcon.icon}
                                        alt=""
                                        aria-hidden="true"
                                        class="size-9 filter-suspense-aurora"
                                    />
                                </a>
                            {/if}
                        {/each}
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-2 grid gap-2 font-noto-sans text-xs font-black uppercase italic text-blue-night sm:text-sm md:grid-cols-3">
            <div class="rounded-sm bg-suspense-aurora px-5 py-1 text-center">
                {member.role}
            </div>
            <div class="rounded-sm bg-suspense-aurora px-5 py-1 text-center">
                {member.location}
            </div>
            <div class="rounded-sm bg-suspense-aurora px-5 py-1 text-center">
                {member.age}
            </div>
        </div>

        <p class="public-team-member-bio mt-6 w-full font-noto-sans text-sm font-normal leading-snug text-suspense-aurora/75 sm:text-base md:text-lg">
            {member.bio}
        </p>

        <div class="mt-8 grid items-start gap-8 md:mt-14 md:grid-cols-2 lg:grid-cols-3 lg:gap-6">
            {#if member.favoriteGenres.length > 0}
                <section class="flex h-full min-w-0 flex-col">
                    <h3 class="public-team-member-section-title mb-1 min-h-8 text-center font-noto-sans text-lg font-black uppercase italic text-orange-amber">
                        Gosta de:
                    </h3>
                    <div class="grid gap-3">
                        {#each member.favoriteGenres as genre}
                            <div class="flex min-h-10 items-center justify-center rounded-md bg-blue-ocean px-4 py-1 text-center font-noto-sans text-lg font-black uppercase italic text-suspense-aurora sm:px-5 sm:py-1.5">
                                {formatPreference(genre)}
                            </div>
                        {/each}
                    </div>
                </section>
            {/if}

            {#if member.dislikedGenres.length > 0}
                <section class="flex h-full min-w-0 flex-col">
                    <h3 class="public-team-member-section-title mb-1 min-h-8 text-center font-noto-sans text-lg font-black uppercase italic text-orange-amber">
                        Não gosta de:
                    </h3>
                    <div class="grid gap-3">
                        {#each member.dislikedGenres as genre}
                            <div class="flex min-h-10 items-center justify-center rounded-md bg-blue-ocean px-4 py-1 text-center font-noto-sans text-lg font-black uppercase italic text-suspense-aurora sm:px-5 sm:py-1.5">
                                {formatPreference(genre)}
                            </div>
                        {/each}
                    </div>
                </section>
            {/if}

            {#if member.topAnimes.length > 0}
                <section class="flex h-full min-w-0 flex-col md:col-span-2 lg:col-span-1">
                    <h3 class="public-team-member-section-title mb-1 min-h-8 text-center font-noto-sans text-lg font-black uppercase italic text-orange-amber">
                        Meu Top 3:
                    </h3>
                    <div class="grid h-36 min-h-36 max-h-36 grid-cols-3 gap-3 sm:gap-4">
                        {#each member.topAnimes as anime}
                            <div class="group/anime relative h-full min-h-0 w-full">
                                <div
                                    class="flex h-full w-full overflow-hidden rounded-md bg-blue-ocean/90 ring-1 ring-blue-skywave/20"
                                    aria-label={`Anime favorito ${anime.position}`}
                                >
                                    {#if anime?.image}
                                        <img
                                            src={anime.image}
                                            alt={anime.name}
                                            class="h-full w-full object-cover object-top"
                                        />
                                    {/if}
                                </div>
                                <div class="pointer-events-none absolute inset-x-1 bottom-2 z-20 hidden rounded-sm bg-blue-night/90 px-2 py-1 text-center font-noto-sans text-[10px] leading-tight text-suspense-aurora opacity-0 shadow-lg transition-opacity group-hover/anime:opacity-100 lg:block">
                                    <span class="line-clamp-2">
                                        {anime.name}
                                    </span>
                                </div>
                            </div>
                        {/each}
                    </div>
                </section>
            {/if}
        </div>
    </article>
{/if}
