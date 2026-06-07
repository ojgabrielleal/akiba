self.addEventListener('install', event => {
  console.log('PWA Service Worker instalado');
});

self.addEventListener('activate', event => {
  console.log('PWA Service Worker ativado');
});