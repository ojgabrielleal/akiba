import { get, writable } from "svelte/store";

const DEFAULT_VOLUME = 0.03;
const MIN_PLAY_LOADING_MS = 500;
const WAVE_BAR_COUNT = 140;
const DEFAULT_WAVE_LEVELS = Array.from({ length: WAVE_BAR_COUNT }, () => 0.2);
const createFallbackWaveLevels = () =>
    Array.from({ length: WAVE_BAR_COUNT }, (_, index) => {
        const phase = (Date.now() / 180) + index * 0.85;

        return 0.18 + Math.abs(Math.sin(phase)) * 0.62;
    });

const clampVolume = (volume) => Math.min(1, Math.max(0, Number(volume)));

const initialState = {
    playing: false,
    loading: false,
    volume: DEFAULT_VOLUME,
    muted: false,
    error: null,
    waveLevels: DEFAULT_WAVE_LEVELS,
};

export const player = writable(initialState);

let audio;
let audioContext;
let audioSource;
let analyser;
let frequencyData;
let waveFrame;
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

const resolveAudioContext = () => {
    if (typeof window === "undefined") return null;

    const AudioContextConstructor = window.AudioContext || window.webkitAudioContext;

    return AudioContextConstructor ? new AudioContextConstructor() : null;
};

const ensureAudioAnalyser = () => {
    try {
        const element = getAudio();

        if (!element) return null;

        if (!audioContext) {
            audioContext = resolveAudioContext();
        }

        if (!audioContext) return null;

        if (!analyser) {
            analyser = audioContext.createAnalyser();
            analyser.fftSize = 128;
            analyser.smoothingTimeConstant = 0.78;
            frequencyData = new Uint8Array(analyser.frequencyBinCount);
        }

        if (!audioSource) {
            audioSource = audioContext.createMediaElementSource(element);
            audioSource.connect(analyser);
            analyser.connect(audioContext.destination);
        }

        return analyser;
    } catch {
        return null;
    }
};

const stopWaveAnalysis = () => {
    if (waveFrame) {
        cancelAnimationFrame(waveFrame);
        waveFrame = undefined;
    }

    player.update((state) => ({ ...state, waveLevels: DEFAULT_WAVE_LEVELS }));
};

const startWaveAnalysis = () => {
    const activeAnalyser = ensureAudioAnalyser();

    if (!activeAnalyser || !frequencyData) {
        startFallbackWave();
        return;
    }

    const readLevels = () => {
        activeAnalyser.getByteFrequencyData(frequencyData);
        const hasSignal = frequencyData.some((value) => value > 0);

        if (!hasSignal) {
            player.update((state) => ({ ...state, waveLevels: createFallbackWaveLevels() }));
            waveFrame = requestAnimationFrame(readLevels);
            return;
        }

        const bucketSize = Math.max(1, Math.floor(frequencyData.length / WAVE_BAR_COUNT));
        const waveLevels = Array.from({ length: WAVE_BAR_COUNT }, (_, index) => {
            const start = index * bucketSize;
            const end = Math.min(frequencyData.length, start + bucketSize);
            const bucket = frequencyData.slice(start, end);
            const average = bucket.reduce((sum, value) => sum + value, 0) / Math.max(1, bucket.length);

            return Math.max(0.12, Math.min(1, average / 180));
        });

        player.update((state) => ({ ...state, waveLevels }));
        waveFrame = requestAnimationFrame(readLevels);
    };

    if (!waveFrame) {
        readLevels();
    }
};

const startFallbackWave = () => {
    const animateFallback = () => {
        player.update((state) => ({ ...state, waveLevels: createFallbackWaveLevels() }));
        waveFrame = requestAnimationFrame(animateFallback);
    };

    if (!waveFrame) {
        animateFallback();
    }
};

const getAudio = () => {
    if (typeof Audio === "undefined") return null;

    if (!audio) {
        audio = new Audio();
        audio.crossOrigin = "anonymous";
        audio.src = "/api/stream";
        audio.preload = "none";
    }

    if (audio && !listenersAttached) {
        audio.addEventListener("pause", handlePause);
        audio.addEventListener("waiting", handleWaiting);
        audio.addEventListener("playing", handlePlaying);
        audio.addEventListener("error", handleError);
        listenersAttached = true;

        audio.volume = DEFAULT_VOLUME;
        player.update((state) => ({ ...state, volume: DEFAULT_VOLUME }));
    }

    return audio;
};

const handlePause = () => {
    clearPlayLoadingTimeout();
    stopWaveAnalysis();
    player.update((state) => ({ ...state, playing: false, loading: false }));
};

const handleWaiting = () => {
    player.update((state) => ({ ...state, loading: true }));
};

const handlePlaying = () => {
    player.update((state) => ({ ...state, playing: true, error: null }));
    startWaveAnalysis();
    finishPlayLoading();
};

const handleError = () => {
    clearPlayLoadingTimeout();
    stopWaveAnalysis();
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
        ensureAudioAnalyser();
        try {
            await audioContext?.resume();
        } catch {
            audioContext = undefined;
        }

        await element.play();
        setupMediaSession();
    } catch {
        clearPlayLoadingTimeout();
        stopWaveAnalysis();
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
    stopWaveAnalysis();

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
    audioContext?.close();
    audioContext = undefined;
    audioSource = undefined;
    analyser = undefined;
    frequencyData = undefined;
};
