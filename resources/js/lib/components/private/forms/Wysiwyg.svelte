<script context="module">
    import Quill from "quill";
    import BlotFormatter from "@enzedonline/quill-blot-formatter2";
    import "quill/dist/quill.snow.css";
    import "@enzedonline/quill-blot-formatter2/dist/css/quill-blot-formatter2.css";
    import { createSafeEmbed, registerQuillExtensions } from "@/lib/utils/quillEmbeds";

    registerQuillExtensions(Quill);
    BlotFormatter.registerFormats(Quill);
    Quill.register("modules/blotFormatter2", BlotFormatter);
</script>

<script>
    import { onDestroy, onMount } from "svelte";

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
    let handlePaste;

    function syncValue() {
        value = quill.root.innerHTML;
        textarea.value = value === "<p><br></p>" ? "" : value;
    }

    function insertImage() {
        const input = document.createElement("input");
        input.type = "file";
        input.accept = "image/*";

        input.addEventListener("change", () => {
            const file = input.files?.[0];
            if (!file) return;

            const reader = new FileReader();
            const range = quill.getSelection(true);

            reader.addEventListener("load", () => {
                quill.insertEmbed(range.index, "image", reader.result, "user");
                quill.insertText(range.index + 1, "\n", "user");
                quill.setSelection(range.index + 2, 0, "silent");
                syncValue();
            }, { once: true });

            reader.readAsDataURL(file);
        }, { once: true });

        input.click();
    }

    function insertEmbed() {
        const input = window.prompt("Cole a URL do YouTube ou Spotify");
        const embed = createSafeEmbed(input ?? "");

        if (!embed) {
            if (input) window.alert("URL de embed não permitida.");
            return;
        }

        const range = quill.getSelection(true);
        quill.insertEmbed(range.index, "safeEmbed", embed, "user");
        quill.insertText(range.index + 1, "\n", "user");
        quill.setSelection(range.index + 2, 0, "silent");
    }

    onMount(() => {
        quill = new Quill(editor, {
            theme: "snow",
            modules: {
                blotFormatter2: {
                    video: {
                        registerCustomVideoBlot: true,
                        defaultAspectRatio: "16 / 9",
                    },
                    align: {
                        allowAligning: true,
                        alignments: ["left", "center", "right"],
                    },
                    resize: {
                        allowResizing: true,
                        useRelativeSize: false,
                    },
                    delete: {
                        allowKeyboardDelete: true,
                    },
                },
                toolbar: {
                    container: [
                        [{ size: [] }],
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
                    handlers: {
                        image: insertImage,
                        safeEmbed: insertEmbed,
                    },
                },
            },
            formats: [
                "size", "bold", "italic", "underline", "strike",
                "color", "background", "script", "header", "blockquote",
                "code-block", "list", "indent", "direction", "align",
                "link", "image", "video", "formula", "width", "height",
                "safeEmbed", "imageAlign", "iframeAlign",
            ],
        });

        handlePaste = (event) => {
            const text = event.clipboardData?.getData("text/plain");
            if (!text) return;

            event.preventDefault();
            event.stopImmediatePropagation();

            const range = quill.getSelection(true);
            if (range.length) quill.deleteText(range.index, range.length, "user");
            quill.insertText(range.index, text, "user");
            quill.setSelection(range.index + text.length, 0, "silent");
        };

        quill.root.addEventListener("paste", handlePaste, true);

        quill.root.classList.add("font-noto-sans");
        quill.root.style.fontFamily = '"Noto Sans", sans-serif';

        if (value) quill.clipboard.dangerouslyPasteHTML(value, "api");
        syncValue();

        quill.on("text-change", syncValue);
    });

    onDestroy(() => {
        if (!quill) return;

        quill.off("text-change", syncValue);
        if (handlePaste) {
            quill.root.removeEventListener("paste", handlePaste, true);
        }

        quill = null;
        handlePaste = null;
    });

    $: isDisabled = disabled;
    $: if (quill) quill.enable(!isDisabled);

    $: if (quill && value !== quill.root.innerHTML) {
        quill.clipboard.dangerouslyPasteHTML(value ?? "", "api");
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
