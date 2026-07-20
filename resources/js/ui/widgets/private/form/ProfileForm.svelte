<script>
    import { useForm, page } from "@inertiajs/svelte";
    import {
        Button,
        FormField,
        Preview,
        Section,
        SelectInput,
        TextArea,
        TextInput,
    } from "@/ui/components/private/";
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
        birth_date: null,
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
        $form.birth_date = profile.data.birth_date;
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
            forceFormData: true,
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
                    <FormField for="name" label="Nome completo" labelVariant="editorial" spacing="none" error={$form.errors.name}>
                        <TextInput
                            id="name"
                            type="text"
                            name="name"
                            variant="profile"
                            bind:value={$form.name}
                            error={$form.errors.name}
                            required
                        />
                    </FormField>
                    <FormField for="nickname" label="Apelido" labelVariant="editorial" spacing="none" error={$form.errors.nickname}>
                        <TextInput
                            id="nickname"
                            type="text"
                            name="nickname"
                            variant="profile"
                            bind:value={$form.nickname}
                            error={$form.errors.nickname}
                            required
                        />
                    </FormField>
                    <FormField for="gender" label="Gênero" labelVariant="editorial" spacing="none" error={$form.errors.gender}>
                        <SelectInput
                            id="gender"
                            name="gender"
                            variant="profile"
                            bind:value={$form.gender}
                            error={$form.errors.gender}
                            required
                        >
                            <option value="male">
                                Masculino
                            </option>
                            <option value="female">
                                Feminino
                            </option>
                        </SelectInput>
                    </FormField>
                    <FormField for="birth_date" label="Nascimento" labelVariant="editorial" spacing="none" error={$form.errors.birth_date}>
                        <TextInput
                            id="birth_date"
                            type="date"
                            name="birth_date"
                            variant="profile"
                            bind:value={$form.birth_date}
                            error={$form.errors.birth_date}
                            required
                        />
                    </FormField>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-8">
                    <FormField for="city" label="Cidade" labelVariant="editorial" spacing="none" error={$form.errors.city}>
                        <TextInput
                            id="city"
                            type="text"
                            name="city"
                            variant="profile"
                            bind:value={$form.city}
                            error={$form.errors.city}
                            required
                        />
                    </FormField>
                    <FormField for="state" label="Estado" labelVariant="editorial" spacing="none" error={$form.errors.state}>
                        <TextInput
                            id="state"
                            type="text"
                            name="state"
                            variant="profile"
                            bind:value={$form.state}
                            error={$form.errors.state}
                            required
                        />
                    </FormField>
                    <FormField for="country" label="País" labelVariant="editorial" spacing="none" error={$form.errors.country}>
                        <TextInput
                            id="country"
                            type="text"
                            name="country"
                            variant="profile"
                            bind:value={$form.country}
                            error={$form.errors.country}
                            required
                        />
                    </FormField>
                </div>
            </div>
        </div>
    </Section>
    <Section title="Onde encontrar">
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-8">
                {#if $form.socials}
                    {#each $form.socials as item, index}
                        <FormField for={`social-${index}`} label={item.name} labelVariant="editorial" spacing="none">
                            <TextInput
                                id={`social-${index}`}
                                type="url"
                                name={`socials[${index}][url]`}
                                variant="profile"
                                bind:value={item.url}
                            />
                        </FormField>
                    {/each}
                {/if}
            </div>
        </div>
    </Section>
    <Section title="Aprofundando">
        <FormField for="bibliography" label="Biografia" labelVariant="editorial" spacing="lg" error={$form.errors.bibliography}>
            <TextArea
                id="bibliography"
                name="bibliography"
                rows="5"
                variant="profile"
                bind:value={$form.bibliography}
                error={$form.errors.bibliography}
                required
            />
        </FormField>
        <FormField for="likes-0" label="3 Gêneros de anime que você mais gosta" labelVariant="editorial" spacing="lg">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {#each $form.preferences.likes as item, index}
                    <SelectInput
                        id={`likes-${index}`}
                        name={`preferences[likes][${index}][content]`}
                        variant="profile"
                        bind:value={item.content}
                    >
                        {#each userPreferences as item}
                            <option value={item.value}>
                                {item.name}
                            </option>
                        {/each}
                    </SelectInput>
                {/each}
            </div>
        </FormField>
        <FormField for="unlikes-0" label="3 Gêneros de anime que você menos gosta" labelVariant="editorial" spacing="none">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {#each $form.preferences.unlikes as item, index}
                    <SelectInput
                        id={`unlikes-${index}`}
                        name={`preferences[unlikes][${index}][content]`}
                        variant="profile"
                        bind:value={item.content}
                    >
                        {#each userPreferences as item}
                            <option value={item.value}>
                                {item.name}
                            </option>
                        {/each}
                    </SelectInput>
                {/each}
            </div>
        </FormField>
    </Section>
    {#if can.update}
        <div class="flex justify-center mt-5 mb-8">
            <Button
                type="submit"
                value="published"
                variant="outline"
                size="lg"
                loading={$form.processing}
                class="w-full lg:w-auto"
            >
                Atualizar
            </Button>
        </div>
    {/if}
</form>
