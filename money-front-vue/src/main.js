import Vue from 'vue'
import App from './App'
import router from './router'
import Auth from './services/Auth'
import VueResource from 'vue-resource'
import VeeValidate from 'vee-validate'
import Bus from './services/Bus.js'
import 'font-awesome/css/font-awesome.min.css'
import './assets/styles.scss'

Vue.config.productionTip = false
Vue.use(VueResource)
Vue.use(VeeValidate)
// set header at init
delete Vue.http.headers.common['Authorization']
let authHeader = Auth.getAuthHeader()
if (authHeader) {
  Vue.http.headers.common['Authorization'] = authHeader
}

// check auth before changing page
router.beforeEach((to, from, next) => {
  // check if user is authenticated before each page (except login page)
  Auth.getToken()
  if (!Auth.user.authenticated && to.name !== 'login') {
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
  next()
})

// add http interceptor to redirect user on login page if a 401 occurs during API call
Vue.http.interceptors.push((request, next) => {
  next((response) => {
    if (response.status === 401 && router.currentRoute.fullPath.search(/^\/login/) === -1) {
      // redirect only if page is not already login page (multiples 401 in same time)
      Auth.logout()
      Bus.$emit('user-logged', {})
      router.replace({name: 'login', query: { redirect: router.currentRoute.fullPath }})
    }
    return response
  })
})

/* eslint-disable no-new */
new Vue({
  el: '#money',
  router,
  components: { App },
  template: '<App/>'
})
