import Vue from 'vue'
import Config from './../services/Config'
import VueResource from 'vue-resource'

Vue.use(VueResource)

export default {
  data () {
    return {
      categories: [],
      categoriesAndSubcategoriesLookup: [],
      rCategories: Vue.resource(Config.API_URL + 'categories{/id}'),
    }
  },
  methods: {
    handleResponse (categories) {
      function getLookup (categories) {
        // save the complete list and create lookup for getting label
        var lookup = []
        for (let i = 0; i < categories.length; i++) {
          const category = categories[i]
          lookup[category.id] = category
          for (let i = 0; i < category.sub.length; i++) {
            const subcategory = category.sub[i]
            lookup[subcategory.id] = subcategory
          }
        }
        return lookup
      }
      this.categories = categories
      this.categoriesAndSubcategoriesLookup = getLookup(this.categories)
    },

    // get categories and subcategories and push it on shared bus
    getCategories (includeInactives) {
      let localStorageKey = 'categoriesActives'
      let query = {}
      if (includeInactives) {
        localStorageKey = 'categories'
        query = { status: 'all' }
      }
      if (localStorage.getItem(localStorageKey)) {
        this.handleResponse(JSON.parse(localStorage.getItem(localStorageKey)))
        return
      }
      this.rCategories.query(query).then((response) => {
        this.handleResponse(response.body)
        // put categories in local storage for future usage
        localStorage.setItem(localStorageKey, JSON.stringify(this.categories))
      }, (response) => {
        // @TODO : add error handling
        console.error(response)
      })
    },
  },
}
