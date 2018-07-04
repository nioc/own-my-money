<template>
  <form action="">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">New account</p>
        <button class="delete" aria-label="close" @click="$parent.close()"></button>
      </header>
      <form @submit.prevent="validateBeforeSubmit" novalidate>
        <section class="modal-card-body">
          <div class="field">
            <label class="label">Bank identifier</label>
            <div class="control">
              <input class="input" type="text" name="bankId" placeholder="Bank Id" v-model="account.bankId" v-validate="'required|alpha_num'" data-vv-as="bank id" :class="{'input': true, 'is-danger': errors.has('bankId') }">
              <span v-show="errors.has('bankId')" class="help is-danger">{{errors.first('bankId')}}</span>
            </div>
          </div>
          <div class="field">
            <label class="label">Branch identifier</label>
            <div class="control">
              <input class="input" type="text" name="BranchId" placeholder="Branch Id" v-model="account.branchId" v-validate="'required|alpha_num'" data-vv-as="branch id" :class="{'input': true, 'is-danger': errors.has('BranchId') }">
              <span v-show="errors.has('BranchId')" class="help is-danger">{{errors.first('BranchId')}}</span>
            </div>
          </div>
          <div class="field">
            <label class="label">Account identifier</label>
            <div class="control">
              <input class="input" type="text" name="AccountId" placeholder="Account Id" v-model="account.accountId" v-validate="'required|alpha_num'" data-vv-as="account id" :class="{'input': true, 'is-danger': errors.has('AccountId') }">
              <span v-show="errors.has('AccountId')" class="help is-danger">{{errors.first('AccountId')}}</span>
            </div>
          </div>
          <div class="message is-danger block" v-if="error">
            <div class="message-body">
              {{ error }}
            </div>
          </div>
        </section>
        <footer class="modal-card-foot">
          <button class="button is-primary">Create</button>
          <button class="button" type="button" @click="$parent.close()">Cancel</button>
        </footer>
      </form>
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
      // resources
      rAccounts: this.$resource(Config.API_URL + 'accounts{/id}')
    }
  },
  methods: {
    validateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
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
        }
      })
    }
  }
}
</script>
