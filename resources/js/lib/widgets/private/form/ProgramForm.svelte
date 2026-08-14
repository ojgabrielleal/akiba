<script>
    import { useForm } from "@inertiajs/svelte";

    import { locutionIcons } from "@/lib/constants";
    import {
        Button,
        CheckboxInput,
        FormField,
        Modal,
        Preview,
        RadioInput,
        SectionDivider,
        SelectInput,
        TextInput,
    } from "@/lib/components/private";
    import { programFormPermissions } from "@/lib/utils";

    export let close = () => {};
    export let programSelected;
    export let users = null;

    const can = programFormPermissions();
    let iconModalRef;
    let phraseIndex;

    $: form = useForm({
        _method: programSelected ? "PATCH" : "POST",
        user: programSelected?.host.uuid ?? null,
        name: programSelected?.name ?? null,
        image: programSelected?.image ?? null,
        access_type: programSelected?.access_type ?? null,
        execution_mode: programSelected?.execution_mode ?? null,
        is_default_auto_dj: programSelected?.is_default_auto_dj ?? false,
        airtimes: programSelected?.airtimes ?? [],
        schedules: programSelected?.schedules ?? [],
        phrases: programSelected?.phrases ?? [],
    });

    function submit() {
        let url = programSelected
            ? `/panel/radio/program/${programSelected.uuid}`
            : "/panel/radio/program";

        $form.post(url, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: (page) => {
                if (page.props.flash?.type !== "error") {
                    close();
                }
            },
        });
    }

    function addAirtime() {
        $form.airtimes = [
            ...$form.airtimes,
            { uuid: null, scheduled_at: null },
        ];
    }

    function removeAirtime(index) {
        $form.airtimes = $form.airtimes.filter((_, i) => i !== index);
    }

    function addSchedule() {
        $form.schedules = [
            ...$form.schedules,
            { uuid: null, name: null, price: null, benefits: [] },
        ];
    }

    function removeSchedule(index) {
        $form.schedules = $form.schedules.filter((_, i) => i !== index);
    }

    function addPhrase() {
        $form.phrases = [
            ...$form.phrases,
            { icon: null, text: null, decoration: null, texture: null },
        ];
    }

    function removePhrase(index) {
        $form.phrases = $form.phrases.filter((_, i) => i !== index);
    }
</script>

