<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: this.$t('labels.home')},
          {link: '/accounts', icon: 'fa-table', text: this.$tc('objects.account', 2), isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.account', 2) }}</h1>
        <div v-if="error" class="message is-danger">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
        <div v-if="accounts.length" class="table-container">
          <table class="table is-striped is-hoverable is-fullwidth">
            <thead>
              <tr>
                <th />
                <th>{{ $tc('objects.account', 1) }}</th>
                <th>{{ $t('fieldnames.balance') }}</th>
                <th>{{ $t('fieldnames.updated') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="account in accounts" :key="account.id">
                <td class="icon-account"><img v-if="account.iconUrl" :src="account.iconUrl" height="24" width="24"></td>
                <td><router-link :to="{name: 'account', params: {id: account.id}}">{{ account.bankId }} {{ account.branchId }} {{ account.accountId }}<span v-if="account.label"> ({{ account.label }})</span></router-link><span v-if="!account.isOwned" class="tag is-danger is-light">{{ $t('labels.readOnly') }}</span></td>
                <td>{{ $n(account.balance, 'currency') }}</td>
                <td v-if="account.lastUpdate">{{ account.lastUpdate | moment("from", "now") }}</td><td v-else />
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else>
          <p>{{ $t('labels.noAccount') }}</p>
        </div>
        <div class="field is-grouped">
          <p class="control">
            <button class="button is-primary" role="button" :disabled="!isOnline" @click="isCreateAccountModalActive = true"><i class="fa fa-plus fa-mr" />{{ $t('actions.addAccount') }}</button>
          </p>
          <p class="control">
            <b-field class="file">
              <b-upload v-model="upload.file" :disabled="(!isOnline || upload.isUploading)" @input="uploadDataset">
                <a class="button is-primary" :disabled="(!isOnline || upload.isUploading)">
                  <b-icon icon="upload" />
                  <span>{{ $t('actions.uploadOfx') }}</span>
                </a>
              </b-upload>
              <span v-if="upload.file" class="file-name">
                {{ upload.file.name }} ({{ $tc('objects.byte', upload.file.size) }})
              </span>
            </b-field>
          </p>
        </div>
        <div class="field is-horizontal">
          <div class="field-body">
            <div v-if="upload.result" class="message is-danger">
              <div class="message-body">
                {{ upload.result }}
              </div>
            </div>
          </div>
        </div>
        <b-modal :active.sync="isCreateAccountModalActive" has-modal-card scroll="keep">
          <create-account />
        </b-modal>
        <b-loading :is-full-page="false" :active.sync="isLoading" />
      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
import CreateAccount from '@/components/CreateAccount'
export default {
  name: 'Accounts',
  components: {
    Breadcrumb,
    CreateAccount,
  },
  data () {
    return {
      accounts: [],
      error: '',
      isCreateAccountModalActive: false,
      isLoading: false,
      // upload dataset
      upload: {
        file: null,
        isUploading: false,
        result: '',
      },
      // resources
      rAccounts: this.$resource(Config.API_URL + 'accounts{/id}'),
      rDatasets: this.$resource(Config.API_URL + 'dataset'),
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    },
  },
  mounted () {
    this.getAccounts()
  },
  methods: {
    getAccounts () {
      this.isLoading = true
      this.rAccounts.query()
        .then((response) => {
          this.accounts = response.body
          this.accounts.map((account) => {
            account.iconUrl = account.iconUrl ? Config.API_URL + account.iconUrl : null
            return account
          })
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
    },
    uploadDataset () {
      this.upload.result = ''
      // get file
      const file = this.upload.file
      var data = new FormData()
      data.append('Content-Type', file.type || 'application/octet-stream')
      data.append('file', file)
      // check file size
      if (file.size > 80000000) {
        this.upload.result = this.$t('labels.fileTooBig')
        return
      }
      // prepare context
      this.upload.isUploading = true
      this.isLoading = true
      // call API
      this.rDatasets.save({}, data)
        .then((response) => {
          if (response.body.message) {
            this.upload.result = response.body.message
          }
          if (response.body.accounts && response.body.insertTime) {
            response.body.accounts.forEach(account => {
              sessionStorage.setItem('accounts:' + account + ':transactions:lastFetch', response.body.insertTime)
            })
          }
          this.getAccounts()
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
    },
  },
}
</script>
