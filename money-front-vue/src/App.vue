<template>
  <div id="app">
    <nav v-if="user.authenticated" class="navbar is-dark is-fixed-top">
      <div class="navbar-brand">
        <router-link class="navbar-item" to="/"><img src="./assets/icon-whitesmoke.svg" class="fa-mr">OwnMyMoney</router-link>
        <a id="navbar-burger" role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" @click="toggleMenu">
          <span aria-hidden="true" class="is-primary" />
          <span aria-hidden="true" />
          <span aria-hidden="true" />
        </a>
      </div>
      <div id="navbar-menu" class="navbar-menu">
        <div class="navbar-start">
          <router-link class="navbar-item" to="/accounts"><i class="fa fa-table fa-fw fa-mr" />{{ $tc('objects.account', 2) }}</router-link>
        </div>
        <div class="navbar-end">
          <div v-if="user.scope.admin" class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link"><i class="fa fa-wrench fa-fw fa-mr" />{{ $t('labels.systemSettings') }}</a>
            <div class="navbar-dropdown is-right">
              <router-link class="navbar-item" to="/categories"><i class="fa fa-folder-open-o fa-fw fa-mr" />{{ $tc('objects.category', 2) }}</router-link>
              <router-link class="navbar-item" to="/maps"><i class="fa fa-random fa-fw fa-mr" />{{ $tc('objects.transactionMapping', 2) }}</router-link>
              <router-link class="navbar-item" to="/users"><i class="fa fa-users fa-fw fa-mr" />{{ $tc('objects.user', 2) }}</router-link>
            </div>
          </div>
          <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link"><i class="fa fa-globe fa-fw fa-mr" />{{ $t('labels.language') }}</a>
            <div class="navbar-dropdown is-right">
              <a class="navbar-item" :class="{'router-link-exact-active': this.$i18n.locale === 'en'}" @click="setLocale('en')">English</a>
              <a class="navbar-item" :class="{'router-link-exact-active': this.$i18n.locale === 'fr'}" @click="setLocale('fr')">Français</a>
            </div>
          </div>
          <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link"><i class="fa fa-user fa-fw fa-mr" />{{ user.login }}</a>
            <div class="navbar-dropdown is-right">
              <router-link class="navbar-item" to="/profile"><i class="fa fa-user-circle fa-fw fa-mr" />{{ $t('labels.profile') }}</router-link>
              <router-link class="navbar-item" to="/patterns"><i class="fa fa-magic fa-fw fa-mr" />{{ $tc('objects.pattern', 2) }}</router-link>
              <hr class="navbar-divider">
              <router-link class="navbar-item" to="/about"><i class="fa fa-info-circle fa-fw fa-mr" />{{ $t('labels.about') }}</router-link>
              <a class="navbar-item" href="https://github.com/nioc/own-my-money/issues/new" target="_blank" rel="noreferrer"><i class="fa fa-bug fa-fw fa-mr" />{{ $t('labels.reportABug') }}</a>
              <hr class="navbar-divider">
              <a class="navbar-item" @click="logout()"><i class="fa fa-sign-out fa-fw fa-mr" />{{ $t('labels.logout') }}</a>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <transition-router>
      <router-view :key="$route.fullPath" />
    </transition-router>
  </div>
</template>

<script>
import Auth from './services/Auth'
import Bus from './services/Bus'
import TransitionRouter from '@/components/TransitionRouter'
import Vue from 'vue'
export default {
  name: 'App',
  components: {
    TransitionRouter,
  },
  data () {
    return {
      isOnline: window.navigator.onLine,
      user: Auth.getProfile(),
    }
  },
  mounted () {
    Bus.$on('user-logged', (user) => {
      this.user = user
    })
    this.$store.commit('setConnectivity', this.isOnline)
    window.addEventListener('offline', this.notifyConnectivity)
    window.addEventListener('online', this.notifyConnectivity)
  },
  methods: {
    logout () {
      this.user = Auth.logout()
      this.$router.replace({ name: 'login' })
    },
    toggleMenu (e) {
      e.target.classList.toggle('is-active')
      document.getElementById('navbar-menu').classList.toggle('is-active')
    },
    notifyConnectivity (event) {
      this.isOnline = event.type === 'online'
      this.$store.commit('setConnectivity', this.isOnline)
      const toast = {}
      toast.message = this.isOnline ? this.$t('labels.isOnline') : this.$t('labels.isOffline')
      toast.type = this.isOnline ? 'is-success' : 'is-danger'
      toast.position = 'is-bottom'
      toast.queue = false
      this.$toast.open(toast)
    },
    setLocale (locale) {
      Vue.http.headers.common['Accept-Language'] = locale
      document.querySelector('html').setAttribute('lang', locale)
      this.$i18n.locale = locale
      this.$moment.locale(locale)
      const localeData = this.$moment.localeData()
      this.$buefy.setOptions({
        defaultIconPack: 'fa',
        defaultFirstDayOfWeek: localeData.firstDayOfWeek(),
        defaultMonthNames: localeData.months(),
        defaultDayNames: localeData.weekdaysMin(),
        defaultDateFormatter: (date) => this.$moment(date, 'L').format('L'),
      })
    },
  },
}
</script>
