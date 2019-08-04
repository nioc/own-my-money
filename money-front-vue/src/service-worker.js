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
  /.*\.(?:woff|woff2|ttf|eot)$/,
  workbox.strategies.cacheFirst({
    cacheName: 'fonts-cache'
  })
)

workbox.routing.registerRoute(
  /\/server\/api\//,
  workbox.strategies.networkFirst({
    cacheName: 'api-cache'
  })
)
