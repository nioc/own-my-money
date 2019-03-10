module.exports = {
  publicPath: '/own-my-money/',
  pwa: {
    workboxPluginMode: 'InjectManifest',
    workboxOptions: {
      importWorkboxFrom: 'local',
      swSrc: 'src/service-worker.js'
    },
    name: 'OwnMyMoney',
    themeColor: '#363636',
    msTileColor: '#363636',
    iconPaths: {
      favicon32: 'img/favicon-32x32.png',
      favicon16: 'img/favicon-16x16.png',
      appleTouchIcon: 'img/apple-touch-icon.png',
      maskIcon: 'img/safari-pinned-tab.svg',
      msTileImage: 'img/mstile-150x150.png'
    }
  }
}
