<script>
    import { onMount } from "svelte";
    import { usePoll } from "@inertiajs/svelte";
    import { Meta } from "@/config";
    import { MainPlayer, MobilePlayer } from "@/ui/widgets/public";

    usePoll(30 * 1000, {
        only: ["onair"],
    });

    onMount(() => {
        document.body.style.backgroundColor = "var(--color-blue-night)";
    });
</script>

<Meta />
<header>
    <div class="w-full flex overflow-hidden fixed top-0 z-10">
        <div class="flex shrink-0">
            <div class="bg-blue-ocean py-1 px-10 [clip-path:polygon(0_0,100%_0,calc(100%-1.25rem)_100%,0_100%)] text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
                Em Reforma
            </div>
            <div class="w-40 bg-orange-amber py-1 px-4 -ml-5 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)]"></div>
        </div>
        {#each Array(15) as _, index}
            <div class="flex -ml-5 shrink-0">
                <div class="bg-blue-ocean py-1 px-10 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)] text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
                    Em Reforma
                </div>
                <div class="w-40 bg-orange-amber py-1 px-4 -ml-5 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)]"></div>
            </div>
        {/each}
    </div>
</header>

<main class="flex flex-col justify-center items-center gap-5 lg:gap-10 h-screen">
    <div class="container-page mt-25">
        <div class="w-full flex justify-center">
            <img
                src="/img/brand/logo.webp"
                alt="Logo"
                class="w-80 md:w-100 lg:w-110"
                loading="lazy"
            />
        </div>
    </div>

    <div class="hidden lg:block w-full">
        <MainPlayer />
    </div>
    <div class="lg:hidden w-full pb-10">
        <MobilePlayer />
    </div>
</main>

<footer>
    <div class="w-full flex overflow-hidden fixed bottom-0 z-10">
        <div class="flex shrink-0">
            <div class="bg-blue-ocean py-1 px-10 [clip-path:polygon(0_0,100%_0,calc(100%-1.25rem)_100%,0_100%)] text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
                Em Reforma
            </div>
            <div class="w-40 bg-orange-amber py-1 px-4 -ml-5 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)]"></div>
        </div>
        {#each Array(15) as _, index}
            <div class="flex -ml-5 shrink-0">
                <div class="bg-blue-ocean py-1 px-10 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)] text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
                    Em Reforma
                </div>
                <div class="w-40 bg-orange-amber py-1 px-4 -ml-5 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)]"></div>
            </div>
        {/each}
    </div>
</footer>
