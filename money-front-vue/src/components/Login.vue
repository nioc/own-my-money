<template>
  <section class="hero is-light is-fullheight login">
    <div class="hero-body">
      <div class="container has-text-centered">
        <div class="column is-4 is-offset-4">
          <div class="box">
            <form @submit.prevent="validateBeforeSubmit">
              <h3 class="title has-text-grey">{{ $t('actions.login') }}</h3>
              <p class="subtitle has-text-grey">{{ $t('labels.login') }}</p>

              <div class="field">
                <div class="control has-icons-left has-icons-right">
                  <input v-model="credentials.login" class="input is-medium" type="text" name="login" :placeholder="$t('fieldnames.login')" :class="{'input': true, 'is-danger': errors.login}">
                  <span class="icon is-small is-left">
                    <i class="fa fa-user" />
                  </span>
                  <span v-show="errors.login" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.login" class="help is-danger has-text-left">{{ errors.login.message }}</span>
                </div>
              </div>

              <div class="field">
                <div class="control has-icons-left has-icons-right">
                  <input v-model="credentials.password" class="input is-medium" type="password" name="password" :placeholder="$t('fieldnames.password')" :class="{'input': true, 'is-danger': errors.password}">
                  <span class="icon is-small is-left">
                    <i class="fa fa-lock" />
                  </span>
                  <span v-show="errors.password" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.password" class="help is-danger has-text-left">{{ errors.password.message }}</span>
                </div>
              </div>
              <div class="field">
                <button class="button is-block is-primary is-medium is-fullwidth" :class="{'is-loading': isLoading}" :disabled="isDisabled"><span class="icon"><i class="fas fa-sign-in-alt" aria-hidden="true" /></span><span>{{ $t('actions.login') }}</span></button>
              </div>
              <div v-if="error" class="message is-danger">
                <div class="message-body">
                  {{ error }}
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { useValidator } from '@/services/Validator'
import Auth from '@/services/Auth'
import { setHeader } from '@/services/Http'

export default {
  setup() {
    const { errors, validationRules, validateForm } = useValidator()
    return { errors, validationRules, validateForm }
  },
  data () {
    return {
      credentials: {
        login: '',
        password: '',
      },
      isLoading: false,
      error: '',
    }
  },
  computed: {
    isDisabled () {
      return !this.$store.isOnline || this.isLoading
    },
  },
  created () {
    this.validationRules = {
      login: 'required|min:3|alpha_num',
      password: 'required',
    }
  },
  methods: {
    async validateBeforeSubmit (submitEvent) {
      if (!this.validateForm(submitEvent)) {
        return
      }
      this.isLoading = true
      const credentials = {
        login: this.credentials.login,
        password: this.credentials.password,
      }
      // call the auth service
      try {
        const user = await Auth.login(credentials)
        // set user and locale
        this.$store.setUser(user)
        if (user.language) {
          setHeader('Accept-Language', user.language)
          document.querySelector('html').setAttribute('lang', user.language)
          this.$i18n.locale = user.language
          this.$dayjs.locale(user.language)
        }
        // load data
        this.$store.loadCategories()
        this.$store.loadHolders()
        this.$store.loadMaps()
        if (this.$route.query.redirect) {
          // redirect to the requested route
          this.$router.replace(this.$route.query.redirect)
          return
        }
        // redirect to home
        this.$router.replace({ name: 'home' })
      } catch (error) {
        this.error = error.message 
      }
      this.isLoading = false
    },
  },
}
</script>

<style>
  .login {
    margin-top: -3.25rem;
  }
</style>
