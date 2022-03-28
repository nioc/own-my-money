import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { http, setHeader, addResponseInterceptor } from '@/services/Http'
import App from '@/App.vue'
import router from '@/router'
import Auth from '@/services/Auth'
import { createI18n } from 'vue-i18n'
import messages from '@/lang/messages'
import datetimeFormats from '@/lang/dateTimeFormats'
import numberFormats from '@/lang/numberFormats'
import dayjs from '@/services/Datetime'
import { useStore } from '@/store'
import { configValidator } from '@/services/Validator'
import mitt from 'mitt'
import { Tabs, Field, Config, Loading, Switch, Modal, Table, Icon, Upload, Datepicker, Collapse, Slider, Input, Notification, Tooltip, Steps } from '@oruga-ui/oruga-next'
import { bulmaConfig } from '@oruga-ui/theme-bulma'
import '@oruga-ui/oruga-next/dist/oruga.css'
import '@fortawesome/fontawesome-free/css/fontawesome.css'
import '@fortawesome/fontawesome-free/css/regular.css'
import '@fortawesome/fontawesome-free/css/solid.css'
import '@fortawesome/fontawesome-free/css/brands.css'
import './assets/styles.scss'

const app = createApp(App)

// global bus events et http client
const bus = mitt()
app.config.globalProperties.$bus = bus
app.config.globalProperties.$http = http

// store
app.use(createPinia())
const store = useStore()
app.config.globalProperties.$store = store

// try to get user
const user = Auth.getProfile()
if (user) {
  store.setUser(user)
}

// set locale / i18n
let locale = navigator.language.substring(0,2)
if (!['en', 'fr'].includes(locale)) {
  locale = 'en'
}
if (user && user.language) {
  locale = user.language
}

const i18n = createI18n({
  legacy: true,
  fallbackLocale: 'en',
  locale: locale,
  messages: messages,
  datetimeFormats,
  numberFormats,
})
app.use(i18n)
setHeader('Accept-Language', locale)
document.querySelector('html').setAttribute('lang', locale)

// fields validation
configValidator({
  locale,
  i18n,
})

// datetime
dayjs.locale(locale)
app.config.globalProperties.$dayjs = dayjs
const localeData = dayjs.localeData()

// UI elements
app.use(Tabs)
  .use(Field)
  .use(Loading)
  .use(Switch)
  .use(Modal)
  .use(Table)
  .use(Icon)
  .use(Upload)
  .use(Datepicker)
  .use(Collapse)
  .use(Slider)
  .use(Input)
  .use(Notification)
  .use(Tooltip)
  .use(Steps)
  .use(Config, {
    ...bulmaConfig,
    iconPack: 'fas',
    locale,
    datepicker: {
      ...bulmaConfig.datepicker,
      firstDayOfWeek: localeData.firstDayOfWeek(),
      monthNames: localeData.months(),
      dayNames: localeData.weekdaysMin(),
    },
  })

// set authorization header at init
setHeader('Authorization', Auth.getAuthHeader())

// check auth before changing page
router.beforeEach((to, from, next) => {
  // check if user is authenticated before each page (except login and setup page)
  Auth.getToken()
  if (!Auth.user.authenticated && to.name !== 'login' && to.name !== 'setup') {
    // user not authenticated, redirect to login page
    Auth.logout()
    store.reset()
    next('/login?redirect=' + to.fullPath)
    return
  }
  // change title attribute
  if (to.meta.title) {
    let title = to.meta.title
    const params = to.params
    for (var property in params) {
      title = title.replace(':' + property, params[property])
    }
    document.title = title + ' | OwnMyMoney'
  }
  // close hamburger (if existing)
  const burger = document.getElementById('navbar-burger')
  if (burger) {
    burger.classList.remove('is-active')
    document.getElementById('navbar-menu').classList.remove('is-active')
  }
  next()
})

// add http interceptor to redirect user on login page if a 401 occurs during API call
addResponseInterceptor((response) => {
  return response
}, ((error) => {
  if (error.response && error.response.data && error.response.data.message) {
    error.message = error.response.data.message
  }
  if (error.response && error.response.status === 401 && router.currentRoute.value.fullPath.search(/^\/login/) === -1) {
    // redirect only if page is not already login page (multiples 401 in same time)
    Auth.logout()
    store.reset()
    router.replace({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
  }
  return Promise.reject(error)
}))

if (user.authenticated) {
  store.loadCategories()
  store.loadHolders()
  store.loadMaps()
}

app.use(router)
app.mount('#body')
