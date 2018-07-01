<template>
  <section class="hero is-light is-fullheight">
     <div class="hero-body">
       <div class="container has-text-centered">
         <div class="column is-4 is-offset-4">
          <div class="box">
            <form>
              <h3 class="title has-text-grey">Login</h3>
              <p class="subtitle has-text-grey">Access to your money account</p>
              <div class="field">
                  <div class="control has-icons-left has-icons-right">
                      <input class="input is-medium" type="text" name="login" placeholder="Login" v-model="credentials.login" v-validate="'required|min:3|alpha'" :class="{'input': true, 'is-danger': errors.has('login') }">
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
                      <input class="input is-medium" type="password" name="password" placeholder="Password" v-model="credentials.password" v-validate="'required|min:5'" :class="{'input': true, 'is-danger': errors.has('password') }">
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
                <button class="button is-block is-primary is-medium is-fullwidth" @click="submit"><span class="fa fa-sign-in fa-fw" aria-hidden="true"></span>&nbsp;Login</button>
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
      error: ''
    }
  },
  methods: {
    submit: function (e) {
      // prevent form submit
      e.preventDefault()
      // get credentials
      let credentials = {
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
  }
}
</script>
