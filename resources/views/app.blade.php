<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @inertiaHead
    @vite([
        'resources/js/app.js', 
        'resources/js/css/app.css',
        'resources/js/css/custom.css',
        'resources/js/css/quill.css', 
    ])
</head>
<body>
    @inertia
    <audio id="audio">
        <source src="/api/stream" type="audio/mpeg">
    </audio>

    @production
        <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
        <script>
            if (window.location.hostname === 'www.akiba.com.br') {
                window.OneSignalDeferred = window.OneSignalDeferred || [];
                OneSignalDeferred.push(async function (OneSignal) {
                    await OneSignal.init({
                        appId: @js(config('services.onesignal.app_id')),
                    });
                });
            }
        </script>
    @endproduction
</body>
</html>
