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
                  <input class="input" type="text" placeholder="Find a transaction" v-model="search.query">
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
                  <b-datepicker placeholder="Start date" icon="calendar" :readonly="false" :max-date="search.currentDate" v-model="search.startDate"></b-datepicker>
                </p>
              </div>
              <div class="field">
                <p class="control">
                  <b-datepicker placeholder="End date" icon="calendar" :readonly="false" :max-date="search.currentDate" v-model="search.endDate"></b-datepicker>
                </p>
              </div>
              <div class="field">
                <div class="control">
                  <div class="select">
                    <select name="parent" v-model="search.category">
                      <option value="">-- Category --</option>
                      <option v-for="category in categories" :key="category.id" v-bind:value="category.id">{{ category.label }}</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="field" v-if="search.category && categoriesAndSubcategoriesLookup[search.category].sub.length > 0">
                <div class="control">
                  <div class="select">
                    <select name="parent" v-model="search.subcategory">
                      <option value="">-- Subcategory --</option>
                      <option v-for="subcategory in categoriesAndSubcategoriesLookup[search.category].sub" :key="subcategory.id" v-bind:value="subcategory.id">{{ subcategory.label }}</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <b-table :data=displayedTransactions :paginated="true" :striped="true" :hoverable="true" :loading="isLoading" default-sort="dateUser" default-sort-direction="desc" selectable @select="editTransaction">
              <template slot-scope="props">
                <b-table-column field="amount" label="Amount" sortable numeric>
                  <span :class="[props.row.amount < 0 ? 'has-text-danger' : 'has-text-success']">{{ props.row.amount | currency }}</span>
                </b-table-column>
                <b-table-column field="name" label="Name" sortable>
                  {{ props.row.fullname }}<span class="has-text-grey" v-if="props.row.note"> | {{ props.row.note }}</span>
                </b-table-column>
                <b-table-column field="dateUser" label="Date" sortable>
                  {{ props.row.dateUser | moment("DD/MM/YYYY") }}
                </b-table-column>
                <b-table-column field="category" label="Category" sortable>
                  {{ props.row.categoryLabel }}<span v-if="props.row.subcategory"> / {{ props.row.subcategoryLabel }}</span>
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
            <div class="field">
              <p class="control">
                <b-field class="file">
                  <b-upload v-model="upload.files" @input="uploadDataset" :disabled="upload.isUploading">
                    <a class="button is-primary">
                      <b-icon icon="upload"></b-icon>
                      <span>Upload OFX</span>
                    </a>
                  </b-upload>
                  <span class="file-name"
                    v-if="upload.files && upload.files.length">
                    {{ upload.files[0].name }} ({{ upload.files[0].size }} bytes)
                  </span>
                </b-field>
              </p>
            </div>
            <div class="field is-horizontal" >
              <div class="field-body">
                <div class="message is-danger"  v-if="upload.result">
                  <div class="message-body">
                    {{ upload.result }}
                  </div>
                </div>
              </div>
            </div>
            <b-modal :active.sync="modalTransaction.isActive" has-modal-card>
              <transaction v-bind="modalTransaction"></transaction>
            </b-modal>
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
import Transaction from '@/components/Transaction'
export default {
  name: 'account',
  components: {
    Breadcrumb,
    Transaction
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
      search: {
        query: '',
        currentDate: today,
        startDate: new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30),
        endDate: today,
        category: '',
        subcategory: ''
      },
      // upload dataset
      upload: {
        file: null,
        isUploading: false,
        result: ''
      },
      // modal
      modalTransaction: {
        isActive: false,
        transaction: {},
        accountId: parseInt(this.$route.params.id)
      },
      // categories
      categories: [],
      categoriesAndSubcategoriesLookup: [],
      // resources
      rAccounts: this.$resource(Config.API_URL + 'accounts{/id}'),
      rCategories: this.$resource(Config.API_URL + 'categories{/id}'),
      rTransactions: this.$resource(Config.API_URL + 'accounts/{aid}/transactions{/id}'),
      rDatasets: this.$resource(Config.API_URL + 'accounts/' + parseInt(this.$route.params.id) + '/dataset')
    }
  },
  computed: {
    displayedTransactions: function () {
      let query = this.search.query
      let startDate = this.search.startDate
      let endDate = this.search.endDate
      let category = this.search.category
      let subcategory = this.search.subcategory
      let transactions = this.account.transactions
      transactions.map(t => {
        t.categoryLabel = (t.category in this.categoriesAndSubcategoriesLookup) ? this.categoriesAndSubcategoriesLookup[t.category].label : null
        t.subcategoryLabel = (t.subcategory in this.categoriesAndSubcategoriesLookup) ? this.categoriesAndSubcategoriesLookup[t.subcategory].label : null
        t.fullname = (t.memo) ? t.memo + ' ' + t.name : t.name
        return t
      })
      return transactions.filter(function (transaction) {
        return transaction.fullname.toLowerCase().indexOf(query.toLowerCase()) > -1 &
        new Date(Date.parse(transaction.dateUser)) >= startDate &
        new Date(Date.parse(transaction.dateUser)) <= endDate &
        (!category || transaction.category === category) &
        (!subcategory || transaction.subcategory === subcategory)
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
    editTransaction (item) {
      this.modalTransaction.transaction = item
      this.modalTransaction.isActive = true
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
    },
    // get categories and subcategories
    getCategories () {
      function getLookup (categories) {
        // save the complete list and create lookup for getting label
        var lookup = []
        for (let i = 0; i < categories.length; i++) {
          let category = categories[i]
          lookup[category.id] = category
          for (let i = 0; i < category.sub.length; i++) {
            let subcategory = category.sub[i]
            lookup[subcategory.id] = subcategory
          }
        }
        return lookup
      }
      if (localStorage.getItem('categories')) {
        this.categories = JSON.parse(localStorage.getItem('categories'))
        this.categoriesAndSubcategoriesLookup = getLookup(this.categories)
        return
      }
      this.rCategories.query({status: 'all'}).then(response => {
        this.categories = response.body
        this.categoriesAndSubcategoriesLookup = getLookup(this.categories)
        // put categories in local storage for future usage
        localStorage.setItem('categories', JSON.stringify(this.categories))
      }, response => {
        // @TODO : add error handling
        console.error(response)
      })
    },
    uploadDataset () {
      this.upload.result = ''
      // get file
      let file = this.upload.files[0]
      var data = new FormData()
      data.append('Content-Type', file.type || 'application/octet-stream')
      data.append('file', file)
      // check file size
      if (file.size > 80000000) {
        this.upload.result = 'File too big'
        return
      }
      // prepare context
      this.upload.isUploading = true
      this.isLoading = true
      // call API
      this.rDatasets.save({}, data)
        .then(response => {
          if (response.body.message) {
            this.upload.result = response.body.message
          }
          this.getTransactions()
        }, response => {
        // upload failed, inform user
          if (response.body.message) {
            this.upload.result = response.body.message
            return
          }
          this.upload.result = response.status + ' - ' + response.statusText
        })
        .finally(function () {
          // remove loading overlay when API replies
          this.upload.isUploading = false
          this.isLoading = false
        })
    }
  },
  watch: {
    'search.category': function () {
      // clear subcategory search field if category has changed
      this.search.subcategory = ''
    }
  },
  mounted: function () {
    this.get()
    this.getCategories()
    this.getTransactions()
  }
}
</script>
