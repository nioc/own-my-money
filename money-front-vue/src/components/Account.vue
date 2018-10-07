<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          { link: '/', icon: 'fa-home', text: 'Home' },
          { link: '/accounts', text: 'Accounts' },
          { link: '/accounts', text: accountTitle, isActive: true }
        ]">
      </breadcrumb>
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">Account {{ accountTitle }}</h1>
        <b-tabs type="is-boxed" :animated="false">
          <b-tab-item label="Transactions" icon="file-text-o">
            <div class="field is-grouped is-grouped-multiline">
              <div class="field has-addons">
                <p class="control has-icons-left">
                  <input class="input" type="text" placeholder="Find a transaction" v-model="query">
                  <span class="icon is-small is-left">
                    <i class="fa fa-search"></i>
                  </span>
                </p>
                <p class="control">
                  <a class="button is-primary">Search</a>
                </p>
              </div>
              <div class="field">
                <p class="control">
                  <b-datepicker placeholder="Start date" icon="calendar" :readonly="false" :max-date="currentDate" v-model="startDate"></b-datepicker>
                </p>
              </div>
              <div class="field">
                <p class="control">
                  <b-datepicker placeholder="End date" icon="calendar" :readonly="false" :max-date="currentDate" v-model="endDate"></b-datepicker>
                </p>
              </div>
            </div>
            <b-table :data=displayedTransactions :paginated="true" :striped="true" :hoverable="true" :loading="isLoading" default-sort="dateUser" default-sort-direction="desc">
              <template slot-scope="props">
                <b-table-column field="amount" label="Amount" sortable numeric>
                  {{ props.row.amount | currency }}
                </b-table-column>
                <b-table-column field="name" label="Name" sortable>
                  {{ props.row.name }}
                </b-table-column>
                <b-table-column field="dateUser" label="Date" sortable>
                  {{ props.row.dateUser | moment("DD/MM/YYYY") }}
                </b-table-column>
                <b-table-column field="category" label="Category" sortable>
                  {{ props.row.category }}
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
          </b-tab-item>
          <b-tab-item label="Edit" icon="pencil">
            <form @submit.prevent="validateUpdateBeforeSubmit" novalidate class="section is-400px-form">
              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">Bank identifier</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <input type="text" class="input" name="bankId" placeholder="Bank Id" v-model="updatedAccount.bankId" v-validate="'required|alpha_num'" data-vv-as="bank id" :class="{ 'is-danger': errors.has('bankId') }">
                      <span v-show="errors.has('bankId')" class="help is-danger">{{ errors.first('bankId') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">Bank identifier</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <p class="control">
                      <input type="text" class="input" name="BranchId" placeholder="Branch Id" v-model="updatedAccount.branchId" v-validate="'required|alpha_num'" data-vv-as="branch id" :class="{ 'is-danger': errors.has('BranchId') }">
                      <span v-show="errors.has('BranchId')" class="help is-danger">{{ errors.first('BranchId') }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">Account identifier</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <input type="text" class="input" name="AccountId" placeholder="Account Id" v-model="updatedAccount.accountId" v-validate="'required|alpha_num'" data-vv-as="account id" :class="{ 'is-danger': errors.has('AccountId') }">
                      <p v-show="errors.has('AccountId')" class="help is-danger">{{ errors.first('AccountId') }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">Label</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <input type="text" class="input" name="label" placeholder="Label of your choice" v-model="updatedAccount.label" v-validate="'max:30'" :class="{'is-danger': errors.has('label') }">
                      <p v-show="errors.has('label')" class="help is-danger">{{ errors.first('label') }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                </div>
                <div class="field-body">
                  <div class="field">
                    <button type="submit" class="button is-primary" role="button"><i class="fa fa-save"/>&nbsp;Save</button>
                    <button type="button" class="button is-danger" role="button" v-on:click="deleteAccount"><i class="fa fa-trash"/>&nbsp;Delete</button>
                  </div>
                </div>
              </div>
            </form>
            <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>
          </b-tab-item>
        </b-tabs>
      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
export default {
  name: 'account',
  components: {
    Breadcrumb
  },
  data () {
    const today = new Date()
    return {
      // account
      account: {
        id: parseInt(this.$route.params.id),
        bankId: '',
        branchId: '',
        accountId: '',
        label: '',
        balance: 0,
        transactions: []
      },
      isLoading: false,
      updatedAccount: {
      },
      // filter
      query: '',
      currentDate: today,
      startDate: new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30),
      endDate: today,
      transactionModal: {
        transactionEdited: {category: {id: '', label: ''}, subcategory: {id: '', label: ''}},
        transactionBackup: {},
        savePattern: false,
        pattern: '',
        updating: false,
        result: ''
      },
      // categories
      categories: [],
      subcategories: [],
      categoriesAndSubcategoriesLookup: {},
      // resources
      rAccounts: this.$resource(Config.API_URL + 'accounts{/id}'),
      rTransactions: this.$resource(Config.API_URL + 'accounts/{aid}/transactions{/id}')
    }
  },
  computed: {
    displayedTransactions: function () {
      let query = this.query
      let startDate = this.startDate
      let endDate = this.endDate
      return this.account.transactions.filter(function (transaction) {
        return transaction.name.toLowerCase().indexOf(query.toLowerCase()) > -1 &
        new Date(Date.parse(transaction.dateUser)) >= startDate &
        new Date(Date.parse(transaction.dateUser)) <= endDate
      })
    },
    accountTitle: function () {
      if (this.account.label) {
        return this.account.bankId + ' ' + this.account.branchId + ' ' + this.account.accountId + ' (' + this.account.label + ')'
      }
      return this.account.bankId + ' ' + this.account.branchId + ' ' + this.account.accountId
    }
  },
  methods: {
    // get account informations
    get () {
      this.rAccounts.get({id: this.account.id}).then(response => {
        this.account.bankId = response.body.bankId
        this.account.branchId = response.body.branchId
        this.account.accountId = response.body.accountId
        this.account.balance = response.body.balance
        this.account.label = response.body.label
        this.updatedAccount = JSON.parse(JSON.stringify(this.account))
        delete (this.updatedAccount.transactions)
      }, response => {
        if (response.status === 403 || response.status === 404) {
          // user does not can access this account, return to home
          this.$router.replace({name: 'home'})
          return
        }
        // @TODO : add error handling
        console.error(response)
      })
    },
    // get account transactions
    getTransactions () {
      this.isLoading = true
      this.rTransactions.query({aid: this.account.id}).then(response => {
        this.account.transactions = response.body
      }, response => {
        // @TODO : add error handling
        console.error(response)
      }).finally(function () {
        // remove loading overlay when API replies
        this.isLoading = false
      })
    },
    validateUpdateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          this.isLoading = true
          // if validation is ok, call accounts API
          this.rAccounts.update({id: this.account.id}, this.updatedAccount)
            .then(response => {
              // this.getAccounts()
              this.account.bankId = response.body.bankId
              this.account.branchId = response.body.branchId
              this.account.accountId = response.body.accountId
              this.account.balance = response.body.balance
              this.account.label = response.body.label
            }, response => {
              if (response.body.message) {
                this.error = response.body.message
                return
              }
              this.error = response.status + ' - ' + response.statusText
            })
            .finally(function () {
              // remove loading overlay when API replies
              this.isLoading = false
            })
        }
      })
    },
    deleteAccount () {
      this.$dialog.confirm({
        message: 'Are you sure you want to delete this account?<br>All transactions will be deleted too.',
        title: 'Deleting account',
        type: 'is-danger',
        hasIcon: true,
        icon: 'trash',
        confirmText: 'Delete account',
        focusOn: 'cancel',
        onConfirm: () => {
          this.isLoading = true
          this.rAccounts.delete({id: this.account.id})
            .then(response => {
              this.$router.replace({name: 'accounts'})
            }, response => {
              // @TODO : add error handling
              console.error(response)
            })
            .finally(function () {
              // remove loading overlay when API replies
              this.isLoading = false
            })
        }
      })
    }
  },
  mounted: function () {
    this.get()
    this.getTransactions()
  }
}
</script>
