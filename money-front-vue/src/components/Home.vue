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
export default {
  name: 'home',
  components: {
    Breadcrumb,
    Transactions
  },
  data () {
    return {
      url: Config.API_URL + 'transactions{/id}'
    }
  }
}
</script>
