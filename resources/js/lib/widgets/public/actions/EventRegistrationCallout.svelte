<script>
    import { Modal } from "@/lib/components/public";
    import EventRegistrationForm from "../form/EventRegistrationForm.svelte";

    let modalRef;
</script>

<Modal bind:this={modalRef} title="Informar evento" size="sm">
    <EventRegistrationForm close={() => modalRef.close()} />
</Modal>

<div class="mt-6 flex items-center justify-center gap-2 px-2 py-1 font-noto-sans not-italic uppercase">
    <p class="text-center text-xs font-normal leading-none tracking-normal text-orange-citric not-italic lg:text-sm">
        Algum evento otaku vai acontecer na sua região?
    </p>
    <button
        type="button"
        class="inline-flex min-h-7 shrink-0 cursor-pointer items-center justify-center rounded-md bg-orange-amber px-3 py-1 font-noto-sans text-sm font-black leading-none text-blue-night uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
        on:click={() => modalRef.open()}
    >
        Conta pra gente!
    </button>
</div>
