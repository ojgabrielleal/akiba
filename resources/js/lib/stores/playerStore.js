import { get, writable } from "svelte/store";

const DEFAULT_VOLUME = 0.03;
const MIN_PLAY_LOADING_MS = 500;
const VOLUME_STORAGE_KEY = "akiba-player-volume";

const clampVolume = (volume) => Math.min(1, Math.max(0, Number(volume)));

const readStoredVolume = () => {
    if (typeof localStorage === "undefined") return DEFAULT_VOLUME;

    try {
        const storedValue = localStorage.getItem(VOLUME_STORAGE_KEY);
        if (storedValue === null) return DEFAULT_VOLUME;

        const storedVolume = Number(storedValue);
        return Number.isFinite(storedVolume) ? clampVolume(storedVolume) : DEFAULT_VOLUME;
    } catch {
        return DEFAULT_VOLUME;
    }
};

const initialState = {
    playing: false,
    loading: false,
    volume: readStoredVolume(),
    muted: false,
    error: null,
};

export const player = writable(initialState);

let audio;
let listenersAttached = false;
let playLoadingTimeout;
let playLoadingStartedAt = 0;

const clearPlayLoadingTimeout = () => {
    if (!playLoadingTimeout) return;

    clearTimeout(playLoadingTimeout);
    playLoadingTimeout = undefined;
};

const startPlayLoading = () => {
    clearPlayLoadingTimeout();
    playLoadingStartedAt = Date.now();
    player.update((state) => ({ ...state, loading: true, error: null }));
};

const finishPlayLoading = () => {
    const remainingTime = Math.max(0, MIN_PLAY_LOADING_MS - (Date.now() - playLoadingStartedAt));

    clearPlayLoadingTimeout();

    playLoadingTimeout = setTimeout(() => {
        player.update((state) => ({ ...state, loading: false }));
        playLoadingTimeout = undefined;
    }, remainingTime);
};

const getAudio = () => {
    if (typeof Audio === "undefined") return null;

    if (!audio) {
        audio = new Audio("/api/stream");
        audio.preload = "none";
    }

    if (audio && !listenersAttached) {
        audio.addEventListener("pause", handlePause);
        audio.addEventListener("waiting", handleWaiting);
        audio.addEventListener("playing", handlePlaying);
        audio.addEventListener("error", handleError);
        listenersAttached = true;

        const volume = readStoredVolume();
        audio.volume = volume;
        player.update((state) => ({ ...state, volume }));
    }

    return audio;
};

const handlePause = () => {
    clearPlayLoadingTimeout();
    player.update((state) => ({ ...state, playing: false, loading: false }));
};

const handleWaiting = () => {
    player.update((state) => ({ ...state, loading: true }));
};

const handlePlaying = () => {
    player.update((state) => ({ ...state, playing: true, error: null }));
    finishPlayLoading();
};

const handleError = () => {
    clearPlayLoadingTimeout();
    player.update((state) => ({
        ...state,
        playing: false,
        loading: false,
        error: "Não foi possível reproduzir a rádio.",
    }));
};

const setupMediaSession = () => {
    if (typeof navigator === "undefined" || !("mediaSession" in navigator)) {
        return;
    }

    navigator.mediaSession.setActionHandler("pause", pauseAudio);
    navigator.mediaSession.setActionHandler("play", playAudio);
};

export const syncMediaSessionMetadata = (air, stream) => {
    if (typeof navigator === "undefined" || !("mediaSession" in navigator) || typeof MediaMetadata === "undefined" || !air || !stream) {
        return;
    }

    const cover = stream.current_song?.cover;
    navigator.mediaSession.metadata = new MediaMetadata({
        title: stream.current_song?.music || "Rede Akiba",
        artist: [air.program?.name, air.program?.host?.nickname].filter(Boolean).join(" - "),
        album: "Rede Akiba - O Paraíso dos Otakus",
        artwork: cover ? [{ src: cover, sizes: "192x192" }, { src: cover, sizes: "512x512" }] : [],
    });
};

export const playAudio = async () => {
    const element = getAudio();
    if (!element) return;

    startPlayLoading();

    try {
        await element.play();
        setupMediaSession();
    } catch {
        clearPlayLoadingTimeout();
        player.update((state) => ({
            ...state,
            playing: false,
            loading: false,
            error: "O navegador bloqueou ou não conseguiu iniciar a rádio.",
        }));
    }
};

export const pauseAudio = () => {
    getAudio()?.pause();
};

export const toggleAudio = () => {
    return get(player).playing ? pauseAudio() : playAudio();
};

export const setVolume = (volume) => {
    const normalizedVolume = clampVolume(volume);
    const element = getAudio();

    if (element) {
        element.volume = normalizedVolume;
        element.muted = false;
    }

    if (typeof localStorage !== "undefined") {
        localStorage.setItem(VOLUME_STORAGE_KEY, String());
    }

    player.update((state) => ({
        ...state,
        volume: normalizedVolume,
        muted: false,
    }));
};

export const toggleMute = () => {
    const element = getAudio();

    if (!element) {
        return;
    }

    element.muted = !element.muted;
    player.update((state) => ({ ...state, muted: element.muted }));
};

export const destroyPlayer = () => {
    clearPlayLoadingTimeout();

    if (audio && listenersAttached) {
        audio.removeEventListener("pause", handlePause);
        audio.removeEventListener("waiting", handleWaiting);
        audio.removeEventListener("playing", handlePlaying);
        audio.removeEventListener("error", handleError);
        listenersAttached = false;
    }

    audio?.pause();
    audio?.removeAttribute("src");
    audio?.load();
    audio = undefined;
};
