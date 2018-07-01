<template>
  <section class="hero">
     <div class="hero-body">
       <div class="container">
         <div class="column is-4 is-offset-4">
          <div class="box">
            <form @submit.prevent="validateBeforeSubmit" novalidate>
              <h3 class="title has-text-grey">Profile</h3>
              <p class="subtitle has-text-grey">Update your money account</p>
              <div class="field">
                <label class="label">Login</label>
                <div class="control has-icons-left has-icons-right">
                  <input class="input" type="text" name="login" placeholder="Type your new login" v-model="user.login" v-validate="'required|min:3|alpha'" :class="{'input': true, 'is-danger': errors.has('login') }">
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
                <label class="label">New password</label>
                <div class="control has-icons-left has-icons-right">
                  <input class="input" type="password" name="password" placeholder="Type your new password" v-model="password" v-validate="'required|min:5'" :class="{'input': true, 'is-danger': errors.has('password') }">
                  <span class="icon is-small is-left">
                    <i class="fa fa-lock"></i>
                  </span>
                  <span class="icon is-small is-right" v-show="errors.has('password')">
                    <i class="fa fa-exclamation-triangle"></i>
                  </span>
                  <span v-show="errors.has('password')" class="help is-danger">{{errors.first('password')}}</span>
                </div>
              </div>
              <div class="field is-grouped">
                <div class="control">
                  <button class="button is-primary"><span class="fa fa-save fa-fw" aria-hidden="true"></span>&nbsp;Save</button>
                </div>
                <div class="control">
                  <a @click="$router.go(-1)" class="button is-light"><span class="fa fa-ban fa-fw" aria-hidden="true"></span>&nbsp;Cancel</a>
                </div>
              </div>
              <div class="message is-danger" v-if="error">
                <!-- <div class="message-header has-text-centered">Error<button class="delete" aria-label="delete"></button></div> -->
                <div class="message-body">
                  {{ error }}
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="hero-foot">
      <div class="container box">
        <nav class="breadcrumb" aria-label="breadcrumbs">
          <ul>
            <li>
              <router-link to="/">
                <span class="icon is-small">
                  <i class="fa fa-home" aria-hidden="true"></i>
                </span>
                <span>Home</span>
              </router-link>
            </li>
            <li class="is-active">
              <router-link to="/">
                <span>Profile</span>
              </router-link>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </section>
</template>

<script>
import Auth from './../services/Auth'
import Config from './../services/Config'
export default {
  name: 'profile',
  data () {
    return {
      user: Auth.getProfile(),
      password: '',
      error: '',
      // resources
      rUsers: this.$resource(Config.API_URL + 'users{/id}')
    }
  },
  methods: {
    validateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          // if validation is ok, call user API
          this.rUsers.update({id: this.user.id}, {sub: this.user.id, login: this.user.login, password: this.password})
            .then(response => {
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
