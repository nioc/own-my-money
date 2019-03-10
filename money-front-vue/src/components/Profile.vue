<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: this.$t('labels.home')},
          {link: '/profile', icon: 'fa-user-circle', text: this.$t('labels.profile'), isActive: true}
        ]">
      </breadcrumb>
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $t('labels.profile') }}</h1>
        <p class="subtitle has-text-grey">{{ $t('labels.profileLabel') }}</p>
        <form @submit.prevent="validateBeforeSubmit" novalidate class="section is-max-width-form">

          <div class="field is-horizontal">
            <div class="field-label is-normal">
              <label class="label">{{ $t('fieldnames.login') }}</label>
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
              <label class="label">{{ $t('fieldnames.newPassword') }}</label>
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
              <label class="label">{{ $t('fieldnames.email') }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-left has-icons-right">
                  <input class="input" type="email" name="email" placeholder="Type your mail address" v-model="user.mail" v-validate="'required|email'" :class="{'input': true, 'is-danger': errors.has('email') }">
                  <span class="icon is-small is-left">
                    <i class="fa fa-envelope"></i>
                  </span>
                  <span class="icon is-small is-right" v-show="errors.has('email')">
                    <i class="fa fa-exclamation-triangle"></i>
                  </span>
                  <span v-show="errors.has('email')" class="help is-danger">{{errors.first('email')}}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal">
              <label class="label">{{ $t('fieldnames.language') }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control">
                  <div class="select">
                    <select name="language" v-model="user.language">
                      <option value="">{{ $t('labels.automatic') }}</option>
                      <option value="fr">Français</option>
                      <option value="en">English</option>
                    </select>
                  </div>
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
                  <button class="button is-primary" :disabled="!isOnline"><span class="fa fa-save fa-fw" aria-hidden="true"></span>&nbsp;{{ $t('actions.save') }}</button>
                </div>
                <div class="control">
                  <a @click="$router.go(-1)" class="button is-light"><span class="fa fa-ban fa-fw" aria-hidden="true"></span>&nbsp;{{ $t('actions.cancel') }}</a>
                </div>
              </div>
            </div>
          </div>
          <div class="field is-horizontal">
            <div class="field-label is-normal">
            </div>
            <div class="field-body">
              <div class="message is-danger" v-if="error">
                <div class="message-body">
                  {{ error }}
                </div>
              </div>
            </div>
          </div>
          <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>
        </form>
        <user-connections v-bind:id="this.user.id"></user-connections>
      </div>
    </div>
  </section>
</template>

<script>
import Auth from './../services/Auth'
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
import UserConnections from '@/components/UserConnections'
export default {
  name: 'profile',
  components: {
    UserConnections,
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
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    }
  },
  methods: {
    validateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          this.isLoading = true
          // if validation is ok, call user API
          this.rUsers.update({ id: this.user.id }, { sub: this.user.id, login: this.user.login, password: this.password, mail: this.user.mail, language: this.user.language })
            .then((response) => {
            }, (response) => {
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
