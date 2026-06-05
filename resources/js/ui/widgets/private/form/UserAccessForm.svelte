<script>
    export let close = () => {};
    export let identifier;

    import { useForm, page } from "@inertiajs/svelte";
    import axios from "axios";
    import { userPermissions } from "@/utils";

    $: ({ roles } = $page.props);

    let can = userPermissions().access;

    let form = useForm({
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
    <div class="mb-4">
        <label for="password" class="text-md text-gray-700 font-noto-sans block mb-1">
            Nova senha
        </label>
        <input
            id="password"
            type="password"
            name="password"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            placeholder="∗∗∗∗∗∗∗∗∗∗∗∗∗∗∗"
            bind:value={$form.password}
        />
        <div class="text-sm font-noto-sans text-gray-400 mt-1">
            Essa senha será criptografada para proteção
        </div>
    </div>
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
                    <div class="flex gap-2">
                        <input
                            type="checkbox"
                            name={item.name}
                            id={item.name}
                            value={item.name}
                            bind:group={$form.roles}
                        />
                        <label for={item.name}>
                            <div class="text-sm font-noto-sans font-semibold">
                                {item.label}
                            </div>
                            <div class="text-xs text-gray-400 font-noto-sans line-clamp-2">
                                {item.description}
                            </div>
                        </label>
                    </div>
                {/each}
            {/if}
        </div>
    </div>
    {#if can.update}
        <button type="submit" class="cursor-pointer bg-blue-skywave px-8 py-2 rounded-md text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
            Atualizar
        </button>
    {/if}
</form>
