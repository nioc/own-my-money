importScripts("/own-my-money/precache-manifest.bbcfac83fde1ebf24510f485f31c8a84.js", "/own-my-money/workbox-v3.6.3/workbox-sw.js");
workbox.setConfig({modulePathPrefix: "/own-my-money/workbox-v3.6.3"});
/* global workbox */
workbox.precaching.precacheAndRoute([])

workbox.routing.registerRoute(
  /.*\.(?:css|js)$/,
  workbox.strategies.staleWhileRevalidate({
    cacheName: 'css-cache'
  })
)

workbox.routing.registerRoute(
  /.*\.(?:png|jpg|jpeg|svg|gif)$/,
  workbox.strategies.cacheFirst({
    cacheName: 'images-cache'
  })
)

workbox.routing.registerRoute(
  /\/server\/api\//,
  workbox.strategies.networkFirst({
    cacheName: 'api-cache'
  })
)

