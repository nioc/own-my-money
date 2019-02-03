<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          { link: '/', icon: 'fa-home', text: this.$t('labels.home'), isActive: true }
        ]">
      </breadcrumb>
    </div>
    <div class="hero-body">
      <div class="section no-padding-parent-mobile">
        <div class="container">
          <div class="columns no-padding-parent-mobile is-multiline">
            <div class="column no-padding-mobile is-full">
              <transactions-history-chart
              :title="$t('labels.transactionsByDay')"
              chartEndpoint="transactions/history"
              :isIndependent="true"
              :date="date"
              ></transactions-history-chart>
            </div>
            <div class="column no-padding-mobile is-one-half-desktop">
              <transactions-distribution-chart
              :title="$t('labels.incomeDistribution')"
              chartEndpoint="transactions/distribution/credit/categories"
              :isIndependent="false"
              :date="date"
              ></transactions-distribution-chart>
            </div>
            <div class="column no-padding-mobile is-one-half-desktop">
              <transactions-distribution-chart
              :title="$t('labels.expenseDistribution')"
              chartEndpoint="transactions/distribution/debit/categories"
              :isIndependent="false"
              :date="date"
              ></transactions-distribution-chart>
            </div>
            <div v-if="categorySelected.key" class="column no-padding-mobile is-full">
              <transactions-history-chart
              :title="$t('labels.transactionsByDay') + ' ' + $t('labels.for') + ' ' + categorySelected.label"
              :chartEndpoint="'transactions/history?category='+categorySelected.key"
              :isIndependent="false"
              :date="date"
              ></transactions-history-chart>
            </div>
            <div v-if="categorySelected.key" class="column no-padding-mobile is-one-half-desktop">
              <transactions-distribution-chart
              :title="$t('labels.incomeDistribution') + ' ' + $t('labels.for') + ' ' + categorySelected.label"
              :chartEndpoint="'transactions/distribution/credit/subcategories?value='+categorySelected.key"
              :isIndependent="false"
              :date="date"
              ></transactions-distribution-chart>
            </div>
            <div v-if="categorySelected.key" class="column no-padding-mobile is-one-half-desktop">
              <transactions-distribution-chart
              :title="$t('labels.expenseDistribution') + ' ' + $t('labels.for') + ' ' + categorySelected.label"
              :chartEndpoint="'transactions/distribution/debit/subcategories?value='+categorySelected.key"
              :isIndependent="false"
              :date="date"
              ></transactions-distribution-chart>
            </div>
            <div v-if="subcategorySelected.key" class="column no-padding-mobile is-full">
              <transactions-history-chart
              :title="$t('labels.transactionsByDay') + ' ' + $t('labels.for') + ' ' + subcategorySelected.label"
              :chartEndpoint="'transactions/history?subcategory='+subcategorySelected.key"
              :isIndependent="false"
              :date="date"
              ></transactions-history-chart>
            </div>
          </div>
        </div>
      </div>
      <div class="container box">
        <h1 class="title">{{ $tc('objects.transaction', 2) }}</h1>
        <transactions v-bind:url="url"/>
      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
import Transactions from '@/components/Transactions'
import TransactionsDistributionChart from '@/components/TransactionsDistributionChart'
import TransactionsHistoryChart from '@/components/TransactionsHistoryChart'
import Bus from '@/services/Bus'
export default {
  name: 'home',
  components: {
    Breadcrumb,
    TransactionsDistributionChart,
    TransactionsHistoryChart,
    Transactions
  },
  data () {
    const today = new Date()
    today.setHours(0, 0, 0)
    return {
      categorySelected: { key: null },
      subcategorySelected: { key: null },
      url: Config.API_URL + 'transactions{/id}',
      date: {
        timeUnit: '',
        periodStart: this.$moment(today).subtract(this.$moment.duration('P3M')).toDate(),
        periodEnd: today
      }
    }
  },
  mounted () {
    Bus.$on('category-selected', (category) => {
      if (!this.categorySelected.key || this.categorySelected.key !== category.key) {
        // clear previous subcategory pies
        this.categorySelected.key = null
        this.$nextTick(function () {
          // set category after next DOM update cycle
          this.categorySelected = category
        })
      }
    })
    Bus.$on('subcategory-selected', (subcategory) => {
      if (!this.subcategorySelected.key || this.subcategorySelected.key !== subcategory.key) {
        // clear previous subcategory history
        this.subcategorySelected.key = null
        this.$nextTick(function () {
          // set subcategory after next DOM update cycle
          this.subcategorySelected = subcategory
        })
      }
    })
    Bus.$on('transactions-date-filtered', (search) => {
      if ((this.date.periodStart.getTime() !== search.periodStart.getTime()) || (this.date.periodEnd.getTime() !== search.periodEnd.getTime()) || (this.date.timeUnit !== search.timeUnit)) {
        this.date.periodStart = search.periodStart
        this.date.periodEnd = search.periodEnd
        if (search.timeUnit) {
          this.date.timeUnit = search.timeUnit
        }
      }
    })
  },
  beforeDestroy () {
    // remove events listener
    Bus.$off('category-selected')
    Bus.$off('transactions-date-filtered')
  }
}
</script>

<style>
  .section {
    padding-top: 0;
  }
</style>
