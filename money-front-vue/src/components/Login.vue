<template>
  <section class="hero is-light is-fullheight login">
     <div class="hero-body">
       <div class="container has-text-centered">
         <div class="column is-4 is-offset-4">
          <div class="box">
            <form>
              <h3 class="title has-text-grey">{{ $t('actions.login') }}</h3>
              <p class="subtitle has-text-grey">{{ $t('labels.login') }}</p>
              <div class="field">
                  <div class="control has-icons-left has-icons-right">
                      <input class="input is-medium" type="text" name="login" :placeholder="$t('fieldnames.login')" v-model="credentials.login" v-validate="'required|min:3|alpha'" :class="{'input': true, 'is-danger': errors.has('login') }">
                      <span class="icon is-small is-left">
                        <i class="fa fa-user"></i>
                      </span>
                      <span class="icon is-small is-right" v-show="errors.has('login')">
                        <i class="fa fa-exclamation-triangle"></i>
                      </span>
                      <span v-show="errors.has('login')" class="help is-danger">{{errors.first('login')}}</span>
                  </div>
              </div>
              <div class="field">
                  <div class="control has-icons-left has-icons-right">
                      <input class="input is-medium" type="password" name="password" :placeholder="$t('fieldnames.password')" v-model="credentials.password" v-validate="'required'" :class="{'input': true, 'is-danger': errors.has('password') }">
                      <span class="icon is-small is-left">
                        <i class="fa fa-lock"></i>
                      </span>
                      <span class="icon is-small is-right" v-show="errors.has('password')">
                        <i class="fa fa-exclamation-triangle"></i>
                      </span>
                      <span v-show="errors.has('password')" class="help is-danger">{{errors.first('password')}}</span>
                  </div>
              </div>
              <div class="field">
                <button class="button is-block is-primary is-medium is-fullwidth" :class="{ 'is-loading': isLoading }" :disabled="isDisabled" @click="submit"><span class="fa fa-sign-in fa-fw" aria-hidden="true"></span>&nbsp;{{ $t('actions.login') }}</button>
              </div>
              <div class="message is-danger" v-if="error">
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
import Auth from '../services/Auth'
export default {
  data () {
    return {
      credentials: {
        login: '',
        password: ''
      },
      isLoading: false,
      error: ''
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    },
    isDisabled () {
      return !this.isOnline || this.isLoading || this.errors.any()
    }
  },
  methods: {
    submit (e) {
      // prevent form submit
      e.preventDefault()
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          this.isLoading = true
          // get credentials
          const credentials = {
            login: this.credentials.login,
            password: this.credentials.password
          }
          // check errors bag and field value
          if (this.errors.count() > 0 || credentials.login === '' || credentials.password === '') {
            return
          }
          // call the auth service
          Auth.login(this, credentials, this.$router.currentRoute.query.redirect)
        }
      })
    }
  }
}
</script>

<style>
  .login {
    margin-top: -3.25rem;
  }
</style>
