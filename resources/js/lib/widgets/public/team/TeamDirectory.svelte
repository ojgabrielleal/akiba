<script>
    import { socialIcons, userPreferences } from "@/lib/constants";

    export let members = [];

    const allMembersRole = { key: "all", label: "Todos" };

    let activeRole = null;
    let selectedIndex = 0;
    let carouselStart = 0;

    $: teamMembers = normalizeMembers(members?.data ?? members);
    $: roles = [allMembersRole, ...resolveRoles(teamMembers)];
    $: if (!activeRole || !roles.some((role) => role.key === activeRole.key)) {
        activeRole = roles[0] ?? null;
        selectedIndex = 0;
    }
    $: filteredMembers =
        activeRole && activeRole.key !== allMembersRole.key
            ? teamMembers.filter((member) => member.categories.some((role) => role.key === activeRole.key))
            : teamMembers;
    $: selectedMember = filteredMembers[selectedIndex] ?? filteredMembers[0] ?? teamMembers[0];
    $: carouselMembers = resolveCarouselMembers(filteredMembers, carouselStart);

    const normalizeMembers = (items = []) => {
        const normalized = items
            .filter(Boolean)
            .map((member) => {
                const memberRoles = member.roles ?? [];
                const categories = memberRoles
                    .map((role) => ({
                        key: role.name ?? role.label,
                        label: resolveRoleLabel(role),
                    }))
                    .filter((role) => role.key && role.label);
                const socials = Object.fromEntries(
                    (member.socials ?? [])
                        .filter((social) => social.url)
                        .map((social) => [social.name.toLocaleLowerCase("pt-BR").replace(/\s+/g, ""), social.url])
                );

                return {
                    name: member.nickname ?? member.name,
                    fullName: member.name,
                    role: memberRoles.map((role) => role.label).join(" - ") || "Equipe Rede Akiba",
                    location: [member.city, member.state].filter(Boolean).join(" - ") || member.country || "Rede Akiba",
                    age: formatAge(member.birth_date),
                    avatar: member.avatar || "/img/placeholders/avatar.webp",
                    cover: member.avatar || "/img/placeholders/avatar.webp",
                    categories,
                    favoriteGenres: (member.preferences?.likes ?? []).map((item) => item.content).filter(Boolean),
                    dislikedGenres: (member.preferences?.unlikes ?? []).map((item) => item.content).filter(Boolean),
                    socials,
                    topAnimes: member.top_animes ?? [],
                    bio: member.bibliography || "Perfil em atualização.",
                };
            });

        return normalized;
    };

    const resolveRoleLabel = (role) =>
        role.public_label || role.label;

    const resolveRoles = (items = []) =>
        Array.from(
            items
                .flatMap((member) => member.categories)
                .reduce((rolesByKey, role) => rolesByKey.set(role.key, role), new Map())
                .values()
        )
            .sort((first, second) => first.label.localeCompare(second.label, "pt-BR"));

    const resolveCarouselMembers = (items = [], start = 0) => {
        if (items.length <= 7) return items;

        return Array.from({ length: 7 }, (_, offset) => items[(start + offset) % items.length]);
    };

    const formatAge = (birthDate) => {
        if (!birthDate) return "Idade não informada";

        const birth = new Date(`${birthDate}T00:00:00`);
        const today = new Date();
        let age = today.getFullYear() - birth.getFullYear();
        const hadBirthday =
            today.getMonth() > birth.getMonth()
            || (today.getMonth() === birth.getMonth() && today.getDate() >= birth.getDate());

        if (!hadBirthday) age -= 1;

        return `${age} anos`;
    };

    const getSocialIcon = (key) =>
        socialIcons.find((socialIcon) => socialIcon.key === key);

    const formatPreference = (preference) =>
        userPreferences.find((option) => option.value === preference)?.name ?? preference;

    const selectRole = (role) => {
        activeRole = role;
        selectedIndex = 0;
        carouselStart = 0;
    };

    const moveSelection = (direction) => {
        if (!filteredMembers.length) return;

        if (filteredMembers.length > 7) {
            carouselStart = (carouselStart + direction + filteredMembers.length) % filteredMembers.length;
            selectedIndex = carouselStart;

            return;
        }

        selectedIndex =
            (selectedIndex + direction + filteredMembers.length) %
            filteredMembers.length;
    };
