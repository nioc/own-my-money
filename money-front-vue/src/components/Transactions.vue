<template>
  <div>
    <o-collapse :open="search.isActive" class="card">
      <template #trigger>
        <div class="card-header">
          <span class="card-header-icon">
            <i class="fa" :class="search.isActive ? 'fa-angle-down' : 'fa-angle-right'" />
          </span>
          <p class="card-header-title">{{ $t('labels.searchTransactions') }}</p>
        </div>
      </template>
      <div class="card-content">
        <div class="field is-grouped is-grouped-multiline is-block-mobile">
          <div class="control has-icons-left">
            <input v-model="search.query" class="input" type="text" :placeholder="$t('labels.findATransaction')">
            <span class="icon is-small is-left">
              <i class="fa fa-search" />
            </span>
          </div>
          <div class="control">
            <o-datepicker v-model="search.periodStart" placeholder="Start date" icon="calendar" editable :max-date="search.currentDate" lists-class="field has-addons" @update:model-value="get()" />
          </div>
          <div class="control">
            <o-datepicker v-model="search.periodEnd" placeholder="End date" icon="calendar" editable :max-date="search.currentDate" lists-class="field has-addons" @update:model-value="get()" />
          </div>
          <div class="control">
            <div class="select">
              <select v-model="search.category" name="parent">
                <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                <option :value="null">{{ $t('labels.uncategorizedTransaction') }}</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
          <div v-if="search.category && categoriesAndSubcategoriesLookup[search.category].sub.length > 0" class="control">
            <div class="select">
              <select v-model="search.subcategory" name="parent">
                <option value="">-- {{ $tc('objects.subcategory', 1) }} --</option>
                <option :value="null">{{ $t('labels.uncategorizedTransaction') }}</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[search.category].sub" :key="subcategory.id" :value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </o-collapse>

    <o-collapse v-model:open="batch.isActive" class="card">
      <template #trigger>
        <div class="card-header">
          <span class="card-header-icon">
            <i class="fa" :class="batch.isActive ? 'fa-angle-down' : 'fa-angle-right'" />
          </span>
          <p class="card-header-title">{{ $t('labels.batchUpdates') }}</p>
        </div>
      </template>
      <div class="card-content">
        <div class="field is-grouped is-grouped-multiline">
          <div class="control">
            <div class="select">
              <select v-model="batch.category">
                <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                <option v-for="category in activeCategories" :key="category.id" :value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
          <div v-if="batch.category && categoriesAndSubcategoriesLookup[batch.category].sub.length > 0" class="control">
            <div class="select">
              <select v-model="batch.subcategory">
                <option value="">-- {{ $tc('objects.subcategory', 1) }} --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[batch.category].sub" :key="subcategory.id" :value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
          </div>
          <div class="control">
            <div class="select">
              <select v-model="batch.isRecurring">
                <option :value="null">-- {{ $t('fieldnames.isRecurring') }} --</option>
                <option :value="true">{{ $t('labels.isRecurring') }}</option>
                <option :value="false">{{ $t('labels.isNotRecurring') }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field">
          <div class="control">
            <table class="table is-fullwidth">
              <thead>
                <tr>
                  <th>{{ $tc('objects.user', 1) }}</th>
                  <th>{{ $t('fieldnames.share') }}</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                <tr v-for="(share, index) in batch.shares" :key="share.user">
                  <td>
                    <div class="control">
                      <div class="select">
                        <select v-model="share.user" name="user">
                          <option v-for="holder in holders" :key="holder.id" :value="holder.id">{{ holder.name }}</option>
                        </select>
                      </div>
                    </div>
                  </td>
                  <td class="dispatch-slider">
                    <o-field grouped>
                      <o-field expanded root-class="is-expanded">
                        <o-slider v-model="share.share" :custom-formatter="val => val + '%'" />
                      </o-field>
                      <o-field>
                        <o-input v-model.number="share.share" type="number" min="0" max="100" />
                      </o-field>
                    </o-field>
                  </td>
                  <td>
                    <button class="button is-light" type="button" @click="removeShareLine(index)"><span class="icon"><i class="fas fa-trash-alt" /></span><span class="is-hidden-mobile">{{ $t('actions.delete') }}</span></button>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3">
                    <button class="button is-light" type="button" @click="addShareLine()"><span class="icon"><i class="fas fa-plus-square" /></span><span>{{ $t('actions.add') }}</span></button>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="field is-grouped is-grouped-multiline">
          <div class="control">
            <button class="button is-primary" :class="{'is-loading': batch.isLoading}" :disabled="(batch.isLoading || !isOnline)" @click="processBatchUpdate"><span class="icon"><i class="fas fa-cogs" /></span><span>{{ $t('actions.apply') }}</span></button>
          </div>
          <div class="control">
            <button class="button is-light" :disabled="batch.isLoading" @click="selectAll"><span class="icon"><i class="far fa-check-square" /></span><span>{{ $t('actions.selectAll') }}</span></button>
          </div>
          <div class="control">
            <button class="button is-light" :disabled="batch.isLoading" @click="selectNone"><span class="icon"><i class="far fa-square" /></span><span>{{ $t('actions.clear') }}</span></button>
          </div>
          <div class="control">
            <div class="input is-static">{{ $tc('objects.transactionsSelected', batch.checkedTransactions.length) }} ({{ $n(transactionsCheckedSum, 'currency') }})</div>
          </div>
        </div>
        <div class="field is-block-mobile">
          <progress class="progress" :value="batch.progress" max="1">{{ batch.progress }} %</progress>
        </div>
        <div class="field">
          <div v-if="batch.result" class="message is-danger">
            <div class="message-body">
              {{ batch.result }}
            </div>
          </div>
        </div>
      </div>
    </o-collapse>

    <o-table v-model:checked-rows="batch.checkedTransactions" table-class="mt-1" :data="displayedTransactions" :row-class="(row, index) => row.isNew ? 'has-text-weight-bold' : ''" :paginated="true" :striped="true" :hoverable="true" :loading="isLoading" default-sort="datePosted" default-sort-direction="desc" :checkable="batch.isActive" wrapper-class="table-container" @select="edit">
      <o-table-column v-if="displayAccount" v-slot="props" field="icon">
        <o-tooltip v-if="props.row.iconUrl" :label="props.row.accountLabel" variant="black">
          <img class="transaction-account-icon" :src="props.row.iconUrl" alt="" height="24" width="24">
        </o-tooltip>
      </o-table-column>
      <o-table-column v-slot="props" field="amount" :label="$t('fieldnames.amount')" sortable numeric position="right">
        <span :class="[props.row.amount < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(props.row.amount, 'currency') }}</span>
      </o-table-column>
      <o-table-column v-slot="props" field="share" :label="$t('fieldnames.share')" sortable numeric position="right">
        <span v-if="props.row.share" class="has-text-weight-light has-text-grey-dark">{{ props.row.share }}%</span><span v-else class="has-text-weight-light has-text-grey-light">{{ props.row.accountOwner === userId ? 100 : 0 }}%</span>
      </o-table-column>
      <o-table-column v-slot="props" field="name" :label="$t('fieldnames.label')" sortable>
        <o-tooltip :label="props.row.fullname" variant="black">
          <span class="has-no-wrap is-flex">
            <span class="transaction-label">{{ props.row.fullname }}</span><span v-if="props.row.note" class="has-text-grey">&nbsp;|&nbsp;{{ props.row.note }}</span>
          </span>
        </o-tooltip>
      </o-table-column>
      <o-table-column v-slot="props" field="datePosted" :label="$t('fieldnames.date')" sortable>
        {{ $dayjs(props.row.datePosted).format("L") }}
      </o-table-column>
      <o-table-column v-slot="props" field="isRecurring" :label="$t('fieldnames.isRecurring')" sortable position="centered">
        <i class="fa fa-fw" :class="[props.row.isRecurring ? 'fa-toggle-on' : 'fa-toggle-off']" />
      </o-table-column>
      <o-table-column v-slot="props" field="category" :label="$tc('objects.category', 1)" sortable>
        <span class="has-no-wrap">
          <span v-if="props.row.categoryIcon" class="icon"><i class="fa fa-fw" :class="props.row.categoryIcon" /></span>
          {{ props.row.categoryLabel }}<span v-if="props.row.subcategory"> / {{ props.row.subcategoryLabel }}</span>
        </span>
      </o-table-column>
      <template #empty>
        <section class="section">
          <div class="content has-text-grey has-text-centered">
            <p>{{ $t('labels.nothingToDisplay') }}</p>
          </div>
        </section>
      </template>
      <template #bottom-left>
        <a class="button is-light" @click="downloadData"><span class="icon"><i class="fa fa-download fa-lg" /></span><span>{{ $t('actions.download') }}</span></a>
      </template>
    </o-table>
    <o-modal v-model:active="modalTransaction.isActive" has-modal-card scroll="keep">
      <transaction v-if="modalTransaction.isActive" :transaction="modalTransaction.transaction" :url="url" @close="modalTransaction.isActive = false" @updated="update" />
    </o-modal>
  </div>
</template>

<script>
import Transaction from '@/components/Transaction.vue'
import { useStore } from '@/store'
import { mapState } from 'pinia'
import exportFromJSON from 'export-from-json'
import Config from '@/services/Config'
import Auth from '@/services/Auth'

export default {
  name: 'Transactions',
  components: {
    Transaction,
  },
  props: {
    url: {
      required: true,
      type: String,
    },
    displayAccount: {
      required: false,
      type: Boolean,
      default: false,
    },
    accountId: {
      required: false,
      type: Number,
      default: null,
    },
    duration: {
      required: false,
      type: String,
      default: 'P3M',
    },
  },
  data () {
    const today = new Date()
    today.setHours(0, 0, 0)
    today.setMilliseconds(0)
    return {
      transactions: [],
      isLoading: false,
      batch: {
        isActive: false,
        isLoading: false,
        progress: 0,
        result: '',
        checkedTransactions: [],
        isRecurring: null,
        category: '',
        subcategory: '',
        shares: [],
      },
      userId: Auth.getProfile().id,
      // filter
      search: {
        isActive: false,
        query: '',
        currentDate: today,
        periodStart: this.$dayjs(today).subtract(this.$dayjs.duration(this.duration)).toDate(),
        periodEnd: today,
        category: '',
        subcategory: '',
      },
      // modal
      modalTransaction: {
        isActive: false,
        transaction: {},
      },
    }
  },
  computed: {
    displayedTransactions () {
      const query = this.search.query
      const periodStart = this.search.periodStart
      const periodEnd = this.search.periodEnd
      const category = this.search.category
      const subcategory = this.search.subcategory
      const transactions = this.transactions
      transactions.map((t) => {
        t.categoryLabel = (t.category in this.categoriesAndSubcategoriesLookup) ? this.categoriesAndSubcategoriesLookup[t.category].label : null
        t.categoryIcon = (t.category in this.categoriesAndSubcategoriesLookup) ? this.categoriesAndSubcategoriesLookup[t.category].icon : null
        t.subcategoryLabel = (t.subcategory in this.categoriesAndSubcategoriesLookup) ? this.categoriesAndSubcategoriesLookup[t.subcategory].label : null
        t.fullname = (t.memo) ? t.memo + ' ' + t.name : t.name
        return t
      })
      return transactions.filter(function (transaction) {
        return (transaction.note + transaction.fullname).toLowerCase().indexOf(query.toLowerCase()) > -1 &
        new Date(Date.parse(transaction.datePosted)) >= periodStart &
        new Date(Date.parse(transaction.datePosted)) <= periodEnd &
        (category === '' || transaction.category === category) &
        (subcategory === '' || transaction.subcategory === subcategory)
      })
    },
    transactionsCheckedSum () {
      return this.batch.checkedTransactions.length > 0 ? this.batch.checkedTransactions.reduce((sum, transaction) => sum + transaction.amount, 0) : 0
    },
    sharesSum () {
      return this.batch.shares.filter(share => share.user !== null).reduce((acc, item) => acc + item.share, 0)
    },
    ...mapState(useStore, ['isOnline', 'activeCategories', 'categories', 'categoriesAndSubcategoriesLookup', 'holders', 'holderId']),
  },
  watch: {
    'search.category' () {
      // clear subcategory search field if category has changed
      this.search.subcategory = ''
    },
    duration () {
      const today = new Date()
      today.setHours(0, 0, 0)
      today.setMilliseconds(0)
      this.search.periodStart = this.$dayjs(today).subtract(this.$dayjs.duration(this.duration)).toDate()
    },
  },
  async mounted () {
    await this.get()
    this.$bus.on('transactions-updated', () => {
      this.get()
    })
    this.$bus.on('transactions-date-filtered', this.handleTransactionsDateFiltered)
    this.addShareLine({ user: this.holderId, share: 100 })
  },
  beforeUnmount () {
    // remove events listener
    this.$bus.off('transactions-updated')
    this.$bus.off('transactions-date-filtered', this.handleTransactionsDateFiltered)
  },
  methods: {
    // get transactions
    async get () {
      if (this.isLoading) {
        // exit if already loading
        return
      }
      this.isLoading = true
      const config = {
        params: {
          periodStart: this.$dayjs(this.search.periodStart).format('X'),
          periodEnd: this.$dayjs(this.search.periodEnd).format('X'),
        },
        headers: {
        },
      }
      // try to get last fetch date for highlighting new transactions
      if (this.accountId) {
        const transactionsLastFetchDate = sessionStorage.getItem('accounts:' + this.accountId + ':transactions:lastFetch')
        if (transactionsLastFetchDate) {
          config.headers = { 'Omm-Last-Fetch': transactionsLastFetchDate }
        }
      }
      try {
        const response = await this.$http.get(this.url, config)
        this.transactions = response.data.map((transaction) => {
          transaction.iconUrl = transaction.iconUrl ? Config.API_URL + transaction.iconUrl : null
          return transaction
        })
      } catch (error) {
        // @TODO : add error handling
        console.error(error)
      }
      this.isLoading = false
    },
    // edit transaction in modal form
    edit (item) {
      if (!this.batch.isActive) {
        this.modalTransaction.transaction = item
        this.modalTransaction.isActive = true
      } else {
        // batch edition mode, just (un)check the transaction
        const index = this.batch.checkedTransactions.indexOf(item)
        if (index > -1) {
          this.batch.checkedTransactions.splice(index, 1)
        } else {
          this.batch.checkedTransactions.push(item)
        }
      }
    },
    update (transaction) {
      const index = this.transactions.findIndex(t => t.id === transaction.id)
      if (index !== -1) {
        this.transactions[index] = { ...this.transactions[index], ...transaction }
        return
      }
      this.get()
    },
    downloadData () {
      const transactions = JSON.parse(JSON.stringify(this.displayedTransactions)).map((t) => {
        t.amount = this.$n(t.amount, { style: 'decimal', useGrouping: false })
        t.datePosted = this.$dayjs(t.datePosted).format('L')
        t.dateUser = this.$dayjs(t.dateUser).format('L')
        delete (t.id)
        delete (t.fitid)
        delete (t.type)
        delete (t.category)
        delete (t.subcategory)
        delete (t.categoryIcon)
        delete (t.iconUrl)
        delete (t.accountOwner)
        delete (t.fullname)
        for (var property in t) {
          if (t[property] === null) {
            t[property] = ''
          }
        }
        return t
      })
      exportFromJSON({
        data: transactions,
        fileName: 'transactions',
        exportType: exportFromJSON.types.csv,
        withBOM: true,
        beforeTableEncode: entries => entries.map(({ fieldName, fieldValues }) => ({ fieldName: this.formatTitle(fieldName), fieldValues })),
      })
    },
    formatTitle(name) {
      switch (name) {
      case 'datePosted':
        return this.$t('fieldnames.date')
      case 'dateUser':
        return this.$t('fieldnames.dateUser')
      case 'amount':
        return this.$t('fieldnames.amount')
      case 'name':
        return this.$t('fieldnames.label')
      case 'memo':
        return this.$t('fieldnames.memo')
      case 'note':
        return this.$t('fieldnames.note')
      case 'isRecurring':
        return this.$t('fieldnames.isRecurring')
      case 'accountLabel':
        return this.$t('objects.account')
      case 'share':
        return this.$t('fieldnames.share')
      case 'categoryLabel':
        return this.$t('objects.category')
      case 'subcategoryLabel':
        return this.$t('objects.subcategory')
      default:
        return name
      }
    },
    async processBatchUpdate () {
      const length = this.batch.checkedTransactions.length
      if (length > 0) {
        this.batch.isLoading = true
        this.batch.progress = 0
        let processed = 0
        const category = this.batch.category
        const subcategory = this.batch.subcategory
        const isRecurring = this.batch.isRecurring
        const shares = this.batch.shares.filter(share => share.user !== null)
        this.batch.result = ''
        for (const transaction of this.batch.checkedTransactions) {
          if (category) {
            transaction.category = category
            transaction.subcategory = subcategory
          }
          if (isRecurring !== null) {
            transaction.isRecurring = isRecurring
          }
          if (shares.length) {
            // check shares sum
            if (this.sharesSum !== 100) {
              this.batch.result = this.$t('labels.invalidDispatch')
              this.batch.isLoading = false
              return
            }
            // check duplicates
            const sharesByUser = shares.reduce(function (acc, item) {
              if (typeof acc[item.user] === 'undefined') {
                acc[item.user] = 0
              }
              acc[item.user]++
              return acc
            }, [])
            if (sharesByUser.some(share => share > 1)) {
              this.batch.result = this.$t('labels.duplicatesShares')
              this.batch.isLoading = false
              return
            }
            transaction.shares = shares
          }
          try {
            const response = await this.$http.put(`${this.url}/${transaction.id}`, transaction)
            transaction.share = response.data.share
          } catch (error) {
            this.batch.result += `${transaction.id} : ${error.message}. `
          }
          processed++
          this.batch.progress = processed / length
          if (processed === length) {
            this.batch.isLoading = false
          }
        }
      }
    },
    selectAll () {
      this.batch.checkedTransactions = this.displayedTransactions.slice()
    },
    selectNone () {
      this.batch.checkedTransactions = []
    },
    addShareLine (share) {
      if (!share) {
        share = { user: null, share: 100 - this.sharesSum }
      }
      this.batch.shares.push(share)
    },
    removeShareLine (index) {
      this.batch.shares.splice(index, 1)
    },
    handleTransactionsDateFiltered (search) {
      if ((this.search.periodStart.getTime() !== search.periodStart.getTime()) || (this.search.periodEnd.getTime() !== search.periodEnd.getTime())) {
        this.search.periodStart = search.periodStart
        this.search.periodEnd = search.periodEnd
      }
    },
  },
}
</script>
