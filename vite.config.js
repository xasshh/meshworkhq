import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";

// When testing on a phone through an ngrok tunnel, set VITE_TUNNEL_HOST to the
// domain of a tunnel that forwards to THIS dev server (port 5173), e.g.:
//   ngrok http 5173
//   VITE_TUNNEL_HOST=cupbearer-lukewarm-gulf.ngrok-free.dev npm run dev
// Without the variable, normal localhost development is unaffected.
const tunnelHost = process.env.VITE_TUNNEL_HOST;

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/passkeys.js',
            ],
            refresh: true,
            fonts: [
                bunny('Outfit', { weights: [500, 600, 700] }),
                bunny('Manrope', { weights: [400, 500, 600, 700, 800] }),
                bunny('Martian Mono', { weights: [400, 500, 600] }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        host: tunnelHost ? '0.0.0.0' : undefined,
        allowedHosts: ['.ngrok-free.dev', '.ngrok-free.app', '.ngrok.io'],
        ...(tunnelHost
            ? {
                hmr: {
                    host: tunnelHost,
                    protocol: 'wss',
                    clientPort: 443,
                },
            }
            : {}),
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
