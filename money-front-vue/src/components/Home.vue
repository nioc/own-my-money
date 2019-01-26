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
      <div class="section">
        <div class="container">
          <div class="columns is-multiline">
            <div class="column is-full">
              <transactions-history-chart
              :title="$t('labels.transactionsByDay')"
              chartEndpoint="transactions/history"
              :isIndependent="true"
              :date="date"
              ></transactions-history-chart>
            </div>
            <div class="column is-one-half-desktop">
              <transactions-distribution-chart
              :title="$t('labels.incomeDistribution')"
              chartEndpoint="transactions/distribution/credit/categories"
              :isIndependent="false"
              :date="date"
              ></transactions-distribution-chart>
            </div>
            <div class="column is-one-half-desktop">
              <transactions-distribution-chart
              :title="$t('labels.expenseDistribution')"
              chartEndpoint="transactions/distribution/debit/categories"
              :isIndependent="false"
              :date="date"
              ></transactions-distribution-chart>
            </div>
            <div v-if="categorySelected.key" class="column is-one-half-desktop">
              <transactions-distribution-chart
              :title="$t('labels.incomeDistribution') + ' ' + $t('labels.for') + ' ' + categorySelected.label"
              :chartEndpoint="'transactions/distribution/credit/subcategories?value='+categorySelected.key"
              :isIndependent="false"
              :date="date"
              ></transactions-distribution-chart>
            </div>
            <div v-if="categorySelected.key" class="column is-one-half-desktop">
              <transactions-distribution-chart
              :title="$t('labels.expenseDistribution') + ' ' + $t('labels.for') + ' ' + categorySelected.label"
              :chartEndpoint="'transactions/distribution/debit/subcategories?value='+categorySelected.key"
              :isIndependent="false"
              :date="date"
              ></transactions-distribution-chart>
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
      url: Config.API_URL + 'transactions{/id}',
      date: {
        periodStart: new Date(today.getFullYear(), today.getMonth(), today.getDate() - 90),
        periodEnd: today
      }
    }
  },
  mounted: function () {
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
  },
  beforeDestroy () {
    // remove date filter event listener
    Bus.$off('transactions-date-filtered')
  }
}
</script>
