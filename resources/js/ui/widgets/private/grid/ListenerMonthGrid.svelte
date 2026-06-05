<script>
    export let title;

    import { page } from "@inertiajs/svelte";
    import { Section, Offcanvas } from "@/ui/components/private";
    import { ListenerMonthForm } from "@/ui/widgets/private";
    import { listenerMonthPermissions } from "@/utils";

    $: ({ listenerMonth } = $page.props);

    let can = listenerMonthPermissions();

    let offcanvasRef;
</script>

<Offcanvas bind:this={offcanvasRef} title="Atualizar ouvinte do mês">
    <div slot="content" let:close>
        <ListenerMonthForm {close} />
    </div>
</Offcanvas>

{#if listenerMonth}
    <Section {title}>
        <article class="grid grid-cols-1 lg:grid-cols-2">
            <div class="grid grid-cols-2">
                <dl class="mb-8">
                    <dt class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans">
                        Nome:
                    </dt>
                    <dd class="block text-suspense-aurora font-noto-sans uppercase">
                        {listenerMonth.data.name}
                    </dd>
                </dl>
                <dl class="mb-8">
                    <dt class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block">
                        Mora em:
                    </dt>
                    <dd class="block text-suspense-aurora font-noto-sans uppercase">
                        {listenerMonth.data.address}
                    </dd>
                </dl>
                <dl class="mb-8">
                    <dt class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block">
                        Número de pedidos feitos:
                    </dt>
                    <dd class="block text-suspense-aurora font-noto-sans uppercase">
                        {listenerMonth.data.requests_total}
                    </dd>
                </dl>
                <dl class="mb-8">
                    <dt class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block">
                        Programa preferido
                    </dt>
                    <dd class="block text-suspense-aurora font-noto-sans uppercase">
                        {listenerMonth.data.favorite_program.name}
                    </dd>
                </dl>
            </div>
            <div class="flex gap-5 items-center justify-end">
                {#if listenerMonth.data.avatar}
                    <div>
                        <div class="text-orange-amber font-extrabold italic text-sm uppercase font-noto-sans block">
                            Imagem do ouvinte
                        </div>
                        <img
                            src={listenerMonth.data.avatar}
                            alt="Imagem do ouvinte"
                            class="w-36 h-36 bg-gray-600 rounded-md"
                        />
                    </div>
                {/if}
                {#if can.set}
                    <button type="button" class="cursor-pointer bg-blue-skywave px-4 py-2 rounded-md text-suspense-aurora font-noto-sans font-extrabold uppercase italic" on:click={() => { offcanvasRef.open(); }}>
                        Atualizar ouvinte
                    </button>
                {/if}
            </div>
        </article>
    </Section>
{/if}
