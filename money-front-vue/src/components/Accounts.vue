<template>
  <section class="hero">
    <div class="hero-head container">
      <h1 class="title">Accounts</h1>
    </div>
    <div class="hero-body">
      <div class="container">
        <div class="box">
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
          <form @submit.prevent="validateBeforeSubmit" novalidate>
            <div class="field is-grouped">
              <div class="control">
                <input class="input" type="text" name="bankId" placeholder="Bank Id" v-model="createAccount.bankId" v-validate="'required|alpha_num'" :class="{'input': true, 'is-danger': errors.has('bankId') }">
                <span v-show="errors.has('bankId')" class="help is-danger">{{errors.first('bankId')}}</span>
              </div>
              <div class="control">
                <input class="input" type="text" name="BranchId" placeholder="Branch Id" v-model="createAccount.branchId" v-validate="'required|alpha_num'" :class="{'input': true, 'is-danger': errors.has('BranchId') }">
                <span v-show="errors.has('BranchId')" class="help is-danger">{{errors.first('BranchId')}}</span>
              </div>
              <div class="control">
                <input class="input" type="text" name="AccountId" placeholder="Account Id" v-model="createAccount.accountId" v-validate="'required|alpha_num'" :class="{'input': true, 'is-danger': errors.has('AccountId') }">
                <span v-show="errors.has('AccountId')" class="help is-danger">{{errors.first('AccountId')}}</span>
              </div>
              <button type="submit" class="button is-primary" role="button"><i class="fa fa-plus"/>&nbsp;Add an account</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="hero-foot">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: 'Home'},
          {link: '/accounts', text: 'Accounts', isActive: true}
        ]">
      </breadcrumb>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
export default {
  name: 'accounts',
  components: {
    Breadcrumb
  },
  data () {
    return {
      accounts: [],
      createAccount: {},
      error: '',
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
    },
    validateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          // if validation is ok, call accounts API
          this.rAccounts.save(this.createAccount)
            .then(response => {
              this.getAccounts()
            }, response => {
              if (response.body.message) {
                this.error = response.body.message
                return
              }
              this.error = response.status + ' - ' + response.statusText
            })
        }
      })
    }
  },
  mounted: function () {
    this.getAccounts()
  }
}
</script>
