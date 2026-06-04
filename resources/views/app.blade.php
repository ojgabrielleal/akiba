<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @inertiaHead
    @vite(['resources/js/app.js', 'resources/js/css/quill-editor.css', 'resources/js/css/app.css'])
</head>
<body>
    <audio id="audio">
        <source src="/api/cast" type="audio/mpeg">
    </audio>
    @inertia
</body>
</html>
