import { fileURLToPath, URL } from 'url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import GitRevision from 'vite-plugin-git-revision'
import { visualizer } from "rollup-plugin-visualizer"
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  base: './',
  plugins: [
    vue(),
    GitRevision({ branch: true }),
    visualizer({ open: true, gzipSize: true }),
    VitePWA({
      strategies: 'injectManifest',
      srcDir: 'src',
      filename: 'service-worker.js',
      includeAssets: ['favicon.ico', 'robots.txt'],
      manifest: {
        id: 'OwnMyMoney',
        name: 'OwnMyMoney',
        description: 'A simple way to stay on top of your banking from any web browser ',
        "display": "standalone",
        "theme_color": '#363636',
        "background_color": "#363636",
        icons: [
          {
            "src": "./img/android-chrome-192x192.png",
            "sizes": "192x192",
            "type": "image/png",
          },
          {
            "src": "./img/android-chrome-512x512.png",
            "sizes": "512x512",
            "type": "image/png",
          },
        ],
      },
    }),
  ],
  optimizeDeps: {
    esbuildOptions: {
      define: {
        global: 'globalThis',
      },
    },
  },
  build: {
    rollupOptions: {
      plugins: [
      ],
      output: {
        manualChunks: {
          'user-settings': [
            './src/components/Setup.vue',
            './src/components/About.vue',
            './src/components/Profile.vue',
            './src/components/Patterns.vue',
          ],
          'system-settings': [
            './src/components/Users.vue',
            './src/components/Maps.vue',
            './src/components/Map.vue',
            './src/components/Categories.vue',
            './src/components/Category.vue',
          ],
        },
      },
    },
    target: 'modules',
    chunkSizeWarningLimit: 900,
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
})
