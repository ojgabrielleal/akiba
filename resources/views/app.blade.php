<!DOCTYPE html>
<html lang="pt-BR">

<head>
    @inertiaHead
    @vite(['resources/js/app.js', 'resources/js/css/quill-editor.css', 'resources/js/css/app.css'])
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function (OneSignal) {
            await OneSignal.init({
                appId: "{{ config('onesignal.app_id') }}",
                serviceWorkerPath: "/OneSignalSDKWorker.js",
                serviceWorkerParam: {
                    scope: "/"
                }
            });
        });
    </script>
</head>

<body>
    @inertia
    <audio id="audio">
        <source src="/api/stream" type="audio/mpeg">
    </audio>
</body>

</html>
