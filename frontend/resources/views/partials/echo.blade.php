<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    // Reverb speaks the Pusher protocol, so the pusher-js client drives it directly.
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: @json(config('reverb.apps.apps.0.key')),
        wsHost: @json(config('reverb.apps.apps.0.options.host')),
        wsPort: {{ (int) config('reverb.apps.apps.0.options.port', 8080) }},
        wssPort: {{ (int) config('reverb.apps.apps.0.options.port', 8080) }},
        forceTLS: {{ config('reverb.apps.apps.0.options.scheme') === 'https' ? 'true' : 'false' }},
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }
    });
</script>
