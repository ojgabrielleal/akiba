
import { writable, get } from "svelte/store";
import axios from "axios";

export const player = writable({
    playing: false,
    volume: 0.03,
});

let audio = document.getElementById('audio');

const updateMetadata = async () => {
    try {
        const { data: response } = await axios.get('/api/stream/metadata');
        const info = response.data[0];

        if (info) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: info.current_song?.music,
                artist: info.program.name + " - " + info.host.name,
                album: "Rede Akiba - O Paraíso dos Otakus",
                artwork: [
                    { src: info.current_song?.cover, sizes: '192x192', type: 'image/png' },
                    { src: info.current_song?.cover, sizes: '512x512', type: 'image/png' }
                ]
            });
        }
    } catch (e) {
        console.error("Erro ao buscar metadados do streaming para o media session");
    }
}

const setupMediaSession = () => {
    navigator.mediaSession.setActionHandler('pause', () => toggleAudio());
    navigator.mediaSession.setActionHandler('play', () => toggleAudio());

    let interval;

    if (!interval) {
        updateMetadata();
        interval = setInterval(updateMetadata, 60 * 1000);
    }
}

export const toggleAudio = async () => {
    let isPlaying = get(player).playing;

    if (isPlaying) {
        player.update((state) => ({
            ...state,
            playing: false
        }));

        audio.pause();
    } else {
        player.update((state) => ({
            ...state,
            playing: true
        }));

        audio.volume = get(player).volume;
        await audio.play();

        if('mediaSession' in navigator){
            setupMediaSession();
        }
    }
}

export const setVolume = (volume) => {
    audio.volume = volume;

    player.update((state) => ({
        ...state,
        volume: volume
    }));
}
