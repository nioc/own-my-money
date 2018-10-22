<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: 'Home'},
          {link: '/profile', icon: 'fa-user-circle', text: 'Profile', isActive: true}
        ]">
      </breadcrumb>
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">Profile</h1>
        <p class="subtitle has-text-grey">Update your money account</p>
        <form @submit.prevent="validateBeforeSubmit" novalidate class="section is-400px-form">
          <div class="field is-horizontal">
            <div class="field-label is-normal">
              <label class="label">Login</label>
            </div>
            <div class="field-body">
              <div class="field">
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
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal">
              <label class="label">New password</label>
            </div>
            <div class="field-body">
              <div class="field">
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
            </div>
          </div>
          <div class="field is-horizontal">
            <div class="field-label is-normal">
            </div>
            <div class="field-body">
              <div class="field is-grouped">
                <div class="control">
                  <button class="button is-primary"><span class="fa fa-save fa-fw" aria-hidden="true"></span>&nbsp;Save</button>
                </div>
                <div class="control">
                  <a @click="$router.go(-1)" class="button is-light"><span class="fa fa-ban fa-fw" aria-hidden="true"></span>&nbsp;Cancel</a>
                </div>
              </div>
            </div>
          </div>
          <div class="field is-horizontal">
            <div class="field-label is-normal">
            </div>
            <div class="field-body">
              <div class="message is-danger" v-if="error">
                <!-- <div class="message-header has-text-centered">Error<button class="delete" aria-label="delete"></button></div> -->
                <div class="message-body">
                  {{ error }}
                </div>
              </div>
            </div>
          </div>
          <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>
        </form>
      </div>
    </div>
  </section>
</template>

<script>
import Auth from './../services/Auth'
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
export default {
  name: 'profile',
  components: {
    Breadcrumb
  },
  data () {
    return {
      user: Auth.getProfile(),
      password: '',
      error: '',
      isLoading: false,
      // resources
      rUsers: this.$resource(Config.API_URL + 'users{/id}')
    }
  },
  methods: {
    validateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          this.isLoading = true
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
