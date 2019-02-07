<template>
  <div>
    <b-collapse class="card" :open.sync="search.isActive">
      <div slot="trigger" class="card-header">
        <span class="card-header-icon">
          <i class="fa" :class="search.isActive ? 'fa-angle-down' : 'fa-angle-right'"></i>
        </span>
        <p class="card-header-title">{{ $t('labels.searchTransactions') }}</p>
      </div>
      <div class="card-content">
        <div class="field is-grouped is-grouped-multiline is-block-mobile">
            <div class="control has-icons-left">
              <input class="input" type="text" :placeholder="$t('labels.findATransaction')" v-model="search.query">
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
                  <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                  <option v-for="category in categories" :key="category.id" v-bind:value="category.id">{{ category.label }}</option>
                </select>
              </div>
            </div>
            <div class="control" v-if="search.category && categoriesAndSubcategoriesLookup[search.category].sub.length > 0">
              <div class="select">
                <select name="parent" v-model="search.subcategory">
                  <option value="">-- {{ $tc('objects.subcategory', 1) }} --</option>
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
        <p class="card-header-title">{{ $t('labels.batchUpdates') }}</p>
      </div>
      <div class="card-content">
        <div class="field is-grouped is-grouped-multiline">
          <div class="control">
            <div class="input is-static">{{ $tc('objects.transactionsSelected', batch.checkedTransactions.length) }} ({{ $n(transactionsCheckedSum, 'currency') }})</div>
          </div>
          <div class="control">
            <div class="select">
              <select v-model="batch.category">
                <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                <option v-for="category in categories" :key="category.id" v-bind:value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
          <div class="control" v-if="batch.category && categoriesAndSubcategoriesLookup[batch.category].sub.length > 0">
            <div class="select">
              <select v-model="batch.subcategory">
                <option value="">-- {{ $tc('objects.subcategory', 1) }} --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[batch.category].sub" :key="subcategory.id" v-bind:value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
          </div>
          <div class="control">
            <button class="button is-primary" :class="{ 'is-loading': batch.isLoading }" @click="processBatchUpdate" :disabled="(batch.isLoading || !isOnline)"><span class="icon"><i class="fa fa-cogs"></i></span><span>{{ $t('actions.apply') }}</span></button>
          </div>
          <div class="control">
            <button class="button is-light" @click="selectAll" :disabled="batch.isLoading"><span class="icon"><i class="fa fa-check-square-o"></i></span><span>{{ $t('actions.selectAll') }}</span></button>
          </div>
          <div class="control">
            <button class="button is-light" @click="selectNone" :disabled="batch.isLoading"><span class="icon"><i class="fa fa-square-o"></i></span><span>{{ $t('actions.clear') }}</span></button>
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
        <b-table-column field="amount" :label="$t('fieldnames.amount')" sortable numeric>
          <span :class="[props.row.amount < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(props.row.amount, 'currency') }}</span>
        </b-table-column>
        <b-table-column field="name" :label="$t('fieldnames.label')" sortable>
          {{ props.row.fullname }}<span class="has-text-grey" v-if="props.row.note"> | {{ props.row.note }}</span>
        </b-table-column>
        <b-table-column field="datePosted" :label="$t('fieldnames.date')" sortable>
          {{ props.row.datePosted | moment("L") }}
        </b-table-column>
        <b-table-column field="category" :label="$tc('objects.category', 1)" sortable>
          {{ props.row.categoryLabel }}<span v-if="props.row.subcategory"> / {{ props.row.subcategoryLabel }}</span>
        </b-table-column>
      </template>
      <template slot="empty">
        <section class="section">
            <div class="content has-text-grey has-text-centered">
                <p>{{ $t('labels.nothingToDisplay') }}</p>
            </div>
        </section>
      </template>
      <template slot="bottom-left">
        <a class="button is-light" @click="downloadData"><span class="icon"><i class="fa fa-download fa-lg"></i></span><span>{{ $t('actions.download') }}</span></a>
      </template>
    </b-table>
    <b-modal :active.sync="modalTransaction.isActive" has-modal-card scroll="keep">
      <transaction v-bind:transaction="modalTransaction.transaction" v-bind:rTransactions="rTransactions"></transaction>
    </b-modal>
  </div>
</template>