<Modal bind:this={iconModalRef} title="Selecionar ícone">
    <div slot="content" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        {#each locutionIcons as icon, index}
            <button
                type="button"
                class="cursor-pointer aspect-square rounded-md border p-2 bg-white  border-gray-300"
                aria-label={`Selecionar ${icon.alt}`}
                on:click={() => {
                    $form.phrases[phraseIndex].icon = icon.url;
                    iconModalRef.close();
                }}
            >
                <img
                    src={icon.url}
                    alt={icon.alt}
                    class="w-full h-full object-contain"
                    loading="lazy"
                />
            </button>
        {/each}
    </div>
</Modal>

<form on:submit|preventDefault={submit}>
    <FormField for="image" label="Imagem" error={$form.errors.image}>
        <Preview
            size="compact"
            tone="muted"
            color="muted"
            name="image"
            src={$form.image}
            oninput={(event) => ($form.image = event.target.files[0])}
            required={!programSelected}
            error={$form.errors.image}
        />
    </FormField>
    <FormField for="name" label="Programa" error={$form.errors.name}>
        <TextInput
            variant="offcanvas"
            id="name"
            type="text"
            name="name"
            bind:value={$form.name}
            error={$form.errors.name}
            required
        />
    </FormField>
    <FormField for="execution_mode" label="Formato de programa" error={$form.errors.execution_mode}>
        <SelectInput
            variant="offcanvas"
            id="execution_mode"
            name="execution_mode"
            bind:value={$form.execution_mode}
            error={$form.errors.execution_mode}
            on:change={(event) => {
                $form.access_type = null;
                $form.is_default_auto_dj = false;
                if(event.target.value !== "live" && event.target.value !== "auto_dj") $form.access_type = 'private';
            }}
            required
        >
            <option value={null} disabled>
                Selecione uma opção
            </option>
            <option value="live">
                Ao vivo
            </option>
            <option value="scheduled">
                Gravado
            </option>
            <option value="playlist">
                Playlist
            </option>
            <option value="auto_dj">
                Auto DJ
            </option>
        </SelectInput>
    </FormField>
    {#if $form.execution_mode === "auto_dj"}
        <div class="mb-4">
            <CheckboxInput
                id="is_default_auto_dj"
                name="is_default_auto_dj"
                label="Usar como Auto DJ padrão"
                bind:checked={$form.is_default_auto_dj}
            />
        </div>
    {/if}
    {#if $form.execution_mode === "live"}
        <div class="mb-4">
            <div class="text-md text-gray-700 font-noto-sans mb-2">
                Este programa estará disponível a todos?
            </div>
            <div class="mb-1">
                <RadioInput
                    id="open"
                    name="free"
                    value="free"
                    label="Sim"
                    bind:group={$form.access_type}
                    error={$form.errors.access_type}
                />
            </div>
            <div>
                <RadioInput
                    id="close"
                    name="private"
                    value="private"
                    label="Não"
                    bind:group={$form.access_type}
                    error={$form.errors.access_type}
                />
            </div>
        </div>
    {/if}
    {#if $form.access_type === "private" || $form.execution_mode === "auto_dj"}
        <FormField for="user" label="Locutor" error={$form.errors.user}>
            <SelectInput
                variant="offcanvas"
                id="user"
                name="user"
                bind:value={$form.user}
                error={$form.errors.user}
                required
            >
                <option value={null} disabled>
                    Selecione uma opção
                </option>
                {#each users.data as item}
                    <option value={item.uuid}>
                        {item.nickname}
                    </option>
                {/each}
            </SelectInput>
        </FormField>
    {/if}
    {#if $form.execution_mode !== "live"}
        <SectionDivider tone="ocean">Frases</SectionDivider>
        <button
            type="button"
            class="cursor-pointer mb-2 flex items-center gap-[0.2rem] text-blue-ocean text-md font-noto-sans"
            on:click={() => addPhrase()}
        >
            <img
                src="/svg/plus.svg"
                alt=""
                aria-hidden="true"
                class="w-5 filter-blue-ocean"
                loading="lazy"
            />
            Adicionar frase
        </button>
        {#if $form.phrases}
            {#each $form.phrases as phrase, index}
                <div class="mb-4 border border-gray-400 p-4 rounded-md">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="cursor-pointer w-16 h-16 rounded-md border border-gray-400 bg-white flex items-center justify-center overflow-hidden"
                            aria-label="Selecionar ícone"
                            on:click={() => {
                                phraseIndex = index;
                                iconModalRef.open();
                            }}
                        >
                            {#if phrase.icon}
                                <img
                                    src={phrase.icon}
                                    alt=""
                                    aria-hidden="true"
                                    class="w-full h-full object-contain"
                                    loading="lazy"
                                />
                            {:else}
                                <img
                                    src="/svg/plus.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-6 filter-blue-ocean"
                                    loading="lazy"
                                />
                            {/if}
                        </button>
                    <FormField for={`phrase-${index}`} label="Frase" spacing="sm" error={$form.errors[`phrases.${index}.phrase`] ?? $form.errors[`phrases.${index}.text`]}>
                        <TextInput
                            variant="offcanvas"
                            id={`phrase-${index}`}
                            name={`phrases[${index}][phrase]`}
                            bind:value={phrase.text}
                            error={$form.errors[`phrases.${index}.phrase`] ?? $form.errors[`phrases.${index}.text`]}
                            required
                        />
                    </FormField>
                    </div>
                    <button
                        type="button"
                        class="cursor-pointer mt-4 flex items-center gap-[0.2rem] text-blue-ocean text-md font-noto-sans"
                        on:click={() => removePhrase(index)}
                    >
                        <img
                            src="/svg/close.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-5 filter-blue-ocean"
                            loading="lazy"
                        />
                        Remover
                    </button>
                </div>
            {/each}
        {/if}
    {/if}
    {#if $form.execution_mode !== "live" && $form.execution_mode !== "auto_dj"}
        <SectionDivider tone="ocean">Agendamentos</SectionDivider>
        <button
            type="button"
            class="cursor-pointer mb-2 flex items-center gap-[0.2rem] text-blue-ocean text-md font-noto-sans"
            on:click={() => addSchedule()}
        >
            <img
                src="/svg/plus.svg"
                alt=""
                aria-hidden="true"
                class="w-5 filter-blue-ocean"
                loading="lazy"
            />
            Adicionar agendamento
        </button>
        {#if $form.schedules}
            {#each $form.schedules as schedule, index}
                <div class="mb-4 border border-gray-400 p-4 rounded-md">
                    <FormField for={`scheduled-at-${index}`} label="Agendado para" spacing="sm" error={$form.errors[`schedules.${index}.scheduled_at`]}>
                        <TextInput
                            variant="offcanvas"
                            id={`scheduled-at-${index}`}
                            type="datetime-local"
                            name={`schedules[${index}][scheduled_at]`}
                            bind:value={schedule.scheduled_at}
                            error={$form.errors[`schedules.${index}.scheduled_at`]}
                        />
                    </FormField>
                    <button
                        type="button"
                        class="cursor-pointer mt-4 flex items-center gap-[0.2rem] text-blue-ocean text-md font-noto-sans"
                        on:click={() => removeSchedule(index)}
                    >
                        <img
                            src="/svg/close.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-5 filter-blue-ocean"
                            loading="lazy"
                        />
                        Remover
                    </button>
                </div>
            {/each}
        {/if}
    {:else if $form.execution_mode === "live" && $form.access_type === "private"}
        <SectionDivider tone="ocean">Grade de programação</SectionDivider>
        <button
            type="button"
            class="cursor-pointer mb-2 flex items-center gap-[0.2rem] text-blue-ocean text-md font-noto-sans"
            on:click={() => addAirtime()}
        >
            <img
                src="/svg/plus.svg"
                alt=""
                aria-hidden="true"
                class="w-5 filter-blue-ocean"
                loading="lazy"
            />
            Adicionar horário
        </button>
        {#if $form.airtimes}
            {#each $form.airtimes as schedule, index}
                <div class="mb-4 border border-gray-400 p-4 rounded-md">
                    <FormField for={`day-${index}`} label="Dia da semana" spacing="sm" error={$form.errors[`airtimes.${index}.day`]}>
                        <SelectInput
                            variant="offcanvas"
                            id={`day-${index}`}
                            name={`airtimes[${index}][day]`}
                            bind:value={schedule.day}
                            error={$form.errors[`airtimes.${index}.day`]}
                        >
                            <option value={0}>
                                Domingo
                            </option>
                            <option value={1}>
                                Segunda
                            </option>
                            <option value={2}>
                                Terça
                            </option>
                            <option value={3}>
                                Quarta
                            </option>
                            <option value={4}>
                                Quinta
                            </option>
                            <option value={5}>
                                Sexta
                            </option>
                            <option value={6}>
                                Sábado
                            </option>
                        </SelectInput>
                    </FormField>
                    <FormField for={`hour-${index}`} label="Horário" spacing="sm" error={$form.errors[`airtimes.${index}.hour`]}>
                        <TextInput
                            variant="offcanvas"
                            id={`hour-${index}`}
                            type="time"
                            name={`airtimes[${index}][hour]`}
                            bind:value={schedule.hour}
                            error={$form.errors[`airtimes.${index}.hour`]}
                        />
                    </FormField>
                    <button
                        type="button"
                        class="cursor-pointer mt-4 flex items-center gap-[0.2rem] text-blue-ocean text-md font-noto-sans"
                        on:click={() => removeAirtime(index)}
                    >
                        <img
                            src="/svg/close.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-5 filter-blue-ocean"
                            loading="lazy"
                        />
                        Remover
                    </button>
                </div>
            {/each}
        {/if}
    {/if}
    {#if can.create || can.update}
        <Button
            type="submit"
            variant="secondary"
            shape="pill"
            loading={$form.processing}
        >
            {programSelected ? "Atualizar" : "Cadastrar"}
        </Button>
    {/if}
</form>
