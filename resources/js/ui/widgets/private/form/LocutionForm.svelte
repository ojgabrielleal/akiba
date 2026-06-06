<script>
    import { useForm, page } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/private/";
    import { locutionPermissions } from "@/utils";
    import { locutionIcons, locutionTextures, locutionDecorations } from "@/data";

    $: ({ programs } = $page.props);

    let can = locutionPermissions();

    let form = useForm({
        program: null,
        phrase: {
            text: null,
            icon: locutionIcons[17].url,
            decoration: {
                left: locutionDecorations[0].left,
                right: locutionDecorations[0].right,
            },
            texture: locutionTextures[0].url,
        },
        
    });

    const submit = () => {
        $form.post(`/panel/locution/locution/start/${$form.program}`, {
            preserveScroll: true,
            onSuccess: () => {
                $form.reset();
            },
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <Section title="Meus Programas">
        {#if programs.data.length > 0}
            <div class="flex flex-wrap justify-center gap-15 lg:gap-x-0 lg:gap-y-15">
                {#each programs.data as item}
                    <button
                        type="button"
                        aria-label={item.name}
                        class="flex-none cursor-pointer lg:px-10 lg:border-r-2 lg:border-suspense-aurora/10 lg:last:border-0"
                        on:click={() => { $form.program = item.uuid; }}
                    >
                        <img
                            src={item.image}
                            alt=""
                            aria-hidden="true"
                            loading="lazy"
                            class={["w-60 transition duration-300 ease-in-out", 
                                { "opacity-100 scale-100 drop-shadow-[0_0_20px_rgba(0,255,200,0.3)]": $form.program === item.uuid }, 
                                { "opacity-50 scale-90": $form.program !== item.uuid }
                            ]}
                        />
                    </button>
                {/each}
            </div>
        {/if}
    </Section>
    <Section title="Personalização do player">
        <div class="w-full mt-15 rounded-sm hidden lg:flex justify-center bg-contain bg-right bg-no-repeat" style={`background-image: url('${$form.phrase.texture}'), var(--gradient-blue-ocean-cerulean);`}>  
            <div class="w-8/12 h-25 flex items-center relative">
                <img
                    src={$form.phrase.decoration?.left}
                    alt=""
                    aria-hidden="true"
                    class="w-23 absolute left-0"
                    loading="lazy"
                />
                <input 
                    type="text"
                    class="w-9/13 h-15 ml-23 bg-blue-ocean/50 border border-blue-skywave font-noto-sans font-extrabold italic uppercase text-xl text-orange-citric rounded-md outline-none pl-4"
                    placeholder="Digite a frase de locução"
                    bind:value={$form.phrase.text}
                >
                <img
                    src={$form.phrase.icon}
                    alt="Icone da locução"
                    class="w-35 h-35 absolute bottom-0 right-0 "
                />
                <img
                    src={$form.phrase.decoration?.right}
                    alt=""
                    aria-hidden="true"
                    class="w-23 absolute -right-10"
                    loading="lazy"
                />
            </div>
        </div>
        <input 
            type="text"
            class="w-full block lg:hidden h-15 bg-blue-ocean/50 border border-blue-skywave font-noto-sans font-extrabold italic uppercase text-xl text-orange-citric rounded-md outline-none pl-4"
            placeholder="Digite a frase de locução"
            bind:value={$form.phrase.text}
        >
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-10 mt-5 lg:mt-12">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2">
                {#each locutionDecorations as item}
                    <button
                        type="button"
                        class={["cursor-pointer h-15 border-2 border-blue-ocean rounded-md disabled:cursor-not-allowed disabled:opacity-50", 
                            { "border-blue-skywave drop-shadow-[0_0_20px_rgba(0,255,200,0.3)]": $form.phrase.decoration?.left === item.left && $form.phrase.decoration?.right === item.right },
                            { "border-blue-ocean": !($form.phrase.decoration?.left === item.left && $form.phrase.decoration?.right === item.right) }
                        ]}
                        disabled={item.disabled}
                        on:click={() => { $form.phrase.decoration = { left: item.left, right: item.right } }}
                    >
                        <img 
                            src={item.icon}
                            alt={item.alt}
                            aria-hidden="true"
                            class={["object-contain w-full h-full",
                                { "hidden": item.disabled },
                                { "initial": !item.disabled }
                            ]}
                        />
                    </button>
                {/each}
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                {#each locutionTextures as item}
                    <button
                        type="button"
                        class={["cursor-pointer h-15 border-2 border-blue-ocean rounded-md disabled:cursor-not-allowed disabled:opacity-50", 
                            { "border-blue-skywave drop-shadow-[0_0_20px_rgba(0,255,200,0.3)]": $form.phrase.texture === item.url },
                            { "border-blue-ocean": $form.phrase.texture !== item.url }
                        ]}
                        disabled={item.disabled}
                        on:click={() => { $form.phrase.texture = item.url}}
                    >
                        <img 
                            src={item.url}
                            alt={item.alt}
                            aria-hidden="true"
                            class={["object-cover w-full h-full",
                                { "hidden": item.disabled },
                                { "initial": !item.disabled }
                            ]}
                        />
                    </button>
                {/each}
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-9 gap-3 gap-y-18 mt-23">
            {#each locutionIcons as item, index}
                <button
                    type="button"
                    aria-label={`Icon ${index}`}
                    class={["cursor-pointer w-full h-7 flex justify-end items-end rounded-sm", 
                        { "bg-blue-skywave drop-shadow-[0_0_20px_rgba(0,255,200,0.3)]": $form.phrase.icon === item.url },
                        { "bg-blue-ocean": $form.phrase.icon !== item.url }
                    ]}
                    on:click={() => { $form.phrase.icon = item.url; }}
                >
                    <img
                        src={item.url}
                        alt=""
                        aria-hidden="true"
                        class="w-20 aspect-square"
                        loading="lazy"
                    />
                </button>
            {/each}
        </div>
        {#if can.start}
            <div class="flex justify-end mt-10">
                <button 
                    type="submit"
                    class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-blue-marinho py-2 px-6 rounded-full bg-orange-citric"
                >
                    Iniciar
                </button>
            </div>
        {/if}
    </Section>
</form>
