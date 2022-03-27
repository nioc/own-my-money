<template>
  <form novalidate @submit.prevent="validateBeforeSubmit">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">{{ action }} {{ $tc('objects.user', 1).toLowerCase() }}</p>
      </header>
      <section class="modal-card-body">
        <div class="field is-required">
          <label class="label">{{ $t('fieldnames.login') }}</label>
          <div class="control has-icons-left has-icons-right">
            <input v-model="currentUser.login" class="input" type="text" name="login" placeholder="Login" :class="{'is-danger': errors.login}">
            <span class="icon is-small is-left"><i class="fa fa-user" /></span>
            <span v-show="errors.login" class="icon is-small is-right"><i class="fas fa-exclamation-triangle has-text-danger" /></span>
            <span v-if="errors.login" class="help is-danger">{{ errors.login.message }}</span>
          </div>
        </div>
        <div v-if="!currentUser.sub" class="field is-required">
          <label class="label">{{ $t('fieldnames.password') }}</label>
          <div class="control has-icons-left">
            <input v-model="currentUser.password" class="input" type="password" name="password" placeholder="Password" :class="{'is-danger': errors.password}">
            <span class="icon is-small is-left"><i class="fa fa-lock" /></span>
            <span v-if="errors.password" class="help is-danger">{{ errors.password.message }}</span>
          </div>
        </div>
        <div class="field is-required">
          <label class="label">{{ $t('fieldnames.email') }}</label>
          <div class="control has-icons-left has-icons-right">
            <input v-model="currentUser.mail" class="input" type="email" name="email" placeholder="Mail address" :class="{'is-danger': errors.email}">
            <span class="icon is-small is-left"><i class="fa fa-envelope" /></span>
            <span v-show="errors.email" class="icon is-small is-right"><i class="fas fa-exclamation-triangle has-text-danger" /></span>
            <span v-if="errors.email" class="help is-danger">{{ errors.email.message }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.status') }}</label>
          <div class="control">
            <o-switch v-model="currentUser.status" />
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.admin') }}</label>
          <div class="control">
            <o-switch v-model="isAdmin" />
          </div>
        </div>
        <div v-if="error" class="message is-danger block">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
        <user-connections v-if="currentUser.sub" :id="currentUser.sub" />
      </section>
      <footer class="modal-card-foot">
        <button class="button is-primary" :disabled="!isOnline"><span class="icon"><i class="fas fa-save" aria-hidden="true" /></span><span>{{ action }}</span></button>
        <button class="button" type="button" @click="$emit('close')"><span class="icon"><i class="fas fa-ban" aria-hidden="true" /></span><span>{{ $t('actions.cancel') }}</span></button>
      </footer>
      <o-loading :active="isLoading" :full-page="false" />
    </div>
  </form>
</template>

<script>
import UserConnections from '@/components/UserConnections.vue'
import { useValidator } from '@/services/Validator'
import { useStore } from '@/store'

export default {
  name: 'UserForm',
  components: {
    UserConnections,
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  emits: [
    'close',
  ],
  setup() {
    const { errors, validationRules, validateForm } = useValidator()
    const store = useStore()
    return { errors, validationRules, validateForm, store }
  },
  data () {
    return {
      error: '',
      isLoading: false,
      action: '',
      currentUser: { ...this.user },
    }
  },
  computed: {
    isOnline () {
      return this.store.isOnline
    },
    isAdmin: {
      get () {
        return this.currentUser.scope.includes('admin')
      },
      set: function (isAdmin) {
        const scope = this.currentUser.scope.split(' ').filter((value) => value !== 'admin')
        if (isAdmin) {
          scope.push('admin')
        }
        this.currentUser.scope = scope.join(' ')
      },
    },
  },
  created () {
    this.validationRules = {
      login: 'required|min:3|alpha_num',
      password: 'required|min:5|alpha_num',
      email: 'required|email',
    }
    this.action = this.user.sub ? this.$t('actions.update') : this.$t('actions.create')
  },
  methods: {
    async validateBeforeSubmit (submitEvent) {
      this.error = null
      if (!this.validateForm(submitEvent)) {
        return
      }
      this.isLoading = true
      // if validation is ok, call users API
      if (!this.currentUser.sub) {
        // creating new user
        try {
          await this.$http.post('users', this.currentUser)
          await this.store.loadHolders()
          this.$emit('close')
        } catch (error) {
          this.error = error.message
        }
        this.isLoading = false
        return
      }
      // updating user
      try {
        await this.$http.put(`users/${this.currentUser.sub}`, this.currentUser)
        await this.store.loadHolders()
        this.$emit('close')
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
  },
}
</script>
