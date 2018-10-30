<template>
  <div>
    <b-collapse class="card" :open.sync="search.isActive">
      <div slot="trigger" class="card-header">
        <span class="card-header-icon">
          <i class="fa" :class="search.isActive ? 'fa-angle-down' : 'fa-angle-right'"></i>
        </span>
        <p class="card-header-title">Search transactions</p>
      </div>
      <div class="card-content">
        <div class="field is-grouped is-grouped-multiline is-block-mobile">
            <div class="control has-icons-left">
              <input class="input" type="text" placeholder="Find a transaction" v-model="search.query">
              <span class="icon is-small is-left">
                <i class="fa fa-search"></i>
              </span>
            </div>
            <div class="control">
              <b-datepicker placeholder="Start date" icon="calendar" editable :max-date="search.currentDate" v-model="search.startDate"></b-datepicker>
            </div>
            <div class="control">
              <b-datepicker placeholder="End date" icon="calendar" editable :max-date="search.currentDate" v-model="search.endDate"></b-datepicker>
            </div>
            <div class="control">
              <div class="select">
                <select name="parent" v-model="search.category">
                  <option value="">-- Category --</option>
                  <option v-for="category in categories" :key="category.id" v-bind:value="category.id">{{ category.label }}</option>
                </select>
              </div>
            </div>
            <div class="control" v-if="search.category && categoriesAndSubcategoriesLookup[search.category].sub.length > 0">
              <div class="select">
                <select name="parent" v-model="search.subcategory">
                  <option value="">-- Subcategory --</option>
                  <option v-for="subcategory in categoriesAndSubcategoriesLookup[search.category].sub" :key="subcategory.id" v-bind:value="subcategory.id">{{ subcategory.label }}</option>
                </select>
              </div>
            </div>
        </div>
      </div>
    </b-collapse>

    <b-collapse class="card" :open.sync="batch.isActive">
      <div slot="trigger" class="card-header">
        <span class="card-header-icon">
          <i class="fa" :class="batch.isActive ? 'fa-angle-down' : 'fa-angle-right'"></i>
        </span>
        <p class="card-header-title">Batch updates</p>
      </div>
      <div class="card-content">
        <div class="field is-grouped is-grouped-multiline">
          <div class="control">
            <div class="input is-static">{{batch.checkedTransactions.length}} transactions selected ({{ transactionsCheckedSum | currency }})</div>
          </div>
          <div class="control">
            <div class="select">
              <select v-model="batch.category">
                <option value="">-- Category --</option>
                <option v-for="category in categories" :key="category.id" v-bind:value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
          <div class="control" v-if="batch.category && categoriesAndSubcategoriesLookup[batch.category].sub.length > 0">
            <div class="select">
              <select v-model="batch.subcategory">
                <option value="">-- Subcategory --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[batch.category].sub" :key="subcategory.id" v-bind:value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
          </div>
          <div class="control">
            <button class="button is-primary" :class="{ 'is-loading': batch.isLoading }" @click="processBatchUpdate" :disabled="batch.isLoading"><span class="icon"><i class="fa fa-cogs"></i></span><span>Apply</span></button>
          </div>
          <div class="control">
            <button class="button is-light" @click="selectAll" :disabled="batch.isLoading"><span class="icon"><i class="fa fa-check-square-o"></i></span><span>Select all</span></button>
          </div>
          <div class="control">
            <button class="button is-light" @click="selectNone" :disabled="batch.isLoading"><span class="icon"><i class="fa fa-square-o"></i></span><span>Clear</span></button>
          </div>
        </div>
        <div class="field is-block-mobile">
          <progress class="progress" :value="batch.progress" max="1">{{ batch.progress }} %</progress>
        </div>
        <div class="field">
          <div class="message is-danger" v-if="batch.result">
            <div class="message-body">
              {{ batch.result }}
            </div>
          </div>
        </div>
      </div>
    </b-collapse>

    <b-table :data=displayedTransactions :paginated="true" :striped="true" :hoverable="true" :loading="isLoading" default-sort="datePosted" default-sort-direction="desc" @select="edit" :checkable="batch.isActive" :checked-rows.sync="batch.checkedTransactions">
      <template slot-scope="props">
        <b-table-column field="amount" label="Amount" sortable numeric>
          <span :class="[props.row.amount < 0 ? 'has-text-danger' : 'has-text-success']">{{ props.row.amount | currency }}</span>
        </b-table-column>
        <b-table-column field="name" label="Name" sortable>
          {{ props.row.fullname }}<span class="has-text-grey" v-if="props.row.note"> | {{ props.row.note }}</span>
        </b-table-column>
        <b-table-column field="datePosted" label="Date" sortable>
          {{ props.row.datePosted | moment("DD/MM/YYYY") }}
        </b-table-column>
        <b-table-column field="category" label="Category" sortable>
          {{ props.row.categoryLabel }}<span v-if="props.row.subcategory"> / {{ props.row.subcategoryLabel }}</span>
        </b-table-column>
      </template>
      <template slot="empty">
        <section class="section">
            <div class="content has-text-grey has-text-centered">
                <p>There is nothing to display</p>
            </div>
        </section>
      </template>
    </b-table>
    <b-modal :active.sync="modalTransaction.isActive" has-modal-card scroll="keep">
      <transaction v-bind:transaction="modalTransaction.transaction" v-bind:rTransactions="rTransactions"></transaction>
    </b-modal>
  </div>
