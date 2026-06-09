<script>
    import { Meta } from "@/config";
    import { FlashToaster } from "@/ui/components/private";
    import { LoginForm } from "@/ui/widgets/private";
</script>

<FlashToaster />
<Meta meta={{ title: "Realize o Login" } } />
<main class="w-screen h-screen flex justify-center items-center bg-blue-marinho p-5">
    <h1 class="sr-only">Login</h1>
    <section class="bg-suspense-aurora rounded-l-lg rounded-xl shadow-xl/30 xl:w-240 xl:h-120 xl:grid xl:grid-cols-[2fr_3fr]">
        <div class="flex items-center p-8">
            <LoginForm />
        </div>
        <div class="h-full overflow-hidden rounded-r-lg hidden xl:block">
            <video
                class="w-full h-full object-cover"
                src="/video/login/banner.webm"
                alt=""
                aria-hidden="true"
                autoplay
                playsinline
                loop
                muted
                preload="auto"
            >
            </video>
        </div>
    </section>
</main>
