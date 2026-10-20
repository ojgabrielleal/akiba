import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";

const source = readFileSync(new URL("./adsense.js", import.meta.url), "utf8");
let moduleId = 0;

const setup = async (hostname = "akiba.com.br") => {
    const scripts = [];
    const observers = [];
    const originalGlobals = Object.fromEntries(["window", "document", "ResizeObserver"].map((key) => [key, Object.getOwnPropertyDescriptor(globalThis, key)]));
    const createScript = () => {
        const script = new EventTarget();
        script.dataset = {};
        script.remove = () => scripts.splice(scripts.indexOf(script), 1);
        return script;
    };
    globalThis.window = { location: { hostname }, adsbygoogle: [] };
    globalThis.document = {
        querySelector: () => scripts[0] ?? null,
        createElement: createScript,
        head: { appendChild: (script) => scripts.push(script) },
    };
    globalThis.ResizeObserver = class {
        constructor(callback) { this.callback = callback; this.disconnected = false; observers.push(this); }
        observe() {}
        disconnect() { this.disconnected = true; }
    };
    const module = await import(`data:text/javascript;base64,${Buffer.from(source).toString("base64")}#${moduleId++}`);
    const element = () => ({
        isConnected: true,
        offsetWidth: 498,
        dataset: { adClient: "ca-pub-8675050263618388", adSlot: "8597417495" },
        hasAttribute: () => false,
    });
    return {
        ...module, scripts, observers, element,
        restore() {
            for (const [key, descriptor] of Object.entries(originalGlobals)) {
                if (descriptor) Object.defineProperty(globalThis, key, descriptor);
                else delete globalThis[key];
            }
        },
    };
};

const scenario = (name, run) => test(name, async () => {
    const env = await setup();
    try { await run(env); } finally { env.restore(); }
});
const flush = () => new Promise((resolve) => setImmediate(resolve));

scenario("concurrent loaders share one script and wait for its load event", async (env) => {
    const first = env.loadGoogleAdsense();
    const second = env.loadGoogleAdsense();
    assert.equal(first, second);
    assert.equal(env.scripts.length, 1);
    let resolved = false;
    first.then(() => { resolved = true; });
    await flush();
    assert.equal(resolved, false);
    env.scripts[0].dispatchEvent(new Event("load"));
    assert.equal(await second, true);
});

scenario("failed script is removed and a later request can retry", async (env) => {
    const failed = env.loadGoogleAdsense();
    const rejection = assert.rejects(failed, /carregar o script/);
    env.scripts[0].dispatchEvent(new Event("error"));
    await rejection;
    assert.equal(env.scripts.length, 0);
    const retry = env.loadGoogleAdsense();
    assert.equal(env.scripts.length, 1);
    env.scripts[0].dispatchEvent(new Event("load"));
    assert.equal(await retry, true);
});

scenario("one push per element even with concurrent calls; new elements initialize independently", async (env) => {
    const element = env.element();
    const first = env.pushGoogleAdSlot(element);
    assert.equal(await env.pushGoogleAdSlot(element), false);
    env.scripts[0].dispatchEvent(new Event("load"));
    assert.equal(await first, true);
    assert.equal(await env.pushGoogleAdSlot(element), false);
    assert.equal(await env.pushGoogleAdSlot(env.element()), true);
    assert.equal(window.adsbygoogle.length, 2);
});

scenario("a Google-initialized element is not pushed again", async (env) => {
    const element = env.element();
    element.hasAttribute = (name) => name === "data-adsbygoogle-status";
    assert.equal(await env.pushGoogleAdSlot(element), false);
    assert.equal(env.scripts.length, 0);
});

scenario("detachment or lost width while loading prevents a push", async (env) => {
    const detached = env.element();
    const hidden = env.element();
    const first = env.pushGoogleAdSlot(detached);
    const second = env.pushGoogleAdSlot(hidden);
    detached.isConnected = false;
    hidden.offsetWidth = 0;
    env.scripts[0].dispatchEvent(new Event("load"));
    assert.deepEqual(await Promise.all([first, second]), [false, false]);
    assert.equal(window.adsbygoogle.length, 0);
});

scenario("action waits for mounting and width; destroy disconnects observer", async (env) => {
    const element = env.element();
    element.isConnected = false;
    element.offsetWidth = 0;
    const action = env.initializeGoogleAdSlot(element);
    await flush();
    assert.equal(env.scripts.length, 0);
    element.isConnected = true;
    element.offsetWidth = 498;
    env.observers[0].callback();
    env.scripts[0].dispatchEvent(new Event("load"));
    await flush();
    assert.equal(window.adsbygoogle.length, 1);
    action.destroy();
    assert.equal(env.observers[0].disconnected, true);
});

scenario("destroy during loading cancels only the old element, not the new page", async (env) => {
    const oldAction = env.initializeGoogleAdSlot(env.element());
    await flush();
    oldAction.destroy();
    const newAction = env.initializeGoogleAdSlot(env.element());
    await flush();
    env.scripts[0].dispatchEvent(new Event("load"));
    await flush();
    assert.equal(window.adsbygoogle.length, 1);
    assert.equal(env.scripts.length, 1);
    assert.equal(env.observers[0].disconnected, true);
    newAction.destroy();
});

scenario("destroy before mounting prevents any script or push", async (env) => {
    const action = env.initializeGoogleAdSlot(env.element());
    action.destroy();
    await flush();
    assert.equal(env.scripts.length, 0);
    assert.equal(window.adsbygoogle.length, 0);
});

scenario("unapproved hostnames do not load scripts or enqueue ads", async (env) => {
    window.location.hostname = "localhost";
    assert.equal(await env.loadGoogleAdsense(), false);
    assert.equal(await env.pushGoogleAdSlot(env.element()), false);
    const action = env.initializeGoogleAdSlot(env.element());
    await flush();
    assert.equal(env.scripts.length, 0);
    assert.equal(env.observers.length, 0);
    assert.equal(window.adsbygoogle.length, 0);
    action.destroy();
});
