<template>
  <form @submit.prevent="validateBeforeSubmit" novalidate>
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">{{ $t('actions.addAccount') }}</p>
      </header>
      <section class="modal-card-body">
        <div class="field">
          <label class="label">{{ $t('fieldnames.bankIdentifier') }}</label>
          <div class="control">
            <input class="input" type="text" name="bankIdentifier" :placeholder="$t('fieldnames.bankIdentifier')" v-model="account.bankId" v-validate="'required|alpha_num'" :class="{ 'is-danger': errors.has('bankIdentifier') }">
            <span v-show="errors.has('bankIdentifier')" class="help is-danger">{{ errors.first('bankIdentifier') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.branchIdentifier') }}</label>
          <div class="control">
            <input class="input" type="text" name="branchIdentifier" :placeholder="$t('fieldnames.branchIdentifier')" v-model="account.branchId" v-validate="'required|alpha_num'" :class="{ 'is-danger': errors.has('branchIdentifier') }">
            <span v-show="errors.has('branchIdentifier')" class="help is-danger">{{ errors.first('branchIdentifier') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.accountIdentifier') }}</label>
          <div class="control">
            <input class="input" type="text" name="accountIdentifier" :placeholder="$t('fieldnames.accountIdentifier')" v-model="account.accountId" v-validate="'required|alpha_num'" :class="{ 'is-danger': errors.has('accountIdentifier') }">
            <span v-show="errors.has('accountIdentifier')" class="help is-danger">{{ errors.first('accountIdentifier') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.label') }}</label>
          <div class="control">
            <input class="input" type="text" name="label" :placeholder="$t('fieldnames.label')" v-model="account.label" v-validate="'max:30'" :class="{ 'is-danger': errors.has('label') }">
            <span v-show="errors.has('label')" class="help is-danger">{{ errors.first('label') }}</span>
          </div>
        </div>
        <div class="message is-danger block" v-if="error">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
      </section>
      <footer class="modal-card-foot">
        <button class="button is-primary" :disabled="!isOnline">{{ $t('actions.create') }}</button>
        <button class="button" type="button" @click="$parent.close()">{{ $t('actions.cancel') }}</button>
      </footer>
      <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>
    </div>
  </form>
</template>

<script>
import Config from './../services/Config'
export default {
  data () {
    return {
      account: {},
      error: '',
      isLoading: false,
      // resources
      rAccounts: this.$resource(Config.API_URL + 'accounts{/id}')
    }
  },
  computed: {
    isOnline: function () {
      return this.$store.state.isOnline
    }
  },
  methods: {
    validateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          this.isLoading = true
          // if validation is ok, call accounts API
          this.rAccounts.save(this.account)
            .then(response => {
              this.$parent.$parent.getAccounts()
              this.$parent.close()
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
    }
  }
}
</script>
