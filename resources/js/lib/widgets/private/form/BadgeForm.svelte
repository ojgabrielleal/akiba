<script>
    import { useForm } from "@inertiajs/svelte";

    import {
        Button,
        CheckboxInput,
        FormField,
        Preview,
        SelectInput,
        TextInput,
    } from "@/lib/components/private";
    import { badgePermissions } from "@/lib/utils";

    export let close = () => {};
    export let badgeSelected = null;
    export let badgeTargets = null;

    const can = badgePermissions();
    function badgeCodeFromName(name) {
        return (name ?? "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, "_")
            .replace(/^_+|_+$/g, "");
    }

    const sourceOptions = [
        { value: "enigmagame", label: "Enigma Otaku", types: ["stealable"] },
        { value: "song_request", label: "Pedidos musicais", types: ["stealable", "achievement"] },
        { value: "poll", label: "Enquete", types: ["stealable"] },
        { value: "podcast", label: "Podcasts", types: ["achievement"] },
        { value: "easter_egg", label: "Easter Egg", types: ["achievement"] },
    ];

    const easterEggOptions = [
        { value: "konami", label: "Konami Code" },
        { value: "doom_iddqd", label: "DOOM - God Mode" },
        { value: "mortal_kombat_abacabb", label: "Mortal Kombat - Blood Code" },
        { value: "sonic_2_level_select", label: "Sonic 2 - Level Select" },
        { value: "super_mario_continue", label: "Super Mario Bros. - Continue" },
        { value: "page_404", label: "Página 404" },
    ];

    const triggerOptions = {
        stealable: {
            enigmagame: [
                { value: "enigmagame.most_wins", label: "Pessoa que mais venceu enigmas", needsThreshold: false },
            ],
            song_request: [
                { value: "song_request.most_requests", label: "Pessoa que mais fez pedidos", needsThreshold: false },
            ],
            poll: [
                { value: "poll.most_votes", label: "Quem mais respondeu enquetes", needsThreshold: false },
            ],
        },
        achievement: {
            song_request: [
                { value: "song_request.played_total", label: "Pedidos atendidos", needsThreshold: true },
                { value: "song_request.played_ordinal", label: "Pedido atendido nº", needsThreshold: true },
            ],
            podcast: [
                { value: "podcast.all_listened", label: "Todos os podcasts ouvidos", needsThreshold: false },
                { value: "podcast.listened_total", label: "Quantidade de podcasts ouvidos", needsThreshold: true },
            ],
            easter_egg: [
                { value: "easter_egg", label: "Easter Egg", needsThreshold: false },
            ],
        },
    };

    $: form = useForm({
        _method: badgeSelected ? "PATCH" : "POST",
        name: badgeSelected?.name ?? null,
        code: badgeSelected?.code ?? null,
        description: badgeSelected?.description ?? null,
        image: null,
        type: badgeSelected?.type ?? "fixed",
        source: badgeSelected?.source ?? "enigmagame",
        trigger: badgeSelected?.trigger ?? null,
        easter_egg: badgeSelected?.easter_egg ?? null,
        threshold: badgeSelected?.threshold ?? null,
        starts_at: badgeSelected?.starts_at ?? null,
        ends_at: badgeSelected?.ends_at ?? null,
        daily_starts_at: badgeSelected?.daily_starts_at ?? null,
        daily_ends_at: badgeSelected?.daily_ends_at ?? null,
        audience: "target",
        targets: badgeSelected?.targets?.length
            ? badgeSelected.targets.map((target) => `${target.type}:${target.uuid}`)
            : (badgeSelected?.target_type && badgeSelected?.target_uuid ? [`${badgeSelected.target_type}:${badgeSelected.target_uuid}`] : []),
        is_active: badgeSelected?.is_active ?? true,
    });

    $: if ($form.name !== undefined) {
        $form.code = badgeCodeFromName($form.name);
    }

    $: hasSource = $form.type === "stealable" || $form.type === "achievement";
    $: availableSources = sourceOptions.filter((option) => option.types.includes($form.type));
    $: availableTriggers = triggerOptions[$form.type]?.[$form.source] ?? [];
    $: selectedTrigger = availableTriggers.find((item) => item.value === $form.trigger);
    $: needsThreshold = Boolean(selectedTrigger?.needsThreshold);

    $: if (!hasSource) {
        $form.source = null;
        $form.trigger = null;
    }

    $: hasAudience = $form.type === "fixed" || $form.type === "scheduled";

    $: if ($form.type !== "scheduled") {
        $form.starts_at = null;
        $form.ends_at = null;
        $form.daily_starts_at = null;
        $form.daily_ends_at = null;
    }

    $: if (!needsThreshold) {
        $form.threshold = null;
    }

    $: if ($form.trigger !== "easter_egg") {
        $form.easter_egg = null;
    }

    $: if ($form.trigger === "easter_egg" && !easterEggOptions.some((item) => item.value === $form.easter_egg)) {
        $form.easter_egg = easterEggOptions[0].value;
    }

    $: if (!hasAudience) {
        $form.targets = [];
    }

    $: if ($form.type === "scheduled") {
        $form.targets = [];
    }

    $: if (hasSource && availableSources.length > 0 && !availableSources.some((item) => item.value === $form.source)) {
        $form.source = availableSources[0].value;
    }

    $: if (hasSource && availableTriggers.length > 0 && !availableTriggers.some((item) => item.value === $form.trigger)) {
        $form.trigger = availableTriggers[0].value;
    }

    function submit() {
        const url = badgeSelected
            ? `/panel/administration/badge/${badgeSelected.uuid}`
            : "/panel/administration/badge";

        $form.post(url, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => close(),
        });
    }
