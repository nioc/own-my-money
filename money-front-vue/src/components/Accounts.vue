<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: 'Home'},
          {link: '/accounts', text: 'Accounts', isActive: true}
        ]">
      </breadcrumb>
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">Accounts</h1>
        <div class="message is-danger" v-if="error">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
        <div class="table-container" v-if="accounts.length">
          <table class="table is-striped is-hoverable is-fullwidth">
            <thead>
              <tr>
                <th>Account</th>
                <th>Balance</th>
                <th>Updated</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="account in accounts" :key="account.id">
                <td><router-link :to="{ name: 'account', params: { id: account.id }}">{{ account.bankId }} {{ account.branchId }} {{ account.accountId }}</router-link></td>
                <td>{{ account.balance | currency }}</td>
                <td>{{ account.lastUpdate | moment("DD/MM/YYYY HH:mm") }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else>
          <p>There is no account yet</p>
        </div>
        <button class="button is-primary" role="button" @click="isCreateAccountModalActive = true"><i class="fa fa-plus"/>&nbsp;Add account</button>
        <b-modal :active.sync="isCreateAccountModalActive" has-modal-card>
          <create-account></create-account>
        </b-modal>
      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
import CreateAccount from '@/components/CreateAccount'
export default {
  name: 'accounts',
  components: {
    Breadcrumb,
    CreateAccount
  },
  data () {
    return {
      accounts: [],
      error: '',
      isCreateAccountModalActive: false,
      // resources
      rAccounts: this.$resource(Config.API_URL + 'accounts{/id}')
    }
  },
  methods: {
    getAccounts () {
      this.rAccounts.query()
        .then(response => {
          this.accounts = response.body
        }, response => {
          if (response.body.message) {
            this.error = response.body.message
            return
          }
          this.error = response.status + ' - ' + response.statusText
        })
    }
  },
  mounted: function () {
    this.getAccounts()
  }
}
</script>
