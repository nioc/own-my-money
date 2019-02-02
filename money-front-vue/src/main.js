import Vue from 'vue'
import Vuex from 'vuex'
import App from './App'
import router from './router'
import './registerServiceWorker'
import Auth from './services/Auth'
import VueI18n from 'vue-i18n'
import messages from '@/lang/messages'
import dateTimeFormats from '@/lang/dateTimeFormats'
import numberFormats from '@/lang/numberFormats'
import VueMoment from 'vue-moment'
import 'moment/locale/fr'
import validationMessagesEn from 'vee-validate/dist/locale/en'
import validationMessagesFr from 'vee-validate/dist/locale/fr'
import VueResource from 'vue-resource'
import VeeValidate from 'vee-validate'
import Bus from './services/Bus.js'
import Buefy from 'buefy'
import 'font-awesome/css/font-awesome.min.css'
import './assets/styles.scss'
import 'bulma-o-steps/bulma-steps.min.css'
const moment = require('moment')

Vue.config.productionTip = false
Vue.use(Vuex)
Vue.use(VueI18n)
Vue.use(VueResource)

const store = new Vuex.Store({
  state: {
  },
  mutations: {
  }
})

let locale = navigator.language

const i18n = new VueI18n({
  fallbackLocale: 'en',
  locale: locale,
  messages: messages,
  dateTimeFormats,
  numberFormats
})
Vue.http.headers.common['Accept-Language'] = locale
document.querySelector('html').setAttribute('lang', locale)

Vue.use(VeeValidate, {
  i18nRootKey: 'validations',
  i18n,
  dictionary: {
    en: { messages: validationMessagesEn.messages, attributes: messages.en.fieldnames },
    fr: { messages: validationMessagesFr.messages, attributes: messages.fr.fieldnames }
  }
})

Vue.use(VueMoment, {
  moment
})

let localeData = Vue.moment.localeData()
Vue.use(Buefy, {
  defaultIconPack: 'fa',
  defaultFirstDayOfWeek: localeData.firstDayOfWeek(),
  defaultMonthNames: localeData.months(),
  defaultDayNames: localeData.weekdaysMin(),
  defaultDateFormatter: (date) => Vue.moment(date, 'L').format('L')
})

// set header at init
delete Vue.http.headers.common['Authorization']
let authHeader = Auth.getAuthHeader()
if (authHeader) {
  Vue.http.headers.common['Authorization'] = authHeader
}

// check auth before changing page
router.beforeEach((to, from, next) => {
  // check if user is authenticated before each page (except login and setup page)
  Auth.getToken()
  if (!Auth.user.authenticated && to.name !== 'login' && to.name !== 'setup') {
    // user not authenticated, redirect to login page
    Auth.logout()
    Bus.$emit('user-logged', {})
    next('/login?redirect=' + to.fullPath)
    return
  }
  // change title attribute
  if (to.meta.title) {
    let title = to.meta.title
    let params = to.params
    for (var property in params) {
      title = title.replace(':' + property, params[property])
    }
    document.title = title + ' | OwnMyMoney'
  }
  // close hamburger (if existing)
  let burger = document.getElementById('navbar-burger')
  if (burger) {
    burger.classList.remove('is-active')
    document.getElementById('navbar-menu').classList.remove('is-active')
  }
  next()
})

// add http interceptor to redirect user on login page if a 401 occurs during API call
Vue.http.interceptors.push((request, next) => {
  next((response) => {
    if (response.status === 401 && router.currentRoute.fullPath.search(/^\/login/) === -1) {
      // redirect only if page is not already login page (multiples 401 in same time)
      Auth.logout()
      Bus.$emit('user-logged', {})
      router.replace({ name: 'login', query: { redirect: router.currentRoute.fullPath } })
    }
    return response
  })
})

new Vue({
  router,
  i18n,
  store,
  render: h => h(App)
}).$mount('#money')