</template>

<script>
import Config from './../services/Config'
import Bus from './../services/Bus'
import Transaction from '@/components/Transaction'
export default {
  name: 'transactions',
  components: {
    Transaction
  },
  props: ['url'],
  data () {
    const today = new Date()
    return {
      transactions: [],
      isLoading: false,
      batch: {
        isActive: false,
        isLoading: false,
        progress: 0,
        result: '',
        checkedTransactions: [],
        category: '',
        subcategory: ''
      },
      // filter
      search: {
        isActive: false,
        query: '',
        currentDate: today,
        startDate: new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30),
        endDate: today,
        category: '',
        subcategory: ''
      },
      // modal
      modalTransaction: {
        isActive: false,
        transaction: {}
      },
      // categories
      categories: [],
      categoriesAndSubcategoriesLookup: [],
      rCategories: this.$resource(Config.API_URL + 'categories{/id}'),
      rTransactions: this.$resource(this.url)
    }
  },
  computed: {
    displayedTransactions: function () {
      let query = this.search.query
      let startDate = this.search.startDate
      let endDate = this.search.endDate
      let category = this.search.category
      let subcategory = this.search.subcategory
      let transactions = this.transactions
      transactions.map(t => {
        t.categoryLabel = (t.category in this.categoriesAndSubcategoriesLookup) ? this.categoriesAndSubcategoriesLookup[t.category].label : null
        t.subcategoryLabel = (t.subcategory in this.categoriesAndSubcategoriesLookup) ? this.categoriesAndSubcategoriesLookup[t.subcategory].label : null
        t.fullname = (t.memo) ? t.memo + ' ' + t.name : t.name
        return t
      })
      return transactions.filter(function (transaction) {
        return transaction.fullname.toLowerCase().indexOf(query.toLowerCase()) > -1 &
        new Date(Date.parse(transaction.datePosted)) >= startDate &
        new Date(Date.parse(transaction.datePosted)) <= endDate &
        (!category || transaction.category === category) &
        (!subcategory || transaction.subcategory === subcategory)
      })
    },
    transactionsCheckedSum: function () {
      return this.batch.checkedTransactions.length > 0 ? this.batch.checkedTransactions.reduce((sum, transaction) => sum + transaction.amount, 0) : 0
    }
  },
  methods: {
    // get transactions
    get () {
      this.isLoading = true
      this.rTransactions.query({}).then(response => {
        this.transactions = response.body
      }, response => {
        // @TODO : add error handling
        console.error(response)
      }).finally(function () {
        // remove loading overlay when API replies
        this.isLoading = false
      })
    },
    // edit transaction in modal form
    edit (item) {
      if (!this.batch.isActive) {
        this.modalTransaction.transaction = item
        this.modalTransaction.isActive = true
      }
    },
    // get categories and subcategories
    getCategories () {
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
      if (localStorage.getItem('categories')) {
        this.categories = JSON.parse(localStorage.getItem('categories'))
        this.categoriesAndSubcategoriesLookup = getLookup(this.categories)
        return
      }
      this.rCategories.query({status: 'all'}).then(response => {
        this.categories = response.body
        this.categoriesAndSubcategoriesLookup = getLookup(this.categories)
        // put categories in local storage for future usage
        localStorage.setItem('categories', JSON.stringify(this.categories))
      }, response => {
        // @TODO : add error handling
        console.error(response)
      })
    },
    processBatchUpdate () {
      let length = this.batch.checkedTransactions.length
      if (length > 0) {
        this.batch.isLoading = true
        this.batch.progress = 0
        let processed = 0
        let category = this.batch.category
        let subcategory = this.batch.subcategory
        this.batch.result = ''
        for (let transaction of this.batch.checkedTransactions) {
          if (category) {
            transaction.category = category
            if (subcategory) {
              transaction.subcategory = subcategory
            }
          }
          this.rTransactions.update({ id: transaction.id }, transaction)
            .then(response => {
            }, response => {
              if (response.body.message) {
                this.batch.result += transaction.id + ' : ' + response.body.message + '. '
                return
              }
              this.batch.result += transaction.id + ' : ' + response.status + ' - ' + response.statusText + '. '
            })
            .finally(function () {
              processed++
              this.batch.progress = processed / length
              if (processed === length) {
                this.batch.isLoading = false
              }
            })
        }
      }
    },
    selectAll () {
      this.batch.checkedTransactions = this.displayedTransactions
    },
    selectNone () {
      this.batch.checkedTransactions = []
    }
  },
  watch: {
    'search.category': function () {
      // clear subcategory search field if category has changed
      this.search.subcategory = ''
    }
  },
  mounted: function () {
    this.get()
    this.getCategories()
    Bus.$on('transactions-updated', () => {
      this.get()
    })
  }
}
</script>