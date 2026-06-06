importScripts("https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js");

self.addEventListener("install", event => {
  console.log("Service Worker instalado");
});

self.addEventListener("activate", event => {
  console.log("Service Worker ativado");
});