</script>

<section class="bg-blue-night pt-10 text-suspense-aurora">
    <header
        class="relative isolate overflow-hidden bg-cover bg-right bg-no-repeat py-5 lg:bg-contain"
        style="background-image: url('/img/textures/screentone.webp'), var(--gradient-blue-ocean-cerulean);"
    >
        <div class="container-page relative">
            <h1 class="text-center font-noto-sans text-4xl font-black uppercase italic leading-none text-orange-citric sm:text-5xl md:text-6xl">
                Equipe
            </h1>
        </div>
    </header>

    <nav aria-label="Filtros da equipe">
        <div class="container-page overflow-x-auto">
            <ul class="flex min-w-max items-center justify-center gap-y-3 py-8">
                {#each roles as role (role.key)}
                    <li class="flex h-8 items-center border-l border-neutral-gray/35 px-3 first:border-none first:pl-0 xl:px-5">
                        <button
                            type="button"
                            class={[
                                "shrink-0 cursor-pointer rounded-md font-noto-sans text-base font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-citric focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none",
                                activeRole?.key === role.key
                                    ? "text-orange-citric"
                                    : "text-neutral-gray",
                            ]}
                            on:click={() => selectRole(role)}
                        >
                            {role.label}
                        </button>
                    </li>
                {/each}
            </ul>
        </div>
    </nav>

    <div class="bg-blue-marinho pb-8 md:pb-10">
    <div class="container-page pt-12 md:pt-16">
        {#if selectedMember}
        <div class="grid grid-cols-[2rem_minmax(0,1fr)_2rem] items-center gap-1 sm:grid-cols-[2.5rem_minmax(0,1fr)_2.5rem] md:grid-cols-[3rem_minmax(0,1fr)_3rem] md:gap-4">
            <button
                type="button"
                aria-label="Membro anterior"
                class="flex size-10 cursor-pointer items-center justify-center transition hover:scale-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric motion-reduce:transform-none sm:size-14 md:size-18"
                on:click={() => moveSelection(-1)}
            >
                <img
                    src="/svg/chevron-left.svg"
                    alt=""
                    aria-hidden="true"
                    class="size-8 filter-orange-citric sm:size-11 md:size-14"
                />
            </button>

            <div class="mx-auto grid w-full grid-cols-3 gap-x-3 gap-y-8 pt-6 sm:grid-cols-4 md:grid-cols-6 md:gap-y-10 md:pt-8 lg:grid-cols-7 lg:gap-x-4">
                {#each carouselMembers as member, index}
                    <button
                        type="button"
                        class="group/member flex w-full min-w-0 cursor-pointer flex-col items-center text-center focus-visible:outline-none"
                        aria-pressed={selectedMember === member}
                        on:click={() => (selectedIndex = filteredMembers.indexOf(member))}
                    >
                        <span
                            class="relative block h-12 w-full overflow-visible rounded-sm bg-blue-ocean transition duration-200 group-hover/member:-translate-y-1 group-focus-visible/member:-translate-y-1 motion-reduce:transform-none sm:h-14 md:h-16"
                        >
                            <img
                                src={member.avatar}
                                alt={member.name}
                                class="absolute right-0 bottom-0 h-24 w-auto max-w-[150%] object-contain object-bottom drop-shadow-[0_0.25rem_0.35rem_rgba(0,0,20,0.45)] sm:h-28 md:h-32"
                            />
                        </span>
                        <span class="mt-2 block max-w-full truncate font-noto-sans text-xs font-black uppercase italic text-suspense-aurora sm:text-sm">
                            {member.name}
                        </span>
                    </button>
                {/each}
            </div>

            <button
                type="button"
                aria-label="Próximo membro"
                class="flex size-10 cursor-pointer items-center justify-center transition hover:scale-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric motion-reduce:transform-none sm:size-14 md:size-18"
                on:click={() => moveSelection(1)}
            >
                <img
                    src="/svg/chevron-right.svg"
                    alt=""
                    aria-hidden="true"
                    class="size-8 filter-orange-citric sm:size-11 md:size-14"
                />
            </button>
        </div>

        <article class="mt-28 md:mt-28">
            <div class="relative rounded-md bg-gradient-blue-ocean-skywave">
                <img
                    src={selectedMember.cover}
                    alt=""
                    aria-hidden="true"
                    class="absolute bottom-0 left-0 hidden h-48 w-auto max-w-[11rem] object-contain object-left-bottom drop-shadow-[0_0.35rem_0.45rem_rgba(0,0,20,0.45)] md:block"
                />
                <div class="min-h-32 px-5 py-6 md:ml-36 md:px-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="mt-1">
                            <h2 class="font-noto-sans text-3xl font-black uppercase italic text-suspense-aurora sm:text-4xl md:text-5xl">
                                {selectedMember.name}
                            </h2>
                            <p class="mt-1 font-noto-sans text-sm font-black uppercase italic text-suspense-aurora">
                                ({selectedMember.fullName})
                            </p>
                        </div>
                        <div class="flex shrink-0 gap-3 sm:justify-end">
                            {#each Object.entries(selectedMember.socials) as [key, address]}
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

            <div class="mt-2 grid gap-2 font-noto-sans text-xs font-black uppercase italic text-blue-marinho sm:text-sm md:grid-cols-3">
                <div class="rounded-sm bg-suspense-honeycream px-5 py-1 text-center">
                    {selectedMember.role}
                </div>
                <div class="rounded-sm bg-suspense-honeycream px-5 py-1 text-center">
                    {selectedMember.location}
                </div>
                <div class="rounded-sm bg-suspense-honeycream px-5 py-1 text-center">
                    {selectedMember.age}
                </div>
            </div>

            <p class="mt-6 w-full font-noto-sans text-sm font-normal leading-snug text-suspense-aurora sm:text-base md:text-lg">
                {selectedMember.bio}
            </p>

            <div class="mt-20 grid items-start gap-8 md:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                {#if selectedMember.favoriteGenres.length > 0}
                    <section class="flex h-full min-w-0 flex-col">
                        <h3 class="mb-1 min-h-8 text-center font-noto-sans text-lg font-black uppercase italic text-orange-citric">
                            Gosta de:
                        </h3>
                        <div class="grid gap-3">
                            {#each selectedMember.favoriteGenres as genre}
                                <div class="flex min-h-10 items-center justify-center rounded-md bg-blue-ocean px-4 py-1 text-center font-noto-sans text-lg font-black uppercase italic text-suspense-aurora sm:px-5 sm:py-1.5">
                                    {formatPreference(genre)}
                                </div>
                            {/each}
                        </div>
                    </section>
                {/if}

                {#if selectedMember.dislikedGenres.length > 0}
                    <section class="flex h-full min-w-0 flex-col">
                        <h3 class="mb-1 min-h-8 text-center font-noto-sans text-lg font-black uppercase italic text-orange-citric">
                            Não gosta de:
                        </h3>
                        <div class="grid gap-3">
                            {#each selectedMember.dislikedGenres as genre}
                                <div class="flex min-h-10 items-center justify-center rounded-md bg-blue-ocean px-4 py-1 text-center font-noto-sans text-lg font-black uppercase italic text-suspense-aurora sm:px-5 sm:py-1.5">
                                    {formatPreference(genre)}
                                </div>
                            {/each}
                        </div>
                    </section>
                {/if}

                {#if selectedMember.topAnimes.length > 0}
                    <section class="flex h-full min-w-0 flex-col md:col-span-2 lg:col-span-1">
                        <h3 class="mb-1 min-h-8 text-center font-noto-sans text-lg font-black uppercase italic text-orange-citric">
                            Meu Top3
                        </h3>
                        <div class="grid h-36 min-h-36 max-h-36 grid-cols-3 gap-3 sm:gap-4">
                            {#each selectedMember.topAnimes as anime}
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
    </div>
    </div>
</section>
