importScripts("/own-my-money/precache-manifest.4a39a481a6b9afb0e1696fa0a6fe82ba.js", "/own-my-money/workbox-v3.6.3/workbox-sw.js");
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

