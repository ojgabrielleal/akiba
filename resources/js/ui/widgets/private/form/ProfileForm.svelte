<script>
    import { useForm, page } from "@inertiajs/svelte";
    import { Section, Preview } from "@/ui/components/private/";
    import { userPermissions } from "@/utils";
    import { userPreferences } from "@/data";

    $: ({ profile } = $page.props);

    let can = userPermissions();

    $: form = useForm({
        _method: "PATCH",
        name: null,
        nickname: null,
        gender: null,
        avatar: null,
        birthday: null,
        city: null,
        state: null,
        country: null,
        bibliography: null,
        socials: null,
        preferences: null,
    });

    $: if (profile) {
        $form.name = profile.data.name;
        $form.nickname = profile.data.nickname;
        $form.gender = profile.data.gender;
        $form.avatar = profile.data.avatar;
        $form.birthday = profile.data.birthday;
        $form.city = profile.data.city;
        $form.state = profile.data.state;
        $form.country = profile.data.country;
        $form.bibliography = profile.data.bibliography;
        $form.socials = profile.data.socials;
        $form.preferences = profile.data.preferences;
    }

    const submit = () => {
        $form.post(`/panel/profile/${profile.data.uuid}`, {
            preserveScroll: true,
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <Section title="O básico">
        <div class="grid grid-cols-1 xl:grid-cols-[15rem_1fr] gap-5 items-center">
            <div class="mb-3 relative">
                <Preview
                    name="image"
                    size="profile"
                    tone="muted"
                    position="top"
                    src={$form.avatar}
                    oninput={(event) => ($form.avatar = event.target.files[0])}
                    required={!profile}
                />
                <div class="mt-1 py-1 px-3 rounded-md bg-blue-skywave font-noto-sans font-extrabold italic uppercase text-xs text-suspense-aurora absolute bottom-2 left-2">
                    304 x 400
                </div>
            </div>
            <div>
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr_0.5fr_0.5fr] gap-5 mb-8">
                    <div>
                        <label for="name" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Nome completo
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                            bind:value={$form.name}
                            required
                        />
                    </div>
                    <div>
                        <label for="nickname" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Apelido
                        </label>
                        <input
                            id="nickname"
                            type="text"
                            name="nickname"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                            bind:value={$form.nickname}
                            required
                        />
                    </div>
                    <div>
                        <label for="gender" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Gênero
                        </label>
                        <select
                            id="gender"
                            name="gender"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                            bind:value={$form.gender}
                            required
                        >
                            <option value="male">
                                Masculino
                            </option>
                            <option value="female">
                                Feminino
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="birthday" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Nascimento
                        </label>
                        <input
                            id="birthday"
                            type="date"
                            name="birthday"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                            bind:value={$form.birthday}
                            required
                        />
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-8">
                    <div>
                        <label for="city" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Cidade
                        </label>
                        <input
                            id="city"
                            type="text"
                            name="city"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                            bind:value={$form.city}
                            required
                        />
                    </div>
                    <div>
                        <label for="state" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Estado
                        </label>
                        <input
                            id="state"
                            type="text"
                            name="state"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                            bind:value={$form.state}
                            required
                        />
                    </div>
                    <div>
                        <label for="country" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            País
                        </label>
                        <input
                            id="country"
                            type="text"
                            name="country"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                            defaultValue="Brasil"
                            bind:value={$form.country}
                            required
                        />
                    </div>
                </div>
            </div>
        </div>
    </Section>
    <Section title="Onde encontrar">
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-8">
                {#if $form.socials}
                    {#each $form.socials as item}
                        <div>
                            <label for={item.url} class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                                {item.name}
                            </label>
                            <input
                                id={item.name}
                                type="url"
                                name={item.name}
                                class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                                bind:value={item.url}
                            />
                        </div>
                    {/each}
                {/if}
            </div>
        </div>
    </Section>
    <Section title="Aprofundando">
        <div class="mb-8">
            <label for="bibliography" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                Biografia
            </label>
            <textarea
                id="bibliography"
                name="bibliography"
                rows="5"
                class="w-full bg-suspense-aurora font-noto-sans rounded-md outline-none p-4"
                bind:value={$form.bibliography}
                required
            ></textarea>
        </div>
        <div class="mb-8">
            <label for="likes" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                3 Gêneros de anime que você mais gosta
            </label>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {#each $form.preferences.likes as item}
                    <select
                        id="likes"
                        name="likes"
                        class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                        bind:value={item.content}
                    >
                        {#each userPreferences as item}
                            <option value={item.value}>
                                {item.name}
                            </option>
                        {/each}
                    </select>
                {/each}
            </div>
        </div>
        <div>
            <label for="unlikes" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                3 Gêneros de anime que você menos gosta
            </label>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {#each $form.preferences.unlikes as item}
                    <select
                        id="unlikes"
                        name="unlikes"
                        class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                        bind:value={item.content}
                    >
                        {#each userPreferences as item}
                            <option value={item.value}>
                                {item.name}
                            </option>
                        {/each}
                    </select>
                {/each}
            </div>
        </div>
    </Section>
    {#if can.update}
        <div class="flex justify-center mt-5 mb-8">
            <button
                type="submit"
                value="published"
                class="cursor-pointer w-full lg:w-auto py-2 px-6 border-4 border-solid border-blue-skywave rounded-xl text-blue-skywave text-xl font-extrabold font-noto-sans italic uppercase"
            >
                Atualizar
            </button>
        </div>
    {/if}
</form>