<script>
import Bus from './../services/Bus'
import Transaction from '@/components/Transaction'
import CategoriesFactory from './../services/Categories'
import exportFromJSON from 'export-from-json'
export default {
  name: 'transactions',
  components: {
    Transaction
  },
  props: {
    url: {
      required: true,
      type: String
    },
    duration: {
      required: false,
      type: String,
      default: 'P3M'
    }
  },
  mixins: [CategoriesFactory],
  data () {
    const today = new Date()
    today.setHours(0, 0, 0)
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
        startDate: this.$moment(today).subtract(this.$moment.duration(this.duration)).toDate(),
        endDate: today,
        category: '',
        subcategory: ''
      },
      // modal
      modalTransaction: {
        isActive: false,
        transaction: {}
      },
      rTransactions: this.$resource(this.url)
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    },
    displayedTransactions () {
      let query = this.search.query
      let startDate = this.search.startDate
      let endDate = this.search.endDate
      let category = this.search.category
      let subcategory = this.search.subcategory
      let transactions = this.transactions
      transactions.map((t) => {
        t.categoryLabel = (t.category in this.categoriesAndSubcategoriesLookup) ? this.categoriesAndSubcategoriesLookup[t.category].label : null
        t.subcategoryLabel = (t.subcategory in this.categoriesAndSubcategoriesLookup) ? this.categoriesAndSubcategoriesLookup[t.subcategory].label : null
        t.fullname = (t.memo) ? t.memo + ' ' + t.name : t.name
        return t
      })
      return transactions.filter(function (transaction) {
        return (transaction.note + transaction.fullname).toLowerCase().indexOf(query.toLowerCase()) > -1 &
        new Date(Date.parse(transaction.datePosted)) >= startDate &
        new Date(Date.parse(transaction.datePosted)) <= endDate &
        (!category || transaction.category === category) &
        (!subcategory || transaction.subcategory === subcategory)
      })
    },
    transactionsCheckedSum () {
      return this.batch.checkedTransactions.length > 0 ? this.batch.checkedTransactions.reduce((sum, transaction) => sum + transaction.amount, 0) : 0
    }
  },
  methods: {
    // get transactions
    get () {
      this.isLoading = true
      this.rTransactions.query({}).then((response) => {
        this.transactions = response.body
      }, (response) => {
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
      } else {
        // batch edition mode, just (un)check the transaction
        let index = this.batch.checkedTransactions.indexOf(item)
        if (index > -1) {
          this.batch.checkedTransactions.splice(index, 1)
        } else {
          this.batch.checkedTransactions.push(item)
        }
      }
    },
    downloadData () {
      let transactions = JSON.parse(JSON.stringify(this.displayedTransactions)).map((t) => {
        t.amount = this.$n(t.amount, { style: 'decimal', useGrouping: false })
        t.datePosted = this.$moment(t.datePosted).format('L')
        t.dateUser = this.$moment(t.dateUser).format('L')
        delete (t.id)
        delete (t.fitid)
        delete (t.type)
        delete (t.category)
        delete (t.subcategory)
        delete (t.fullname)
        for (var property in t) {
          if (t[property] === null) {
            t[property] = ''
          }
        }
        return t
      })
      exportFromJSON({ data: transactions, fileName: 'transactions', exportType: exportFromJSON.types.csv, withBOM: true })
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
            .then((response) => {
            }, (response) => {
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
      this.batch.checkedTransactions = this.displayedTransactions.slice()
    },
    selectNone () {
      this.batch.checkedTransactions = []
    }
  },
  watch: {
    'search.category' () {
      // clear subcategory search field if category has changed
      this.search.subcategory = ''
    },
    duration () {
      const today = new Date()
      today.setHours(0, 0, 0)
      this.search.startDate = this.$moment(today).subtract(this.$moment.duration(this.duration)).toDate()
    }
  },
  mounted () {
    this.get()
    this.getCategories(true)
    Bus.$on('transactions-updated', () => {
      this.get()
    })
    Bus.$on('transactions-date-filtered', (search) => {
      if ((this.search.endDate.getTime() !== search.periodStart.getTime()) || (this.search.startDate.getTime() !== search.periodEnd.getTime())) {
        this.search.startDate = search.periodStart
        this.search.endDate = search.periodEnd
      }
    })
  },
  beforeDestroy () {
    // remove events listener
    Bus.$off('transactions-updated')
    Bus.$off('transactions-date-filtered')
  }
}
</script>
