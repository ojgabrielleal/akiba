import { get, writable } from "svelte/store";

const DEFAULT_VOLUME = 0.2;
const MIN_PLAY_LOADING_MS = 500;
const AUTOPLAY_RETRY_MS = 2500;
const AUTOPLAY_INTERACTION_EVENTS = ["pointerdown", "touchstart", "keydown", "click"];
const WAVE_BAR_COUNT = 140;
const DEFAULT_WAVE_LEVELS = Array.from({ length: WAVE_BAR_COUNT }, () => 0.2);
const MIN_WAVE_LEVEL = 0.02;
const MAX_WAVE_LEVEL = 1;
const FREQUENCY_GAIN = 4.65;
const WAVE_ATTACK = 0.88;
const WAVE_RELEASE = 0.18;
const BASS_END = 0.28;
const MID_END = 0.62;
const BOOM_ATTACK = 9.5;
const BOOM_THRESHOLD = 0.012;
const BOOM_DECAY = 0.9;
const ENERGY_FOLLOW = 0.02;
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
let autoplayRetryInterval;
let autoplayEnabled = false;
let autoplayAttempting = false;
let smoothedWaveLevels = [...DEFAULT_WAVE_LEVELS];
let previousEnergy = 0;
let boomPulse = 0;
let averageEnergy = 0.18;

const clearPlayLoadingTimeout = () => {
    if (!playLoadingTimeout) return;

    clearTimeout(playLoadingTimeout);
    playLoadingTimeout = undefined;
};

const stopAutoplay = () => {
    autoplayEnabled = false;
    autoplayAttempting = false;

    if (autoplayRetryInterval) {
        clearInterval(autoplayRetryInterval);
        autoplayRetryInterval = undefined;
    }

    if (typeof window === "undefined") return;

    AUTOPLAY_INTERACTION_EVENTS.forEach((eventName) => {
        window.removeEventListener(eventName, handleAutoplayInteraction, true);
    });
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
            analyser.fftSize = 512;
            analyser.smoothingTimeConstant = 0.64;
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

    smoothedWaveLevels = [...DEFAULT_WAVE_LEVELS];
    previousEnergy = 0;
    boomPulse = 0;
    averageEnergy = 0.18;
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

        const bassEndBin = Math.max(1, Math.floor(frequencyData.length * BASS_END));
        const midEndBin = Math.max(bassEndBin + 1, Math.floor(frequencyData.length * MID_END));
        const bassEnergy = averageFrequency(frequencyData.subarray(0, bassEndBin)) / 255;
        const midEnergy = averageFrequency(frequencyData.subarray(bassEndBin, midEndBin)) / 255;
        const trebleEnergy = averageFrequency(frequencyData.subarray(midEndBin)) / 255;
        const fullEnergy = averageFrequency(frequencyData) / 255;
        const currentEnergy = (bassEnergy * 0.84) + (fullEnergy * 0.16);
        averageEnergy = (averageEnergy * (1 - ENERGY_FOLLOW)) + (currentEnergy * ENERGY_FOLLOW);

        const relativeEnergy = currentEnergy / Math.max(0.04, averageEnergy);
        const relativePreviousEnergy = previousEnergy / Math.max(0.04, averageEnergy);
        const energyAttack = Math.max(0, relativeEnergy - relativePreviousEnergy);

        previousEnergy = (previousEnergy * 0.82) + (currentEnergy * 0.18);
        boomPulse = Math.max(boomPulse * BOOM_DECAY, energyAttack > BOOM_THRESHOLD ? Math.min(1, energyAttack * BOOM_ATTACK) : 0);

        const bassLevel = resolveRelativeLevel(bassEnergy, averageEnergy, 0.46, 1.45);
        const midLevel = resolveRelativeLevel(midEnergy, averageEnergy, 0.64, 0.82);
        const trebleLevel = resolveRelativeLevel(trebleEnergy, averageEnergy, 0.74, 0.48);
        const phase = performance.now() / 1000;
        const waveLevels = Array.from({ length: WAVE_BAR_COUNT }, (_, index) => {
            const position = index / Math.max(1, WAVE_BAR_COUNT - 1);
            const mirroredPosition = 1 - Math.abs((position * 2) - 1);
            const waveShape = resolveMusicalWaveShape(position, mirroredPosition, phase, bassLevel, midLevel, trebleLevel);
            const boomShape = Math.sin(mirroredPosition * Math.PI) ** 2.2;
            const boomLift = boomPulse * boomShape * 1.18;
            const target = Math.min(
                MAX_WAVE_LEVEL,
                (waveShape + boomLift) * FREQUENCY_GAIN,
            );
            const motion = target > smoothedWaveLevels[index] ? WAVE_ATTACK : WAVE_RELEASE;

            smoothedWaveLevels[index] =
                (smoothedWaveLevels[index] * (1 - motion)) + (target * motion);

            return Math.max(MIN_WAVE_LEVEL, Math.min(MAX_WAVE_LEVEL, smoothedWaveLevels[index]));
        });

        player.update((state) => ({ ...state, waveLevels }));
        waveFrame = requestAnimationFrame(readLevels);
    };

    if (!waveFrame) {
        readLevels();
    }
};

