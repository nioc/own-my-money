<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fas fa-home', text: $t('labels.home')},
          {link: '/accounts', icon: 'fas fa-table', text: $tc('objects.account', 2)},
          {link: '/accounts', text: accountTitle, isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.account', 1) }} {{ accountTitle }}</h1>
        <o-tabs type="boxed" :animated="false">

          <o-tab-item :label="$tc('objects.transaction', 2)" icon="th-list" class="has-half-margin-mobile">
            <div v-if="isLoaded" class="columns no-padding-parent-mobile is-multiline">
              <div class="column no-padding-mobile is-full">
                <transactions-history-chart
                  :title="$t('labels.transactionsByDay')"
                  :chart-endpoint="'accounts/' + account.id + '/transactions/history'"
                  :is-independent="true"
                  :date="date"
                />
              </div>
            </div>
            <transactions v-if="isLoaded" :url="url" :duration="account.duration" :account-id="account.id" class="pb-5" />

            <div v-if="account.isOwned" class="field is-grouped">
              <p class="control">
                <o-field class="file">
                  <o-upload v-model="upload.file" :disabled="(!isOnline || upload.isUploading)" @input="uploadDataset">
                    <a class="button is-primary">
                      <o-icon icon="upload" />
                      <span>{{ $t('actions.uploadOfxJson') }}</span>
                    </a>
                  </o-upload>
                  <span v-if="upload.file" class="file-name">
                    {{ upload.file.name }} ({{ $tc('objects.byte', upload.file.size) }})
                  </span>
                </o-field>
              </p>
              <div class="control">
                <div class="select">
                  <select v-model="upload.map" name="parent">
                    <option value="">-- JSON Map --</option>
                    <option v-for="map in store.maps" :key="map.code" :value="map.code">{{ map.label }}</option>
                  </select>
                </div>
              </div>
            </div>
            <div v-if="account.isOwned" class="field is-horizontal">
              <div class="field-body">
                <div v-if="upload.result" class="message is-danger">
                  <div class="message-body">
                    {{ upload.result }}
                  </div>
                </div>
              </div>
            </div>
          </o-tab-item>

          <o-tab-item v-if="isLoaded" :label="$t('labels.breakdown')" icon="balance-scale" class="has-half-margin-mobile">
            <account-dispatch :id="account.id" :duration="account.duration" />
          </o-tab-item>

          <o-tab-item v-if="account.isOwned" :label="$t('actions.edit')" icon="pencil-alt">
            <form novalidate class="section is-max-width-form" @submit.prevent="validateUpdateBeforeSubmit">
              <div class="field is-horizontal is-required">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.bankIdentifier') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <input v-model="updatedAccount.bankId" type="text" class="input" name="bankIdentifier" :placeholder="$t('fieldnames.bankIdentifier')" :class="{'is-danger': errors.bankIdentifier}">
                      <span v-if="errors.bankIdentifier" class="help is-danger">{{ errors.bankIdentifier.message }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal is-required">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.branchIdentifier') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <p class="control">
                      <input v-model="updatedAccount.branchId" type="text" class="input" name="branchIdentifier" :placeholder="$t('fieldnames.branchIdentifier')" :class="{'is-danger': errors.branchIdentifier}">
                      <span v-if="errors.branchIdentifier" class="help is-danger">{{ errors.branchIdentifier.message }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal is-required">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.accountIdentifier') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <input v-model="updatedAccount.accountId" type="text" class="input" name="accountIdentifier" :placeholder="$t('fieldnames.accountIdentifier')" :class="{'is-danger': errors.accountIdentifier}">
                      <p v-if="errors.accountIdentifier" class="help is-danger">{{ errors.accountIdentifier.message }}</p>
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
                      <input v-model="updatedAccount.label" type="text" class="input" name="label" :placeholder="$t('fieldnames.label')" :class="{'is-danger': errors.label}">
                      <p v-if="errors.label" class="help is-danger">{{ errors.label.message }}</p>
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
                      <o-field class="file">
                        <img v-if="account.iconUrl" :src="account.iconUrl" class="icon-account-admin">
                        <o-upload v-model="icon.file" :disabled="(!isOnline || icon.isUploading)" accept="image/*" @input="uploadIcon">
                          <a class="button">
                            <o-icon icon="upload" />
                            <span>{{ $t('actions.uploadIcon') }}</span>
                          </a>
                        </o-upload>
                        <span v-if="icon.file" class="file-name">
                          {{ icon.file.name }} ({{ $tc('objects.byte', icon.file.size) }})
                        </span>
                      </o-field>
                    </div>
                    <div class="field is-horizontal">
                      <div class="field-body">
                        <div v-if="icon.result" class="message is-danger">
                          <div class="message-body">
                            {{ icon.result }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal is-required">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.balance') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <input v-model="updatedAccount.balance" type="number" step="0.01" class="input" name="balance" :placeholder="$t('fieldnames.balance')" :class="{'is-danger': errors.balance}">
                      <p v-if="errors.balance" class="help is-danger">{{ errors.balance.message }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">{{ $t('fieldnames.isActive') }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <o-switch v-model="updatedAccount.isActive" root-class="mt-1" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="field is-horizontal">
                <div class="field-label is-normal" />
                <div class="field-body">
                  <div class="field">
                    <button type="button" class="button" role="button" :disabled="!isOnline" @click="modalHolders.isActive = true"><span class="icon"><i class="fas fa-users" /></span><span>{{ $t('actions.manageHolders') }}</span></button>
                  </div>
                </div>
              </div>
              <o-modal v-model:active="modalHolders.isActive" has-modal-card scroll="keep" :destroy-on-hide="true">
                <account-holders :account-id="account.id" @close="modalHolders.isActive = false" />
              </o-modal>

              <div class="field is-horizontal">
                <div class="field-label is-normal" />
                <div class="field-body">
                  <div class="field">
                    <div class="buttons">
                      <button type="submit" class="button is-primary" role="button" :disabled="!isOnline"><span class="icon"><i class="fas fa-save" /></span><span>{{ $t('actions.save') }}</span></button>
                      <button type="button" class="button is-danger" role="button" :disabled="!isOnline" @click="deleteAccount"><span class="icon"><i class="fas fa-trash-alt" /></span><span>{{ $t('actions.delete') }}</span></button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
            <o-loading :full-page="false" :active="isLoading" />
          </o-tab-item>

        </o-tabs>
      </div>
    </div>
  </section>
</template>

<script>
import Config from '@/services/Config'
import Breadcrumb from '@/components/Breadcrumb.vue'
import Transactions from '@/components/Transactions.vue'
import AccountHolders from '@/components/Holders.vue'
import TransactionsHistoryChart from '@/components/TransactionsHistoryChart.vue'
import AccountDispatch from '@/components/AccountDispatch.vue'
import Modal from '@/components/Modal.vue'
import { useValidator } from '@/services/Validator'
import { useStore } from '@/store'

export default {
  name: 'Account',
  components: {
    Breadcrumb,
    TransactionsHistoryChart,
    Transactions,
    AccountHolders,
    AccountDispatch,
  },
  setup() {
    const { errors, validationRules, validateForm } = useValidator()
    const store = useStore()
    return { errors, validationRules, validateForm, store }
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
        transactions: [],
        isOwned: null,
        isActive: true,
      },
      icon: {
        file: null,
        isUploading: false,
        result: null,
      },
      isLoading: false,
      isLoaded: false,
      updatedAccount: {
      },
      // modal
      modalHolders: {
        isActive: false,
      },
      // upload dataset
      upload: {
        file: null,
        map: '',
        isUploading: false,
        result: '',
      },
      date: {
        timeUnit: '',
        currentDate: today,
        duration: 'P3M',
        periodStart: this.$dayjs(today).subtract(this.$dayjs.duration('P3M')).toDate(),
        periodEnd: today,
      },
      url: 'accounts/' + parseInt(this.$route.params.id) + '/transactions',
    }
  },
  computed: {
    isOnline () {
      return this.$store.isOnline
    },
    accountTitle () {
      return this.account.label ? this.account.label : this.account.bankId + ' ' + this.account.branchId + ' ' + this.account.accountId
    },
  },
  created () {
    this.validationRules = {
      bankIdentifier: 'required|alpha_num',
      branchIdentifier: 'required|alpha_num',
      accountIdentifier: 'required|alpha_num',
      label: 'max:30',
      balance: 'required|decimal:2',
    }
  },
  mounted () {
    this.get()
    this.$bus.on('transactions-date-filtered', this.handleTransactionsDateFilteredAccount)
  },
  beforeUnmount () {
    // remove events listener
    this.$bus.off('transactions-date-filtered', this.handleTransactionsDateFilteredAccount)
  },
  methods: {
    // get account informations
    async get () {
      this.isLoading = true
      try {
        const response = await this.$http.get(`accounts/${this.account.id}`)
        this.account.bankId = response.data.bankId
        this.account.branchId = response.data.branchId
        this.account.accountId = response.data.accountId
        this.account.balance = response.data.balance
        this.account.label = response.data.label
        this.account.duration = response.data.duration
        if (response.data.iconUrl) {
          this.account.iconUrl = Config.API_URL + response.data.iconUrl
        }
        this.account.isOwned = response.data.isOwned
        this.account.isActive = response.data.isActive
        this.updatedAccount = JSON.parse(JSON.stringify(this.account))
        delete (this.updatedAccount.transactions)
        this.date.duration = this.account.duration
        this.date.periodStart = this.$dayjs(this.date.currentDate).subtract(this.$dayjs.duration(this.account.duration)).toDate()
        this.isLoaded = true
      } catch (error) {
        if (error.response && error.response.status === 403 || error.response.status === 404) {
          // user does not can access this account, return to home
          this.$router.replace({ name: 'home' })
          return
        }
        // @TODO : add error handling
        console.error(error)
      }
      this.isLoading = false
    },
    async validateUpdateBeforeSubmit (submitEvent) {
      this.error = null
      if (!this.validateForm(submitEvent)) {
        return
      }
      this.isLoading = true
      try {
        const response = await this.$http.put(`accounts/${this.account.id}`, this.updatedAccount)
        this.account.bankId = response.data.bankId
        this.account.branchId = response.data.branchId
        this.account.accountId = response.data.accountId
        this.account.balance = response.data.balance
        this.account.label = response.data.label
      } catch (error) {
        this.error = error.message 
      }
      this.isLoading = false
    },
    async uploadIcon (event) {
      this.icon.result = ''
      // get file
      const file = event.target.files[0]
      var data = new FormData()
      data.append('Content-Type', file.type || 'application/octet-stream')
      data.append('file', file)
      // check file size
      if (file.size > 800000) {
        this.icon.result = this.$t('labels.fileTooBig')
        return
      }
      // prepare context
      this.icon.isUploading = true
      this.isLoading = true
      this.account.iconUrl = null
      try {
        const response = await this.$http.post(`accounts/${parseInt(this.$route.params.id)}/icons`, data)
        this.icon.file = null
        if (response.data.iconUrl) {
          this.account.iconUrl = Config.API_URL + response.data.iconUrl + '?' + this.$dayjs()
        }
        if (response.data.message) {
          this.icon.result = response.data.message
        }
      } catch (error) {
        this.icon.result = error.message
      }
      this.icon.isUploading = false
      this.isLoading = false
    },
    async deleteAccount () {
      if (!await new Promise((resolve) =>
        this.$oruga.modal.open({
          rootClass: 'dialog',
          trapFocus: true,
          component: Modal,
          onCancel: () => resolve(false),
          props: {
            message: this.$t('labels.deleteAccountMsg'),
            title: this.$t('labels.deleteAccount'),
            type: 'is-danger',
            hasIcon: true,
            iconClass: 'fas fa-trash-alt fa-2x',
            hasCancelButton: true,
            confirmText: this.$t('actions.deleteAccount'),
            cancelText: this.$t('actions.cancel'),
            onConfirm: resolve,
            onCancel: () => {
              resolve(false)
            },
          },
        }))) {
        return
      }
      this.isLoading = true
      try {
        await this.$http.delete(`accounts/${this.account.id}`)
        this.$router.replace({ name: 'accounts' })
      } catch (error) {
            
        // @TODO : add error handling
        console.error(error)
      }
      this.isLoading = false
    },
    async uploadDataset (event) {
      this.upload.result = ''
      // get file
      const file = event.target.files[0]
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
      try {
        const response = await this.$http.post(`accounts/${parseInt(this.$route.params.id)}/dataset`, data, { params })
        if (response.data.message) {
          this.upload.result = response.data.message
        }
        if (response.data.accounts && response.data.insertTime) {
          response.data.accounts.forEach(account => {
            sessionStorage.setItem('accounts:' + account + ':transactions:lastFetch', response.data.insertTime)
          })
        }
        this.$bus.emit('transactions-updated', {})
      } catch (error) {
        this.upload.result = error.message
      }
      this.upload.isUploading = false
      this.isLoading = false
    },
    handleTransactionsDateFilteredAccount (search) {
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
