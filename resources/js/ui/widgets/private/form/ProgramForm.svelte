<script>
    export let close = () => {};
    export let programSelected;

    import { onMount } from "svelte";
    import { useForm, page } from "@inertiajs/svelte";
    import { locutionIcons } from "@/data";
    import { Modal, Preview } from "@/ui/components/private";
    import { programFormPermissions } from "@/utils";
    
    $: ({ users } = $page.props);
    
    let can = programFormPermissions();
    let iconModalRef;
    let phraseIndex;

    let form = useForm({
        _method: "POST",
        user: null,
        name: null,
        image: null,
        access_type: null,
        execution_mode: null,
        is_default_auto_dj: false,
        airtimes: [],
        plans: [],
        phrases: [],
    });

    
    onMount(() => {
        if (programSelected) {
            $form._method = "PATCH";
            $form.user = programSelected.host.uuid;
            $form.name = programSelected.name;
            $form.image = programSelected.image;
            $form.access_type = programSelected.access_type;
            $form.execution_mode = programSelected.execution_mode;
            $form.is_default_auto_dj = programSelected.is_default_auto_dj;
            $form.airtimes = programSelected.airtimes;
            $form.plans = programSelected.plans;
            $form.phrases = programSelected.phrases ?? [];
        }
    });

    const submit = () => {
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
        };
        
        const addAirtime = () => {
            $form.airtimes = [
            ...$form.airtimes,
            { uuid: null, scheduled_at: null},
        ];
    };
    
    const removeAirtime = (index) => {
        $form.airtimes = $form.airtimes.filter((_, i) => i !== index);
    };
    
    const addPlan = () => {
        $form.plans = [
            ...$form.plans,
            { uuid: null, name: null, price: null, benefits: [] },
        ];
    }
    
    const removePlan = (index) => {
        $form.plans = $form.plans.filter((_, i) => i !== index);
    };
    
    const addPhrase = () => {
        $form.phrases = [
            ...$form.phrases,
            { icon: null, text: null, decoration: null, texture: null },
        ];
    };

    const removePhrase = (index) => {
        $form.phrases = $form.phrases.filter((_, i) => i !== index);
    };
</script>

