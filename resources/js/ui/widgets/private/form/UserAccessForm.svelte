<script>
    export let close = () => {};
    export let identifier;

    import { useForm, page } from "@inertiajs/svelte";
    import axios from "axios";
    import { Button, CheckboxInput, FormField, TextInput } from "@/ui/components/private";
    import { userPermissions } from "@/utils";

    $: ({ roles } = $page.props);

    let can = userPermissions().access;

    $: form = useForm({
        password: null,
        roles: null,
    });

    if (identifier) {
        axios.get(`/panel/administration/user/${identifier}`)
            .then((response) => {
                const data = response.data.data;
                $form.roles = data.roles.map((role) => role.name);
            })
            .catch(() => {
                console.error("Error when find member selected");
                close();
            });
    }

    const submit = () => {
        $form.patch(`/panel/administration/user/${identifier}`, {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <FormField for="password" label="Nova senha" help="Essa senha será criptografada para proteção" error={$form.errors.password}>
        <TextInput
            variant="offcanvas"
            id="password"
            type="password"
            name="password"
            placeholder="∗∗∗∗∗∗∗∗∗∗∗∗∗∗∗"
            bind:value={$form.password}
            error={$form.errors.password}
        />
    </FormField>
    <div class="flex items-center justify-center w-full mt-8 mb-5">
        <div class="relative w-full">
            <div class="absolute left-0 w-1/3 h-[0.1rem] bg-blue-skywave rounded-full top-1/2 -translate-y-1/2"></div>
            <span class="absolute inset-0 flex items-center justify-center text-blue-skywave font-noto-sans font-extrabold uppercase italic">
                Cargos
            </span>
            <div class="absolute right-0 w-1/3 h-[0.1rem] bg-blue-skywave rounded-full top-1/2 -translate-y-1/2"></div>
        </div>
    </div>
    <div class="mb-4">
        <div class="flex flex-col gap-2">
            {#if roles}
                {#each roles.data as item}
                    <CheckboxInput
                        label={item.label}
                        description={item.description}
                        name={item.name}
                        id={item.name}
                        value={item.name}
                        bind:group={$form.roles}
                    />
                {/each}
            {/if}
        </div>
    </div>
    {#if can.update}
        <Button
            type="submit"
            size="lg"
        >
            Atualizar
        </Button>
    {/if}
</form>