const averageFrequency = (bucket) =>
    bucket.reduce((sum, value) => sum + value, 0) / Math.max(1, bucket.length);

const resolveRelativeLevel = (energy, baseline, threshold, gain) =>
    Math.min(1, Math.max(0, (energy / Math.max(0.04, baseline)) - threshold) * gain);

const resolveMusicalWaveShape = (position, mirroredPosition, phase, bassLevel, midLevel, trebleLevel) => {
    const centerEnvelope = Math.sin(mirroredPosition * Math.PI) ** 0.95;
    const bassWave = smoothPulse(position, 1.75, phase * 0.38, 1.3) * bassLevel * 0.58;
    const midWave = smoothPulse(position, 3.35, phase * -0.52, 1.75) * midLevel * 0.3;
    const trebleWave = smoothPulse(position, 6.8, phase * 0.76, 2.2) * trebleLevel * 0.16;

    return (0.012 + bassWave + midWave + trebleWave) * centerEnvelope;
};

const smoothPulse = (position, frequency, phase, power) =>
    ((Math.sin(((position * frequency) + phase) * Math.PI * 2) + 1) / 2) ** power;

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
    stopAutoplay();
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

export const playAudio = async ({ silent = false } = {}) => {
    const element = getAudio();
    if (!element) return;

    if (!silent) {
        setVolume(DEFAULT_VOLUME);
        startPlayLoading();
    }

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
        if (!silent) {
            clearPlayLoadingTimeout();
        }

        stopWaveAnalysis();
        player.update((state) => ({
            ...state,
            playing: false,
            loading: false,
            error: silent ? state.error : "O navegador bloqueou ou não conseguiu iniciar a rádio.",
        }));
    }
};

export const pauseAudio = () => {
    stopAutoplay();
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

const retryAutoplay = async () => {
    if (!autoplayEnabled || autoplayAttempting || get(player).playing) {
        return;
    }

    setVolume(DEFAULT_VOLUME);
    autoplayAttempting = true;
    await playAudio({ silent: true });
    autoplayAttempting = false;
};

function handleAutoplayInteraction() {
    retryAutoplay();
}

export const startAutoplay = () => {
    if (typeof window === "undefined" || get(player).playing) {
        return stopAutoplay;
    }

    stopAutoplay();
    autoplayEnabled = true;
    retryAutoplay();
    autoplayRetryInterval = setInterval(retryAutoplay, AUTOPLAY_RETRY_MS);

    AUTOPLAY_INTERACTION_EVENTS.forEach((eventName) => {
        window.addEventListener(eventName, handleAutoplayInteraction, {
            capture: true,
            passive: true,
        });
    });

    return stopAutoplay;
};

export const destroyPlayer = () => {
    stopAutoplay();
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