<Modal bind:this={iconModalRef} title="Selecionar ícone">
    <div slot="content" class="grid grid-cols-3 gap-3">
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
    <div class="mb-4 px-5">
        <Preview
            size="compact"
            tone="muted"
            color="muted"
            name="image"
            src={$form.image}
            oninput={(event) => ($form.image = event.target.files[0])}
            required={!programSelected}
        />
    </div>
    <div class="mb-4">
        <label for="name" class="text-md text-gray-700 font-noto-sans block mb-1">
            Programa
        </label>
        <input
            id="name"
            type="text"
            name="name"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.name}
            required
        />
    </div>
    <div class="mb-4">
        <label for="execution_mode" class="text-md text-gray-700 font-noto-sans block mb-1">
            Formato de programa
        </label>
        <select
            id="execution_mode"
            name="execution_mode"
            class="w-full h-10 bg-white font-noto-sans rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.execution_mode}
            on:change={(event) => {
                $form.access_type = null;
                $form.is_default_auto_dj = false;
                if(event.target.value !== "live") $form.access_type = 'private';
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
        </select>
    </div>
    {#if $form.execution_mode === "auto_dj"}
        <div class="mb-4 flex items-center gap-2">
            <input
                id="is_default_auto_dj"
                type="checkbox"
                name="is_default_auto_dj"
                class="cursor-pointer w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                bind:checked={$form.is_default_auto_dj}
            />
            <label for="is_default_auto_dj" class="cursor-pointer text-md text-gray-700 font-noto-sans">
                Usar como Auto DJ padrão
            </label>
        </div>
    {/if}
    {#if $form.execution_mode === "live"}
        <div class="mb-4">
            <div class="text-md text-gray-700 font-noto-sans mb-2">
                Este programa estará disponível a todos?
            </div>
            <div class="flex items-center gap-2 mb-1">
                <input
                    id="open"
                    type="radio"
                    name="free"
                    value="free"
                    class="cursor-pointer w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    bind:group={$form.access_type}
                />
                <label for="free" class="cursor-pointer text-md text-gray-700 font-noto-sans">
                    Sim
                </label>
            </div>
            <div class="flex items-center gap-2">
                <input
                    id="close"
                    type="radio"
                    name="private"
                    value="private"
                    class="cursor-pointer w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    bind:group={$form.access_type}
                />
                <label for="private" class="cursor-pointer text-md text-gray-700 font-noto-sans">
                    Não
                </label>
            </div>
        </div>
    {/if}
    {#if $form.access_type === "private"}
        <div class="mb-4">
            <label for="user" class="text-md text-gray-700 font-noto-sans block mb-1">
                Locutor
            </label>
            <select
                id="user"
                name="user"
                class="w-full h-10 bg-white font-noto-sans rounded-md outline-none pl-4 border border-gray-400"
                bind:value={$form.user}
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
            </select>
        </div>
    {/if}
    {#if $form.execution_mode !== "live"}
        <div class="flex items-center justify-center w-full mt-8 mb-5">
            <div class="relative w-full">
                <div class="absolute left-0 w-30 h-[0.1rem] bg-blue-ocean rounded-full top-1/2 -translate-y-1/2"></div>
                <span class="absolute inset-0 flex items-center justify-center text-blue-ocean font-noto-sans font-extrabold uppercase italic">
                    Frases
                </span>
                <div class="absolute right-0 w-30 h-[0.1rem] bg-blue-ocean rounded-full top-1/2 -translate-y-1/2"></div>
            </div>
        </div>
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
                        <div class="mb-2">
                            <label for={`phrase-${index}`} class="text-md text-gray-700 font-noto-sans block mb-1">
                                Frase
                            </label>
                            <input
                                id={`phrase-${index}`}
                                name={`phrases[${index}][phrase]`}
                                class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
                                bind:value={phrase.text}
                                required
                            />
                        </div>
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
        <div class="flex items-center justify-center w-full mt-8 mb-5">
            <div class="relative w-full">
                <div class="absolute left-0 w-20 h-[0.1rem] bg-blue-ocean rounded-full top-1/2 -translate-y-1/2"></div>
                <span class="absolute inset-0 flex items-center justify-center text-blue-ocean font-noto-sans font-extrabold uppercase italic">
                    Agendamentos
                </span>
                <div class="absolute right-0 w-20 h-[0.1rem] bg-blue-ocean rounded-full top-1/2 -translate-y-1/2"></div>
            </div>
        </div>
        <button
            type="button"
            class="cursor-pointer mb-2 flex items-center gap-[0.2rem] text-blue-ocean text-md font-noto-sans"
            on:click={() => addPlan()}
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
        {#if $form.plans}
            {#each $form.plans as plan, index}
                <div class="mb-4 border border-gray-400 p-4 rounded-md">
                    <div class="mb-2">
                        <label for="scheduled_at" class="text-md text-gray-700 font-noto-sans block mb-1">
                            Agendado para
                        </label>
                        <input
                            id="scheduled_at"
                            type="datetime-local"
                            name="scheduled_at"
                            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
                            bind:value={plan.scheduled_at}
                        />
                    </div>
                    <button
                        type="button"
                        class="cursor-pointer mt-4 flex items-center gap-[0.2rem] text-blue-ocean text-md font-noto-sans"
                        on:click={() => removePlan(index)}
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
        <div class="flex items-center justify-center w-full mt-8 mb-5">
            <div class="relative w-full">
                <div class="absolute left-0 w-12 h-[0.1rem] bg-blue-ocean rounded-full top-1/2 -translate-y-1/2"></div>
                <span class="absolute inset-0 flex items-center justify-center text-blue-ocean font-noto-sans font-extrabold uppercase italic">
                    Grade de programação
                </span>
                <div class="absolute right-0 w-12 h-[0.1rem] bg-blue-ocean rounded-full top-1/2 -translate-y-1/2"></div>
            </div>
        </div>
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
                    <div class="mb-2">
                        <label for="day" class="text-md text-gray-700 font-noto-sans block mb-1">
                            Dia da semana
                        </label>
                        <select
                            id="day"
                            name="day"
                            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
                            bind:value={schedule.day}
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
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="hour" class="text-md text-gray-700 font-noto-sans block mb-1">
                            Horário
                        </label>
                        <input
                            id="hour"
                            type="time"
                            name="hour"
                            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
                            bind:value={schedule.hour}
                        />
                    </div>
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
        <button
            type="submit"
            class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-suspense-aurora py-2 px-6 rounded-full bg-blue-ocean"
        >
            {programSelected ? "Atualizar" : "Cadastrar"}
        </button>
    {/if}
</form>
