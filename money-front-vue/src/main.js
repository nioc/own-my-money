import Vue from 'vue'
import App from './App'
import 'font-awesome/css/font-awesome.min.css'
import 'bulma/css/bulma.css'

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  el: '#money',
  components: { App },
  template: '<App/>'
})
