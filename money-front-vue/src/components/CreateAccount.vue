<template>
  <form novalidate @submit.prevent="validateBeforeSubmit">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">{{ $t('actions.addAccount') }}</p>
      </header>
      <section class="modal-card-body">
        <div class="field is-required">
          <label class="label">{{ $t('fieldnames.bankIdentifier') }}</label>
          <div class="control">
            <input v-model="account.bankId" class="input" type="text" name="bankIdentifier" :placeholder="$t('fieldnames.bankIdentifier')" :class="{'is-danger': errors.bankIdentifier}">
            <span v-if="errors.bankIdentifier" class="help is-danger">{{ errors.bankIdentifier.message }}</span>
          </div>
        </div>
        <div class="field is-required">
          <label class="label">{{ $t('fieldnames.branchIdentifier') }}</label>
          <div class="control">
            <input v-model="account.branchId" class="input" type="text" name="branchIdentifier" :placeholder="$t('fieldnames.branchIdentifier')" :class="{'is-danger': errors.branchIdentifier}">
            <span v-if="errors.branchIdentifier" class="help is-danger">{{ errors.branchIdentifier.message }}</span>
          </div>
        </div>
        <div class="field is-required">
          <label class="label">{{ $t('fieldnames.accountIdentifier') }}</label>
          <div class="control">
            <input v-model="account.accountId" class="input" type="text" name="accountIdentifier" :placeholder="$t('fieldnames.accountIdentifier')" :class="{'is-danger': errors.accountIdentifier}">
            <span v-if="errors.accountIdentifier" class="help is-danger">{{ errors.accountIdentifier.message }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.label') }}</label>
          <div class="control">
            <input v-model="account.label" class="input" type="text" name="label" :placeholder="$t('fieldnames.label')" :class="{'is-danger': errors.label}">
            <span v-if="errors.label" class="help is-danger">{{ errors.label.message }}</span>
          </div>
        </div>
        <div v-if="error" class="message is-danger block">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
      </section>
      <footer class="modal-card-foot">
        <button class="button is-primary" :disabled="!isOnline"><span class="icon"><i class="fas fa-save" aria-hidden="true" /></span><span>{{ $t('actions.create') }}</span></button>
        <button class="button" type="button" @click="$emit('close')"><span class="icon"><i class="fas fa-ban" aria-hidden="true" /></span><span>{{ $t('actions.cancel') }}</span></button>
      </footer>
      <o-loading :active="isLoading" :full-page="false" />
    </div>
  </form>
</template>

<script>
import { useValidator } from '@/services/Validator'

export default {
  name: "CreateAccount",
  emits: [
    'close',
    'created',
  ],
  setup() {
    const { errors, validationRules, validateForm } = useValidator()
    return { errors, validationRules, validateForm }
  },
  data () {
    return {
      account: {},
      error: '',
      isLoading: false,
    }
  },
  computed: {
    isOnline () {
      return this.$store.isOnline
    },
  },
  created () {
    this.validationRules = {
      bankIdentifier: 'required|alpha_num',
      branchIdentifier: 'required|alpha_num',
      accountIdentifier: 'required|alpha_num',
      label: 'max:30',
    }
  },
  methods: {
    async validateBeforeSubmit (submitEvent) {
      this.error = null
      if (!this.validateForm(submitEvent)) {
        return
      }
      this.isLoading = true
      try {
        const account = await this.$http.post('accounts', this.account)
        this.$emit('created', account)
        this.$emit('close')
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
  },
}
</script>
