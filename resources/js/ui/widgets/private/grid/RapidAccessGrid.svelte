<script>
    import { Link } from "@inertiajs/svelte";
    import { quickAccessPermissions } from "@/utils";

    let can = quickAccessPermissions();
</script>

<nav class="flex justify-center mb-10" aria-label="Acesso rapido">
    <ul class="flex flex-wrap justify-center gap-5">
        {#if can.post.create}
            <li>
                <Link
                    href="/post"
                    class="flex items-center gap-2 font-noto-sans font-extrabold italic uppercase text-blue-skywave text-lg pr-5 lg:first:pl-0 lg:border-r-2 lg:border-suspense-aurora/10 lg:last:border-0"
                >
                    <img
                        src="/svg/materials.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-blue-skywave"
                    />
                    Nova matéria
                </Link>
            </li>
        {/if}
        {#if can.activity.create}
            <li>
                <Link
                    href="/administration"
                    class="flex items-center gap-2 font-noto-sans font-extrabold italic uppercase text-blue-skywave text-lg pr-5 lg:first:pl-0 lg:border-r-2 lg:border-suspense-aurora/10 lg:last:border-0"
                >
                    <img
                        src="/svg/alerts.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-blue-skywave"
                    />
                    Adicionar aviso
                </Link>
            </li>
        {/if}
        {#if can.repository.create}
            <li>
                <Link
                    href="/marketing"
                    class="flex items-center gap-2 font-noto-sans font-extrabold italic uppercase text-blue-skywave text-lg pr-5 lg:first:pl-0 lg:border-r-2 lg:border-suspense-aurora/10 lg:last:border-0"
                >
                    <img
                        src="/svg/download.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-blue-skywave"
                    />
                    Upar arquivo
                </Link>
            </li>
        {/if}
        {#if can.locution.start}
            <li>
                <Link
                    href="/locution"
                    class="flex items-center gap-2 font-noto-sans font-extrabold italic uppercase text-blue-skywave text-lg pr-5 lg:first:pl-0 lg:border-r-2 lg:border-suspense-aurora/10 lg:last:border-0"
                >
                    <img
                        src="/svg/locution.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-blue-skywave"
                    />
                    Iniciar programa
                </Link>
            </li>
        {/if}
        {#if can.event.create}
            <li>
                <Link
                    href="/event"
                    class="flex items-center gap-2 font-noto-sans font-extrabold italic uppercase text-blue-skywave text-lg pr-5 lg:first:pl-0 lg:border-r-2 lg:border-suspense-aurora/10 lg:last:border-0"
                >
                    <img
                        src="/svg/events.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-blue-skywave"
                    />
                    Adicionar evento
                </Link>
            </li>
        {/if}
    </ul>
</nav>
