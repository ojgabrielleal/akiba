<script>
    import { onMount } from "svelte";
    import Quill from "quill";
    import "quill/dist/quill.snow.css";

    export let height = "50rem";
    export let name = "content";
    export let id = name;
    export let value = null;
    export let required = false;
    export let disabled = false;
    export let error = null;

    let quill;
    let editor;
    let textarea;

    onMount(() => {
        quill = new Quill(editor, {
            theme: "snow",
            modules: {
                toolbar: [
                    [{ font: [] }, { size: [] }],
                    ["bold", "italic", "underline", "strike"],
                    [{ color: [] }, { background: [] }],
                    [{ script: "sub" }, { script: "super" }],
                    [{ header: 1 }, { header: 2 }, "blockquote", "code-block"],
                    [
                        { list: "ordered" },
                        { list: "bullet" },
                        { indent: "-1" },
                        { indent: "+1" },
                    ],
                    [{ direction: "rtl" }, { align: [] }],
                    ["link", "image", "video", "formula"],
                    ["clean"],
                ],
            },
        });

        quill.on("text-change", () => {
            value = quill.root.innerHTML;
            textarea.value = value === "<p><br></p>" ? "" : value;
        });
    });

    $: isDisabled = disabled;
    $: if (quill) quill.enable(!isDisabled);

    $: if (quill && value !== quill.root.innerHTML) {
        quill.root.innerHTML = value;
        textarea.value = value === "<p><br></p>" ? "" : value;
    }
</script>

<div class={["rounded-md overflow-hidden bg-blue-ocean", 
    error ? "private-field-error" : "",
    {'opacity-70 cursor-not-allowed': isDisabled}
]}>
    <div bind:this={editor} class="p-3" style="min-height: {height};"></div>
</div>
<textarea
    {id}
    {name}
    {required}
    class="sr-only"
    disabled={isDisabled}
    aria-invalid={error ? "true" : undefined}
    aria-describedby={error ? `${id}-error` : undefined}
    bind:this={textarea}
>
</textarea>

<style>
    :global(.private-field-error) {
        border: 2px solid var(--color-red-crimson) !important;
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-red-crimson) 25%, transparent) !important;
    }

    :global(.private-field-error) :global(.ql-toolbar),
    :global(.private-field-error) :global(.ql-container) {
        border-color: var(--color-red-crimson) !important;
    }
</style>
