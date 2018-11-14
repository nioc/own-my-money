<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          { link: '/', icon: 'fa-home', text: 'Home', isActive: true }
        ]">
      </breadcrumb>
    </div>
    <div class="hero-body">
      <div class="section">
        <div class="container">
          <div class="columns is-multiline">
            <div class="column is-full"><transactions-history-chart title="Transactions by day" chartEndpoint="transactions/history"></transactions-history-chart></div>
            <div class="column is-one-half-desktop"><transactions-distribution-chart title="Credit by categories" chartEndpoint="transactions/distribution/credit/categories"></transactions-distribution-chart></div>
            <div class="column is-one-half-desktop"><transactions-distribution-chart title="Debit by categories" chartEndpoint="transactions/distribution/debit/categories"></transactions-distribution-chart></div>
          </div>
        </div>
      </div>
      <div class="container box">
        <h1 class="title">Transactions</h1>
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
export default {
  name: 'home',
  components: {
    Breadcrumb,
    TransactionsDistributionChart,
    TransactionsHistoryChart,
    Transactions
  },
  data () {
    return {
      url: Config.API_URL + 'transactions{/id}'
    }
  }
}
</script>
