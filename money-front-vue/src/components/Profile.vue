<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fas fa-home', text: $t('labels.home')},
          {link: '/profile', icon: 'fas fa-user-circle', text: $t('labels.profile'), isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $t('labels.profile') }}</h1>
        <p class="subtitle has-text-grey">{{ $t('labels.profileLabel') }}</p>
        <form novalidate class="section is-max-width-form" @submit.prevent="validateBeforeSubmit">

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal">
              <label class="label">{{ $t('fieldnames.login') }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-left has-icons-right">
                  <input v-model="user.login" class="input" type="text" name="login" placeholder="Type your new login" :class="{'input': true, 'is-danger': errors.login}" @input="validate">
                  <span class="icon is-small is-left">
                    <i class="fa fa-user" />
                  </span>
                  <span v-show="errors.login" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.login" class="help is-danger">{{ errors.login.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal">
              <label class="label">{{ $t('fieldnames.newPassword') }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-left has-icons-right">
                  <input v-model="password" class="input" type="password" name="password" placeholder="Type your new password" :class="{'input': true, 'is-danger': errors.password}" @input="validate">
                  <span class="icon is-small is-left">
                    <i class="fa fa-lock" />
                  </span>
                  <span v-show="errors.password" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.password" class="help is-danger">{{ errors.password.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal">
              <label class="label">{{ $t('fieldnames.email') }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-left has-icons-right">
                  <input v-model="user.mail" class="input" type="email" name="email" placeholder="Type your mail address" :class="{'input': true, 'is-danger': errors.email}" @input="validate">
                  <span class="icon is-small is-left">
                    <i class="fa fa-envelope" />
                  </span>
                  <span v-show="errors.email" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.email" class="help is-danger">{{ errors.email.message }}</span>
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
                    <select v-model="user.language" name="language">
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
            <div class="field-label is-normal" />
            <div class="field-body">
              <div class="field is-grouped">
                <div class="control">
                  <button class="button is-primary" :disabled="!isOnline"><span class="icon"><i class="fas fa-save" aria-hidden="true" /></span><span>{{ $t('actions.save') }}</span></button>
                </div>
                <div class="control">
                  <a class="button is-light" @click="$router.go(-1)"><span class="icon"><i class="fas fa-ban" aria-hidden="true" /></span><span>{{ $t('actions.cancel') }}</span></a>
                </div>
              </div>
            </div>
          </div>
          <div class="field is-horizontal">
            <div class="field-label is-normal" />
            <div class="field-body">
              <div v-if="error" class="message is-danger">
                <div class="message-body">
                  {{ error }}
                </div>
              </div>
            </div>
          </div>
          <o-loading :active="isLoading" :full-page="false" />
        </form>
        <user-connections :id="user.id" />
      </div>
    </div>
  </section>
</template>

<script>
import Auth from '@/services/Auth'
import { useValidator } from '@/services/Validator'
import Breadcrumb from '@/components/Breadcrumb.vue'
import UserConnections from '@/components/UserConnections.vue'

export default {
  name: 'Profile',
  components: {
    UserConnections,
    Breadcrumb,
  },
  setup() {
    const { errors, validationRules, validate, validateForm } = useValidator()
    return { errors, validationRules, validate, validateForm }
  },
  data () {
    return {
      user: Auth.getProfile(),
      password: '',
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
      login: 'required|min:3|alpha_num',
      password: 'required|min:5',
      email: 'required|email',
    }
  },
  methods: {
    async validateBeforeSubmit (submitEvent) {
      this.error = ''
      if (!this.validateForm(submitEvent)) {
        return
      }
      this.isLoading = true
      try {
        await this.$http.put(`users/${this.user.id}`, { sub: this.user.id, login: this.user.login, password: this.password, mail: this.user.mail, language: this.user.language })
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
  },
}
</script>
