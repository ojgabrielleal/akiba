<script>
    import { EditorialTitle } from "@/lib/components/public";

    import TeamMemberCarousel from "./TeamMemberCarousel.svelte";
    import TeamMemberProfile from "./TeamMemberProfile.svelte";

    export let members = [];

    const allMembersRole = { key: "all", label: "Todos" };

    let activeRole = null;
    let selectedIndex = 0;
    let selectedMemberUuid = null;
    let selectedMemberSlug = resolveMemberSlug();
    let carouselStart = 0;

    $: teamMembers = normalizeMembers(members?.data ?? members);
    $: roles = [allMembersRole, ...resolveRoles(teamMembers)];
    $: if (!activeRole || !roles.some((role) => role.key === activeRole.key)) {
        activeRole = roles[0] ?? null;
    }
    $: targetMember = selectedMemberSlug
        ? teamMembers.find((member) => member.slug === selectedMemberSlug)
        : null;
    $: if (targetMember && selectedMemberUuid !== targetMember.uuid) {
        activeRole = allMembersRole;
        selectedMemberUuid = targetMember.uuid;
        carouselStart = Math.max(teamMembers.findIndex((member) => member.uuid === targetMember.uuid), 0);
    }
    $: filteredMembers =
        activeRole && activeRole.key !== allMembersRole.key
            ? teamMembers.filter((member) => member.categories.some((role) => role.key === activeRole.key))
            : teamMembers;
    $: if (filteredMembers.length > 0 && !filteredMembers.some((member) => member.uuid === selectedMemberUuid)) {
        selectedMemberUuid = filteredMembers[0].uuid;
    }
    $: selectedIndex = Math.max(
        filteredMembers.findIndex((member) => member.uuid === selectedMemberUuid),
        0,
    );
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
                    uuid: member.uuid,
                    slug: member.slug,
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

    const selectRole = (role) => {
        activeRole = role;
        selectedMemberUuid = null;
        selectedMemberSlug = null;
        carouselStart = 0;
    };

    const selectMember = (member) => {
        selectedMemberUuid = member.uuid;
        selectedMemberSlug = member.slug;
        updateMemberQuery(member);
    };

    function resolveMemberSlug() {
        if (typeof window === "undefined") return;

        return new URL(window.location.href).searchParams.get("member");
    }

    const updateMemberQuery = (member) => {
        if (!member?.slug || typeof window === "undefined") return;

        const url = new URL(window.location.href);
        url.searchParams.set("member", member.slug);

        window.history.replaceState(window.history.state, "", `${url.pathname}${url.search}`);
    };

    const moveSelection = (direction) => {
        if (!filteredMembers.length) return;

        if (filteredMembers.length > 7) {
            carouselStart = (carouselStart + direction + filteredMembers.length) % filteredMembers.length;
            selectedMemberUuid = filteredMembers[carouselStart].uuid;
            updateMemberQuery(filteredMembers[carouselStart]);

            return;
        }

        const nextIndex =
            (selectedIndex + direction + filteredMembers.length) %
            filteredMembers.length;

        selectedMemberUuid = filteredMembers[nextIndex].uuid;
        updateMemberQuery(filteredMembers[nextIndex]);
    };

</script>

<section class="bg-blue-night pt-10 text-suspense-aurora">
    <EditorialTitle title="Equipe" listLabel="Filtros da equipe">
        {#each roles as role (role.key)}
            <li class="flex h-8 items-center border-l border-neutral-gray/35 px-3 first:border-none first:pl-0 xl:px-5">
                <button
                    type="button"
                    class={[
                        "shrink-0 cursor-pointer rounded-md font-noto-sans text-base font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-citric focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric motion-reduce:transform-none motion-reduce:transition-none",
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
    </EditorialTitle>

    <div class="bg-blue-marinho pb-8 md:pb-10">
    <div class="container-page pt-8 md:pt-16">
        {#if selectedMember}
        <TeamMemberCarousel
            members={carouselMembers}
            {selectedMember}
            on:previous={() => moveSelection(-1)}
            on:next={() => moveSelection(1)}
            on:select={(event) => selectMember(event.detail)}
        />

        <div>
            <TeamMemberProfile member={selectedMember} />
        </div>
        {/if}
    </div>
    </div>
</section>
