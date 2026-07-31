import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel) => ({
        authorize: (socketId, callback) => {
            window.axios.post('/broadcasting/auth', {
                socket_id: socketId,
                channel_name: channel.name,
            })
                .then((response) => callback(false, response.data))
                .catch((error) => callback(true, error));
        },
    }),
});