</script>

<form on:submit|preventDefault={submit}>
    <FormField
        for="image"
        label="Ícone"
        help="Envie um PNG, JPG ou WebP de até 1 MB."
        error={$form.errors.image}
        spacing="compact"
    >
        <Preview
            name="image"
            size="icon"
            tone="muted"
            color="muted"
            src={badgeSelected?.image}
            oninput={(event) => ($form.image = event.target.files[0])}
            error={$form.errors.image}
        />
    </FormField>

    <FormField for="name" label="Nome" error={$form.errors.name} spacing="compact">
        <TextInput
            variant="offcanvas"
            type="text"
            name="name"
            id="name"
            bind:value={$form.name}
            error={$form.errors.name}
            required
        />
    </FormField>

    <FormField for="type" label="Categoria" error={$form.errors.type} spacing="compact">
        <SelectInput
            variant="offcanvas"
            name="type"
            id="type"
            bind:value={$form.type}
            error={$form.errors.type}
            required
        >
            <option value="fixed">Concedido</option>
            <option value="stealable">Competitivo</option>
            <option value="scheduled">Evento</option>
            <option value="achievement">Conquista</option>
        </SelectInput>
    </FormField>


    {#if $form.type === "fixed"}
        <FormField for="targets" label="Usuários selecionados" help="Opcional. Sem seleção, ninguém recebe este emblema agora." error={$form.errors.targets} spacing="compact">
            <SelectInput
                variant="offcanvas"
                name="targets"
                id="targets"
                class="min-h-56 py-2"
                bind:value={$form.targets}
                error={$form.errors.targets}
                multiple
            >
                {#each badgeTargets ?? [] as target (`${target.type}:${target.uuid}`)}
                    <option value={`${target.type}:${target.uuid}`}>{target.label} - {target.detail}</option>
                {/each}
            </SelectInput>
        </FormField>
    {/if}

    {#if $form.type === "scheduled"}
        <FormField
            for="starts_at"
            label="Início"
            help="Data e hora inicial para o usuário estar online para ganhar o emblema"
            error={$form.errors.starts_at}
            spacing="compact"
        >
            <TextInput
                variant="offcanvas"
                type="datetime-local"
                name="starts_at"
                id="starts_at"
                bind:value={$form.starts_at}
                error={$form.errors.starts_at}
                required
            />
        </FormField>

        <FormField
            for="daily_starts_at"
            label="Todo dia de"
            help="Opcional. Restringe a elegibilidade a uma janela diária dentro do período do evento."
            error={$form.errors.daily_starts_at}
            spacing="compact"
        >
            <TextInput
                variant="offcanvas"
                type="time"
                name="daily_starts_at"
                id="daily_starts_at"
                bind:value={$form.daily_starts_at}
                error={$form.errors.daily_starts_at}
            />
        </FormField>

        <FormField
            for="daily_ends_at"
            label="Todo dia até"
            error={$form.errors.daily_ends_at}
            spacing="compact"
        >
            <TextInput
                variant="offcanvas"
                type="time"
                name="daily_ends_at"
                id="daily_ends_at"
                bind:value={$form.daily_ends_at}
                error={$form.errors.daily_ends_at}
            />
        </FormField>

        <FormField
            for="ends_at"
            label="Fim"
            help="Data e hora limite para o usuário estar online para ganhar o emblema."
            error={$form.errors.ends_at}
            spacing="compact"
        >
            <TextInput
                variant="offcanvas"
                type="datetime-local"
                name="ends_at"
                id="ends_at"
                bind:value={$form.ends_at}
                error={$form.errors.ends_at}
                required
            />
        </FormField>
    {/if}

    {#if hasSource}
        <FormField
            for="source"
            label="Origem"
            help={$form.type === "stealable" ? "Módulo que transfere o emblema competitivo." : "Módulo que desbloqueia esta conquista."}
            error={$form.errors.source}
            spacing="compact"
        >
            <SelectInput
                variant="offcanvas"
                name="source"
                id="source"
                bind:value={$form.source}
                error={$form.errors.source}
            >
                {#each availableSources as option}
                    <option value={option.value}>{option.label}</option>
                {/each}
            </SelectInput>
        </FormField>

        {#if availableTriggers.length > 1}
            <FormField
                for="trigger"
                label="Gatilho"
                help={$form.type === "stealable" ? "Critério que define quem fica com esse emblema competitivo." : "Meta que desbloqueia esta conquista."}
                error={$form.errors.trigger}
                spacing="compact"
            >
                <SelectInput
                    variant="offcanvas"
                    name="trigger"
                    id="trigger"
                    bind:value={$form.trigger}
                    error={$form.errors.trigger}
                    required
                >
                    {#each availableTriggers as option}
                        <option value={option.value}>{option.label}</option>
                    {/each}
                </SelectInput>
            </FormField>
        {:else if availableTriggers.length === 1}
            <div class="mb-5 rounded-md bg-blue-ocean/10 p-3 text-sm text-blue-marinho">
                <span class="block font-noto-sans font-black uppercase italic text-orange-amber">Gatilho</span>
                <span>{availableTriggers[0].label}</span>
            </div>
        {/if}

        {#if needsThreshold}
            <FormField
                for="threshold"
                label="Meta"
                help="Número necessário para desbloquear a conquista."
                error={$form.errors.threshold}
                spacing="compact"
            >
                <TextInput
                    variant="offcanvas"
                    type="number"
                    name="threshold"
                    id="threshold"
                    min="1"
                    bind:value={$form.threshold}
                    error={$form.errors.threshold}
                    required
                />
            </FormField>
        {/if}

        {#if $form.trigger === "easter_egg"}
            <FormField
                for="easter_egg"
                label="Easter Egg"
                error={$form.errors.easter_egg}
                spacing="compact"
            >
                <SelectInput
                    variant="offcanvas"
                    name="easter_egg"
                    id="easter_egg"
                    bind:value={$form.easter_egg}
                    error={$form.errors.easter_egg}
                    required
                >
                    {#each easterEggOptions as option}
                        <option value={option.value}>{option.label}</option>
                    {/each}
                </SelectInput>
            </FormField>
        {/if}
    {/if}

    <div class="mb-5">
        <CheckboxInput
            id="is_active"
            label="Emblema ativo"
            bind:checked={$form.is_active}
        />
    </div>

    {#if badgeSelected ? can.update : can.create}
        <div class="mt-5 pt-2">
            <Button
                type="submit"
                loading={$form.processing}
                variant="secondary"
                shape="pill"
            >
                {badgeSelected ? "Atualizar" : "Cadastrar"}
            </Button>
        </div>
    {/if}
</form>
