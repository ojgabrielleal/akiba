const PROVIDERS = [
    {
        name: "youtube",
        origins: ["youtube.com", "www.youtube.com", "m.youtube.com", "youtu.be"],
        build(url) {
            let id = null;

            if (url.hostname === "youtu.be") {
                id = url.pathname.split("/").filter(Boolean)[0];
            } else if (url.pathname.startsWith("/watch")) {
                id = url.searchParams.get("v");
            } else if (url.pathname.startsWith("/shorts/") || url.pathname.startsWith("/embed/")) {
                id = url.pathname.split("/").filter(Boolean)[1];
            }

            if (!/^[a-zA-Z0-9_-]{6,}$/.test(id ?? "")) return null;

            return {
                provider: "youtube",
                src: `https://www.youtube-nocookie.com/embed/${id}`,
                url: `https://www.youtube.com/watch?v=${id}`,
                title: "YouTube",
                allow: "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share",
            };
        },
    },
    {
        name: "spotify",
        origins: ["open.spotify.com"],
        build(url) {
            const parts = url.pathname.split("/").filter(Boolean);
            const type = parts[0];
            const id = parts[1];

            if (!["episode", "show", "track", "album", "playlist"].includes(type) || !/^[a-zA-Z0-9]+$/.test(id ?? "")) {
                return null;
            }

            return {
                provider: "spotify",
                src: `https://open.spotify.com/embed/${type}/${id}`,
                url: `https://open.spotify.com/${type}/${id}`,
                title: "Spotify",
                allow: "autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture",
            };
        },
    },
];

const normalizeHostname = (hostname) => hostname.toLowerCase().replace(/^www\./, "");

export function createSafeEmbed(input) {
    let url;

    try {
        url = new URL(input);
    } catch {
        return null;
    }

    if (!["https:"].includes(url.protocol)) return null;

    const provider = PROVIDERS.find((item) => item.origins
        .map(normalizeHostname)
        .includes(normalizeHostname(url.hostname)));

    return provider?.build(url) ?? null;
}

export function registerQuillExtensions(Quill) {
    const BaseImage = Quill.import("formats/image");
    const BlockEmbed = Quill.import("blots/block/embed");

    class ResponsiveImage extends BaseImage {
        static formats(domNode) {
            const formats = super.formats(domNode) || {};
            const width = domNode.getAttribute("width") || domNode.style.width;
            const align = domNode.dataset.align;

            if (width) formats.width = width;
            if (align) formats.align = align;

            return formats;
        }

        format(name, value) {
            if (name === "width") {
                if (value) {
                    this.domNode.setAttribute("width", value);
                    this.domNode.style.width = value;
                } else {
                    this.domNode.removeAttribute("width");
                    this.domNode.style.width = "";
                }
                return;
            }

            if (name === "align") {
                this.domNode.dataset.align = value || "";
                this.domNode.classList.remove("ql-image-align-left", "ql-image-align-center", "ql-image-align-right");
                if (value) this.domNode.classList.add(`ql-image-align-${value}`);
                return;
            }

            super.format(name, value);
        }
    }

    ResponsiveImage.blotName = "image";
    ResponsiveImage.tagName = "IMG";

    class SafeEmbed extends BlockEmbed {
        static create(value) {
            const data = typeof value === "string" ? createSafeEmbed(value) : value;
            if (!data?.provider || !data?.src) throw new Error("Invalid embed provider");

            const node = super.create();

            node.dataset.provider = data.provider;
            node.dataset.url = data.url;
            node.setAttribute("contenteditable", "false");

            const iframe = document.createElement("iframe");
            iframe.src = data.src;
            iframe.title = data.title;
            iframe.loading = "lazy";
            iframe.allow = data.allow;
            iframe.referrerPolicy = "strict-origin-when-cross-origin";
            iframe.sandbox = "allow-scripts allow-same-origin allow-presentation allow-popups";
            iframe.allowFullscreen = true;

            node.appendChild(iframe);

            return node;
        }

        static value(node) {
            const iframe = node.querySelector("iframe");

            return {
                provider: node.dataset.provider,
                url: node.dataset.url,
                src: iframe?.getAttribute("src"),
                title: iframe?.getAttribute("title"),
                allow: iframe?.getAttribute("allow"),
            };
        }
    }

    SafeEmbed.blotName = "safeEmbed";
    SafeEmbed.className = "ql-safe-embed";
    SafeEmbed.tagName = "DIV";

    Quill.register(ResponsiveImage, true);
    Quill.register(SafeEmbed, true);
}

