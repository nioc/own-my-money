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
      return new Promise((resolve, reject) => {
        if (localStorage.getItem(localStorageKey)) {
          this.holders = JSON.parse(localStorage.getItem(localStorageKey))
          resolve(this.holders)
        }
        this.rHolders.query().then((response) => {
          this.holders = response.body
          // put holders in local storage for future usage
          localStorage.setItem(localStorageKey, JSON.stringify(this.holders))
          resolve(this.holders)
        }, (response) => {
          // @TODO : add error handling
          console.error(response)
          reject(response)
        })
      })
    },
    removeHoldersCache () {
      localStorage.removeItem(localStorageKey)
    },
    getCurrentHolderId () {
      return new Promise((resolve) => {
        this.getHolders().then((holders) => {
          const currentHolder = holders.filter(holder => holder.current)
          if (currentHolder.length === 1) {
            resolve(currentHolder[0].id)
          }
          resolve(null)
        }, () => {
          resolve(null)
        })
      })
    },
  },
}
