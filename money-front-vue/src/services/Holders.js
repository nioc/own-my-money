import Vue from 'vue'
import Config from '@/services/Config'
import VueResource from 'vue-resource'

Vue.use(VueResource)
const localStorageKey = 'holders'
export default {
  data () {
    return {
      holders: [],
      rHolders: Vue.resource(Config.API_URL + 'holders'),
    }
  },
  methods: {
    getHolders () {
      if (localStorage.getItem(localStorageKey)) {
        this.holders = JSON.parse(localStorage.getItem(localStorageKey))
        return
      }
      this.rHolders.query().then((response) => {
        this.holders = response.body
        // put holders in local storage for future usage
        localStorage.setItem(localStorageKey, JSON.stringify(this.holders))
      }, (response) => {
        // @TODO : add error handling
        console.error(response)
      })
    },
    removeHoldersCache () {
      localStorage.removeItem(localStorageKey)
    },
  },
}
