<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fas fa-home', text: $t('labels.home')},
          {link: '/accounts', icon: 'fas fa-table', text: $tc('objects.account', 2), isActive: true},
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
                <td><span v-if="account.balance">{{ $n(account.balance, 'currency') }}</span></td>
                <td v-if="account.lastUpdate">{{ $dayjs(account.lastUpdate).fromNow() }}</td><td v-else />
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else>
          <p>{{ $t('labels.noAccount') }}</p>
        </div>
        <div class="field is-grouped">
          <p class="control">
            <button class="button is-primary" role="button" :disabled="!isOnline" @click="isCreateAccountModalActive = true"><span class="icon"><i class="fas fa-plus-square" /></span><span>{{ $t('actions.addAccount') }}</span></button>
          </p>
          <p class="control">
            <o-field class="file">
              <o-upload v-model="upload.file" :disabled="(!isOnline || upload.isUploading)" @input="uploadDataset">
                <a class="button is-primary">
                  <o-icon icon="upload" />
                  <span>{{ $t('actions.uploadOfx') }}</span>
                </a>
              </o-upload>
              <span v-if="upload.file" class="file-name">
                {{ upload.file.name }} ({{ $tc('objects.byte', upload.file.size) }})
              </span>
            </o-field>
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
        <o-modal v-model:active="isCreateAccountModalActive" has-modal-card scroll="keep">
          <create-account @close="isCreateAccountModalActive = false" @created="getAccounts" />
        </o-modal>
        <o-loading :active="isLoading" :full-page="false" />
      </div>
    </div>
  </section>
</template>

<script>
import Config from '@/services/Config'
import Breadcrumb from '@/components/Breadcrumb.vue'
import CreateAccount from '@/components/CreateAccount.vue'

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
    }
  },
  computed: {
    isOnline () {
      return this.$store.isOnline
    },
  },
  mounted () {
    this.getAccounts()
  },
  methods: {
    async getAccounts () {
      this.isLoading = true
      try {
        const response = await this.$http.get('accounts')
        this.accounts = response.data
        this.accounts.map((account) => {
          account.iconUrl = account.iconUrl ? Config.API_URL + account.iconUrl : null
          return account
        })
      } catch (error) {
        this.error = error.message
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
      // prepare context
      this.upload.isUploading = true
      this.isLoading = true
      // call API
      try {
        const response = await this.$http.post('dataset', data)
        if (response.data.message) {
          this.upload.result = response.data.message
        }
        if (response.data.accounts && response.data.insertTime) {
          response.data.accounts.forEach(account => {
            sessionStorage.setItem('accounts:' + account + ':transactions:lastFetch', response.data.insertTime)
          })
        }
        this.getAccounts()
      } catch (error) {
        this.upload.result = error.message
      }
      this.upload.isUploading = false
      this.isLoading = false
    },
  },
}
</script>
