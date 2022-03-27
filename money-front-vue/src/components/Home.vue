<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fas fa-home', text: $t('labels.home'), isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="section no-padding-parent-mobile">
        <div class="container">
          <div class="columns no-padding-parent-mobile is-multiline">
            <div class="column no-padding-mobile is-full">
              <transactions-history-chart
                :title="$t('labels.transactionsByDay')"
                chart-endpoint="transactions/history"
                :is-independent="true"
                :is-closable="false"
                :date="date"
              />
            </div>
            <div class="column no-padding-mobile is-one-half-desktop">
              <transactions-distribution-chart
                :title="$t('labels.incomeDistribution')"
                chart-endpoint="transactions/distribution/credit/categories"
                :is-independent="false"
                :is-closable="false"
                :date="date"
              />
            </div>
            <div class="column no-padding-mobile is-one-half-desktop">
              <transactions-distribution-chart
                :title="$t('labels.expenseDistribution')"
                chart-endpoint="transactions/distribution/debit/categories"
                :is-independent="false"
                :is-closable="false"
                :date="date"
              />
            </div>
            <div v-if="categorySelected.key" class="column no-padding-mobile is-full">
              <transactions-history-chart
                :title="$t('labels.transactionsByDay') + ' ' + $t('labels.for') + ' ' + categorySelected.label"
                :chart-endpoint="'transactions/history?category='+categorySelected.key"
                :is-independent="false"
                :is-closable="true"
                :date="date"
              />
            </div>
            <div v-if="categorySelected.key" class="column no-padding-mobile is-one-half-desktop">
              <transactions-distribution-chart
                :title="$t('labels.incomeDistribution') + ' ' + $t('labels.for') + ' ' + categorySelected.label"
                :chart-endpoint="'transactions/distribution/credit/subcategories?value='+categorySelected.key"
                :is-independent="false"
                :is-closable="true"
                :date="date"
              />
            </div>
            <div v-if="categorySelected.key" class="column no-padding-mobile is-one-half-desktop">
              <transactions-distribution-chart
                :title="$t('labels.expenseDistribution') + ' ' + $t('labels.for') + ' ' + categorySelected.label"
                :chart-endpoint="'transactions/distribution/debit/subcategories?value='+categorySelected.key"
                :is-independent="false"
                :is-closable="true"
                :date="date"
              />
            </div>
            <div v-if="subcategorySelected.key" class="column no-padding-mobile is-full">
              <transactions-history-chart
                :title="$t('labels.transactionsByDay') + ' ' + $t('labels.for') + ' ' + subcategorySelected.label"
                :chart-endpoint="'transactions/history?subcategory='+subcategorySelected.key"
                :is-independent="false"
                :is-closable="true"
                :date="date"
              />
            </div>
          </div>
        </div>
      </div>
      <div class="container box">
        <h1 class="title">{{ $tc('objects.transaction', 2) }}</h1>
        <transactions url="transactions" :display-account="true" />
      </div>
    </div>
  </section>
</template>

<script>
import Breadcrumb from '@/components/Breadcrumb.vue'
import Transactions from '@/components/Transactions.vue'
import TransactionsDistributionChart from '@/components/TransactionsDistributionChart.vue'
import TransactionsHistoryChart from '@/components/TransactionsHistoryChart.vue'

export default {
  name: 'Home',
  components: {
    Breadcrumb,
    TransactionsDistributionChart,
    TransactionsHistoryChart,
    Transactions,
  },
  data () {
    const today = new Date()
    today.setHours(0, 0, 0)
    today.setMilliseconds(0)
    return {
      categorySelected: { key: null },
      subcategorySelected: { key: null },
      date: {
        timeUnit: '',
        duration: 'P3M',
        periodStart: this.$dayjs(today).subtract(this.$dayjs.duration('P3M')).toDate(),
        periodEnd: today,
      },
    }
  },
  mounted () {
    this.$bus.on('category-selected', (category) => {
      if (!this.categorySelected.key || this.categorySelected.key !== category.key) {
        // clear previous subcategory pies
        this.categorySelected.key = null
        // clear previous subcategory history
        this.subcategorySelected.key = null
        this.$nextTick(function () {
          // set category after next DOM update cycle
          this.categorySelected = category
        })
      }
    })
    this.$bus.on('subcategory-selected', (subcategory) => {
      if (!this.subcategorySelected.key || this.subcategorySelected.key !== subcategory.key) {
        // clear previous subcategory history
        this.subcategorySelected.key = null
        this.$nextTick(function () {
          // set subcategory after next DOM update cycle
          this.subcategorySelected = subcategory
        })
      }
    })
    this.$bus.on('transactions-date-filtered', this.handleTransactionsDateFilteredHome)
  },
  beforeUnmount () {
    // remove events listener
    this.$bus.off('category-selected')
    this.$bus.off('subcategory-selected')
    this.$bus.off('transactions-date-filtered', this.handleTransactionsDateFilteredHome)
  },
  methods: {
    handleTransactionsDateFilteredHome (search) {
      if ((this.date.periodStart.getTime() !== search.periodStart.getTime()) || (this.date.periodEnd.getTime() !== search.periodEnd.getTime()) || (this.date.timeUnit !== search.timeUnit)) {
        this.date.periodStart = search.periodStart
        this.date.periodEnd = search.periodEnd
        if (search.timeUnit) {
          this.date.timeUnit = search.timeUnit
        }
        if (search.duration) {
          this.date.duration = search.duration
        }
      }
    },
  },
}
</script>

<style>
  .section {
    padding-top: 0;
  }
</style>
