import { skipWaiting } from 'workbox-core'
import { precacheAndRoute, cleanupOutdatedCaches, createHandlerBoundToURL } from 'workbox-precaching'
import { registerRoute, NavigationRoute } from 'workbox-routing'
import { StaleWhileRevalidate, CacheFirst, NetworkFirst } from 'workbox-strategies'


addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') skipWaiting()
})

precacheAndRoute(self.__WB_MANIFEST)

registerRoute(new NavigationRoute(createHandlerBoundToURL('index.html')))

cleanupOutdatedCaches()

registerRoute(
  /.*\.(?:css|js)$/,
  new StaleWhileRevalidate({
    cacheName: 'css-cache',
  }),
)

registerRoute(
  /.*\.(?:png|jpg|jpeg|svg|gif)$/,
  new CacheFirst({
    cacheName: 'images-cache',
  }),
)

registerRoute(
  /.*\.(?:woff|woff2|ttf|eot)$/,
  new CacheFirst({
    cacheName: 'fonts-cache',
  }),
)

registerRoute(
  /\/server\/api\//,
  new NetworkFirst({
    cacheName: 'api-cache',
  }),
)
