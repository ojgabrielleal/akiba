<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @inertiaHead
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    @vite(['resources/js/app.js', 'resources/js/css/app.css', 'resources/js/css/quill-editor.css'])
</head>
<body>
    @inertia
    <audio id="audio">
        <source src="/api/stream" type="audio/mpeg">
    </audio>
</body>
</html>