<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          { link: '/', icon: 'fa-home', text: this.$t('labels.home') },
          { link: '/accounts', icon: 'fa-table', text: this.$tc('objects.account', 2) },
          { link: '/accounts', text: accountTitle, isActive: true }
        ]">
      </breadcrumb>
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.account', 1) }} {{ accountTitle }}</h1>
        <b-tabs type="is-boxed" :animated="false">

          <b-tab-item :label="$tc('objects.transaction', 2)" icon="file-text-o" class="has-half-margin-mobile">
            <div class="columns no-padding-parent-mobile is-multiline" v-if="isLoaded">
              <div class="column no-padding-mobile is-full">
                <transactions-history-chart
                :title="$t('labels.transactionsByDay')"
                :chartEndpoint="'accounts/' + this.account.id + '/transactions/history'"
                :isIndependent="true"
                :date="date"
                ></transactions-history-chart>
              </div>
            </div>
            <transactions :url="url" :duration="account.duration" :accountId="this.account.id" v-if="isLoaded"/>

            <div class="field is-grouped">
              <p class="control">
                <b-field class="file">
                  <b-upload v-model="upload.file" @input="uploadDataset" :disabled="(!isOnline || upload.isUploading)">
                    <a class="button is-primary" :disabled="(!isOnline || upload.isUploading)">
                      <b-icon icon="upload"></b-icon>
                      <span>{{ $t('actions.uploadOfxJson') }}</span>
                    </a>
                  </b-upload>
                  <span class="file-name" v-if="upload.file">
                    {{ upload.file.name }} ({{ $tc('objects.byte', upload.file.size) }})
                  </span>
                </b-field>
              </p>
              <div class="control">
                <div class="select">
                  <select name="parent" v-model="upload.map">
                    <option value="">-- JSON Map --</option>
                    <option v-for="map in maps" :key="map.code" v-bind:value="map.code">{{ map.label }}</option>
                  </select>
                </div>
              </div>
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
          </b-tab-item>

          <b-tab-item :label="$t('actions.edit')" icon="pencil">
            <form @submit.prevent="validateUpdateBeforeSubmit" novalidate class="section is-max-width-form">
              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.bankIdentifier') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <input type="text" class="input" name="bankIdentifier" :placeholder="$t('fieldnames.bankIdentifier')" v-model="updatedAccount.bankId" v-validate="'required|alpha_num'" :class="{ 'is-danger': errors.has('bankIdentifier') }">
                      <span v-show="errors.has('bankIdentifier')" class="help is-danger">{{ errors.first('bankIdentifier') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.branchIdentifier') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <p class="control">
                      <input type="text" class="input" name="branchIdentifier" :placeholder="$t('fieldnames.branchIdentifier')" v-model="updatedAccount.branchId" v-validate="'required|alpha_num'" :class="{ 'is-danger': errors.has('branchIdentifier') }">
                      <span v-show="errors.has('branchIdentifier')" class="help is-danger">{{ errors.first('branchIdentifier') }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.accountIdentifier') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <input type="text" class="input" name="accountIdentifier" :placeholder="$t('fieldnames.accountIdentifier')" v-model="updatedAccount.accountId" v-validate="'required|alpha_num'" :class="{ 'is-danger': errors.has('accountIdentifier') }">
                      <p v-show="errors.has('accountIdentifier')" class="help is-danger">{{ errors.first('accountIdentifier') }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.label') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <input type="text" class="input" name="label" :placeholder="$t('fieldnames.label')" v-model="updatedAccount.label" v-validate="'max:30'" :class="{'is-danger': errors.has('label') }">
                      <p v-show="errors.has('label')" class="help is-danger">{{ errors.first('label') }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.duration') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <div class="select">
                        <select v-model="updatedAccount.duration">
                          <option value="P1M">{{ $tc('objects.lastMonth', 1) }}</option>
                          <option value="P3M">{{ $tc('objects.lastMonth', 3) }}</option>
                          <option value="P6M">{{ $tc('objects.lastMonth', 6) }}</option>
                          <option value="P1Y">{{ $tc('objects.lastYear', 1) }}</option>
                          <option value="P2Y">{{ $tc('objects.lastYear', 2) }}</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.icon') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <b-field class="file">
                      <img v-if="account.iconUrl" :src="account.iconUrl" class="icon-account-admin"/>
                        <b-upload v-model="icon.file" @input="uploadIcon" :disabled="(!isOnline || icon.isUploading)" accept="image/*">
                          <a class="button" :disabled="(!isOnline || icon.isUploading)">
                            <b-icon icon="upload"></b-icon>
                            <span>{{ $t('actions.uploadIcon') }}</span>
                          </a>
                        </b-upload>
                        <span class="file-name" v-if="icon.file">
                          {{ icon.file.name }} ({{ $tc('objects.byte', icon.file.size) }})
                        </span>
                      </b-field>
                    </div>
                    <div class="field is-horizontal" >
                      <div class="field-body">
                        <div class="message is-danger"  v-if="icon.result">
                          <div class="message-body">
                            {{ icon.result }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.balance') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <input type="number" step="0.01" class="input" name="balance" :placeholder="$t('fieldnames.balance')" v-model="updatedAccount.balance" v-validate="'required|decimal:2'" :class="{'is-danger': errors.has('balance') }">
                      <p v-show="errors.has('balance')" class="help is-danger">{{ errors.first('balance') }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="buttons">
                      <button type="submit" class="button is-primary" role="button" :disabled="!isOnline"><i class="fa fa-save"/>&nbsp;{{ $t('actions.save') }}</button>
                      <button type="button" class="button is-danger" role="button" :disabled="!isOnline" v-on:click="deleteAccount"><i class="fa fa-trash"/>&nbsp;{{ $t('actions.delete') }}</button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
            <b-loading :is-full-page="false" :active="isLoading"></b-loading>
          </b-tab-item>

        </b-tabs>
      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
import Bus from './../services/Bus'
import Breadcrumb from '@/components/Breadcrumb'
import Transactions from '@/components/Transactions'
import TransactionsHistoryChart from '@/components/TransactionsHistoryChart'
export default {
  name: 'account',
  components: {
    Breadcrumb,
    TransactionsHistoryChart,
    Transactions
  },
  data () {
    const today = new Date()
    today.setHours(0, 0, 0)
    today.setMilliseconds(0)
    return {
      // account
      account: {
        id: parseInt(this.$route.params.id),
        bankId: '',
        branchId: '',
        accountId: '',
        label: '',
        balance: 0,
        duration: 'P3M',
        iconUrl: null,
        transactions: []
      },
      icon: {
        file: null,
        isUploading: false,
        result: null
      },
      isLoading: false,
      isLoaded: false,
      updatedAccount: {
      },
      // maps
      maps: [],
      // upload dataset
      upload: {
        file: null,
        map: '',
        isUploading: false,
        result: ''
      },
      date: {
        timeUnit: '',
        currentDate: today,
        duration: 'P3M',
        periodStart: this.$moment(today).subtract(this.$moment.duration('P3M')).toDate(),
        periodEnd: today
      },
      // resources
      url: Config.API_URL + 'accounts/' + parseInt(this.$route.params.id) + '/transactions{/id}',
      rAccounts: this.$resource(Config.API_URL + 'accounts{/id}'),
      rAccountIcons: this.$resource(Config.API_URL + 'accounts/' + parseInt(this.$route.params.id) + '/icons'),
      rMaps: this.$resource(Config.API_URL + 'maps'),
      rDatasets: this.$resource(Config.API_URL + 'accounts/' + parseInt(this.$route.params.id) + '/dataset')
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    },
    accountTitle () {
      return this.account.label ? this.account.label : this.account.bankId + ' ' + this.account.branchId + ' ' + this.account.accountId
    }
  },
  methods: {
    // get account informations
    get () {
      this.isLoading = true
      this.rAccounts.get({ id: this.account.id }).then((response) => {
        this.account.bankId = response.body.bankId
        this.account.branchId = response.body.branchId
        this.account.accountId = response.body.accountId
        this.account.balance = response.body.balance
        this.account.label = response.body.label
        this.account.duration = response.body.duration
        if (response.body.iconUrl) {
          this.account.iconUrl = Config.API_URL + response.body.iconUrl
        }
        this.updatedAccount = JSON.parse(JSON.stringify(this.account))
        delete (this.updatedAccount.transactions)
        this.date.duration = this.account.duration
        this.date.periodStart = this.$moment(this.date.currentDate).subtract(this.$moment.duration(this.account.duration)).toDate()
        this.isLoaded = true
      }, (response) => {
        if (response.status === 403 || response.status === 404) {
          // user does not can access this account, return to home
          this.$router.replace({ name: 'home' })
          return
        }
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
          this.updatedAccount.balance = parseFloat(this.updatedAccount.balance)
          this.rAccounts.update({ id: this.account.id }, this.updatedAccount)
            .then((response) => {
              // this.getAccounts()
              this.account.bankId = response.body.bankId
              this.account.branchId = response.body.branchId
              this.account.accountId = response.body.accountId
              this.account.balance = response.body.balance
              this.account.label = response.body.label
            }, (response) => {
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
    uploadIcon () {
      this.icon.result = ''
      // get file
      let file = this.icon.file
      var data = new FormData()
      data.append('Content-Type', file.type || 'application/octet-stream')
      data.append('file', file)
      // check file size
      if (file.size > 800000) {
        this.icon.result = this.$t('labels.fileTooBig')
        return
      }
      let params = {}
      // prepare context
      this.icon.isUploading = true
      this.isLoading = true
      this.account.iconUrl = null
      // call API
      this.rAccountIcons.save(params, data)
        .then((response) => {
          this.icon.file = null
          if (response.body.iconUrl) {
            this.account.iconUrl = Config.API_URL + response.body.iconUrl + '?' + this.$moment()
          }
          if (response.body.message) {
            this.icon.result = response.body.message
          }
        }, (response) => {
        // upload failed, inform user
          if (response.body.message) {
            this.icon.result = response.body.message
            return
          }
          this.icon.result = response.status + ' - ' + response.statusText
        })
        .finally(function () {
          // remove loading overlay when API replies
          this.icon.isUploading = false
          this.isLoading = false
        })
    },
    deleteAccount () {
      this.$dialog.confirm({
        message: this.$t('labels.deleteAccountMsg'),
        title: this.$t('labels.deleteAccount'),
        type: 'is-danger',
        hasIcon: true,
        icon: 'trash',
        confirmText: this.$t('actions.deleteAccount'),
        cancelText: this.$t('actions.cancel'),
        focusOn: 'cancel',
        onConfirm: () => {
          this.isLoading = true
          this.rAccounts.delete({ id: this.account.id })
            .then((response) => {
              this.$router.replace({ name: 'accounts' })
            }, (response) => {
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
    // get dataset maps
    getMaps () {
      // try to get maps from local storage
      if (localStorage.getItem('maps')) {
        this.maps = JSON.parse(localStorage.getItem('maps'))
        return
      }
      this.rMaps.get().then((response) => {
        this.maps = response.body
        // put maps in local storage for future usage
        localStorage.setItem('maps', JSON.stringify(this.maps))
      }, (response) => {
        // @TODO : add error handling
        console.error(response)
      })
    },
    uploadDataset () {
      this.upload.result = ''
      // get file
      let file = this.upload.file
      var data = new FormData()
      data.append('Content-Type', file.type || 'application/octet-stream')
      data.append('file', file)
      // check file size
      if (file.size > 80000000) {
        this.upload.result = this.$t('labels.fileTooBig')
        return
      }
      let params = {}
      if (file.type === 'application/json') {
        if (!this.upload.map) {
          this.upload.result = this.$t('labels.mapMustBeSet')
          return
        }
        params = { map: this.upload.map }
      }
      // prepare context
      this.upload.isUploading = true
      this.isLoading = true
      // call API
      this.rDatasets.save(params, data)
        .then((response) => {
          if (response.body.message) {
            this.upload.result = response.body.message
          }
          if (response.body.accounts && response.body.insertTime) {
            response.body.accounts.forEach(account => {
              sessionStorage.setItem('accounts:' + account + ':transactions:lastFetch', response.body.insertTime)
            })
          }
          Bus.$emit('transactions-updated', {})
        }, (response) => {
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
  mounted () {
    this.get()
    this.getMaps()
    Bus.$on('transactions-date-filtered', (search) => {
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
    })
  },
  beforeDestroy () {
    // remove events listener
    Bus.$off('transactions-date-filtered')
  }
}
</script>
