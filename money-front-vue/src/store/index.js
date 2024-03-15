import { defineStore } from 'pinia'
import { http } from '@/services/Http'

const getDefaultState = () => {
  return {
    user: { authenticated: false, scope: null, login: null },
    holders: [],
    categories: [],
    categoriesAndSubcategoriesLookup: [],
    maps: [],
  }
}

export const useStore = defineStore('main', {
  state: () => {
    return {
      isOnline: null,
      ...getDefaultState(),
    }
  },

  getters: {
    holderId: (state) => {
      const currentHolder = state.holders.find(((holder) => holder.current))
      return currentHolder ? currentHolder.id : null
    },

    getHolderName: (state) => {
      return (holderId, unknownUserName) => {
        const holder = state.holders.find((holder) => holder.id === holderId)
        return holder ? holder.name : unknownUserName
      }
    },

    activeCategories: (state) => {
      return state.categories
        .filter(category => category.status)
        .map(category => {
          const activeCategory = { ...category }
          delete activeCategory.status
          return activeCategory
        })
    },
  },

  actions: {
    reset () {
      this.$state = { ...this.$state, ...getDefaultState() }
    },

    setConnectivity (isOnline) {
      this.isOnline = isOnline
    },

    setUser(user) {
      this.user = user
    },

    async loadHolders() {
      try {
        const response = await http.get('holders')
        this.holders = response.data
      } catch (error) {
        // @TODO : add error handling
        console.error(error)
      }
    },

    async loadCategories() {
      try {
        const response = await http.get('categories', { params: { status: 'all' } })
        this.categories = response.data
        // save the complete list and create lookup for getting label
        const lookup = []
        for (let i = 0; i < response.data.length; i++) {
          const category = response.data[i]
          lookup[category.id] = category
          for (let i = 0; i < category.sub.length; i++) {
            const subcategory = category.sub[i]
            lookup[subcategory.id] = subcategory
          }
        }
        this.categoriesAndSubcategoriesLookup = lookup
      } catch (error) {
        // @TODO : add error handling
        console.error(error)
      }
    },

    async loadMaps(errorCallback) {
      try {
        const response = await http.get('maps')
        this.maps = response.data
      } catch (error) {
        if (errorCallback) {
          errorCallback(error)
          return
        }
        console.error(error)
      }
    },

  },
})
