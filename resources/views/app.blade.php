<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0091ff">
    <meta name="application-name" content="Rede Akiba">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Rede Akiba">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="manifest" href="/manifest.json?v=8">
    <link rel="apple-touch-icon" href="/img/pwa/icon-256.png">
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
</body>
</html>
