<template>
  <form @submit.prevent="validateBeforeSubmit" novalidate>
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Transaction</p>
      </header>
      <section class="modal-card-body">
        <div class="field">
          <label class="label">Name</label>
          <div class="control">
            <input class="input" type="text" name="name" placeholder="Transaction name (from bank)" v-model="transaction.name" v-validate="'required'" :class="{ 'is-danger': errors.has('name') }">
            <span v-show="errors.has('name')" class="help is-danger">{{ errors.first('name') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">Amount</label>
          <div class="control">
            <input class="input" type="number" name="amount" placeholder="Amount" v-model="transaction.amount" v-validate="'required|decimal:2'" :class="{ 'is-danger': errors.has('amount') }">
            <span v-show="errors.has('amount')" class="help is-danger">{{ errors.first('amount') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">Date</label>
          <div class="control">
            <b-datepicker placeholder="End date" icon="calendar" :readonly="false" :max-date="currentDate" v-model="dateUser"></b-datepicker>
          </div>
        </div>
        <div class="field">
          <label class="label">Note</label>
          <div class="control">
            <input class="input" type="text" name="note" placeholder="Note of your choice" v-model="transaction.note" v-validate="'max:30'" :class="{ 'is-danger': errors.has('note') }">
            <span v-show="errors.has('note')" class="help is-danger">{{ errors.first('note') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">Category</label>
          <div class="control">
            <div class="select">
              <select name="parent" v-model="transaction.category">
                <option value="">-- Category --</option>
                <option v-for="category in categories" :key="category.id" v-bind:value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field" v-if="transaction.category && categoriesAndSubcategoriesLookup[transaction.category] && categoriesAndSubcategoriesLookup[transaction.category].sub.length > 0">
          <label class="label">Subcategory</label>
          <div class="control">
            <div class="select">
              <select name="parent" v-model="transaction.subcategory">
                <option value="">-- Subcategory --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[transaction.category].sub" :key="subcategory.id" v-bind:value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="message is-danger block" v-if="error">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
      </section>
      <footer class="modal-card-foot">
        <button class="button is-primary">Save</button>
        <button class="button" type="button" @click="$parent.close()">Cancel</button>
      </footer>
      <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>
    </div>
  </form>
</template>

<script>
import Config from './../services/Config'
export default {
  props: ['rTransactions', 'transaction'],
  data () {
    return {
      error: '',
      isLoading: false,
      currentDate: new Date(),
      categories: [],
      categoriesAndSubcategoriesLookup: [],
      // resources
      rCategories: this.$resource(Config.API_URL + 'categories{/id}')
    }
  },
  computed: {
    dateUser: {
      get: function () {
        return this.$moment(this.transaction.dateUser).toDate()
      },
      set: function (newValue) {
        this.transaction.dateUser = this.$moment(newValue).format()
      }
    }
  },
  methods: {
    // get categories and subcategories
    getCategories () {
      // TODO refactoring this function
      function getLookup (categories) {
        // save the complete list and create lookup for getting label
        var lookup = []
        for (let i = 0; i < categories.length; i++) {
          let category = categories[i]
          lookup[category.id] = category
          for (let i = 0; i < category.sub.length; i++) {
            let subcategory = category.sub[i]
            lookup[subcategory.id] = subcategory
          }
        }
        return lookup
      }
      if (localStorage.getItem('categoriesActives')) {
        this.categories = JSON.parse(localStorage.getItem('categoriesActives'))
        this.categoriesAndSubcategoriesLookup = getLookup(this.categories)
        return
      }
      this.rCategories.query().then(response => {
        this.categories = response.body
        this.categoriesAndSubcategoriesLookup = getLookup(this.categories)
        // put categories in local storage for future usage
        localStorage.setItem('categoriesActives', JSON.stringify(this.categories))
      }, response => {
        // @TODO : add error handling
        console.error(response)
      })
    },
    validateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          // if validation is ok, call accounts API
          this.isLoading = true
          this.transaction.amount = parseFloat(this.transaction.amount)
          this.rTransactions.update({ id: this.transaction.id }, this.transaction)
            .then(response => {
              this.$parent.close()
            }, response => {
              if (response.body.message) {
                this.error = response.body.message
                return
              }
              this.error = response.status + ' - ' + response.statusText
            })
            .finally(function () {
              // remove loading overlay when API replies
              this.isLoading = false
            })
        }
      })
    }
  },
  watch: {
    'transaction.category': function () {
      // clear subcategory field if category has changed
      this.transaction.subcategory = ''
    }
  },
  mounted: function () {
    this.getCategories()
  }
}
</script>
