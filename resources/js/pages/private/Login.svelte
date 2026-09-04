<script>
    import { Meta } from "@/lib/components/shared";
    import { FlashToaster } from "@/lib/components/private";
    import { LoginForm } from "@/lib/widgets/private";
</script>

<FlashToaster />
<Meta meta={{ title: "Realize o Login" } } />
<main class="relative flex min-h-dvh w-full items-center justify-center overflow-hidden bg-blue-night px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="sr-only">Login</h1>
    <div
        class="absolute inset-0 opacity-25"
        aria-hidden="true"
        style="background-image: url('/img/textures/stars.webp'); background-size: 34rem auto;"
    ></div>
    <div
        class="absolute inset-x-0 bottom-0 h-1/2 bg-blue-marinho"
        aria-hidden="true"
    ></div>

    <section class="relative z-10 grid w-full max-w-4xl overflow-hidden rounded-md border border-blue-skywave/20 bg-suspense-aurora shadow-xl shadow-blue-night/45 lg:min-h-125 lg:grid-cols-[minmax(22rem,0.95fr)_minmax(0,1.05fr)]">
        <div class="flex items-center px-5 py-8 sm:px-8 lg:px-10">
            <LoginForm />
        </div>
        <div class="relative hidden min-h-full overflow-hidden bg-blue-marinho lg:block">
            <video
                class="h-full w-full object-cover"
                src="/video/login/banner.webm"
                aria-hidden="true"
                autoplay
                playsinline
                loop
                muted
                preload="auto"
            >
            </video>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-night/10 via-transparent to-blue-night/20" aria-hidden="true"></div>
        </div>
    </section>
</main>
