<template>
  <div id="app">
    <nav v-if="store.user.authenticated" class="navbar is-dark is-fixed-top">
      <div class="navbar-brand">
        <router-link class="navbar-item" to="/"><img src="./assets/icon-whitesmoke.svg" class="fa-mr" alt="">OwnMyMoney</router-link>
        <a id="navbar-burger" role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" @click="toggleMenu">
          <span aria-hidden="true" class="is-primary" />
          <span aria-hidden="true" />
          <span aria-hidden="true" />
        </a>
      </div>
      <div id="navbar-menu" class="navbar-menu">
        <div class="navbar-start">
          <router-link class="navbar-item" to="/accounts"><i class="fas fa-table fa-fw fa-mr" />{{ $tc('objects.account', 2) }}</router-link>
        </div>
        <div class="navbar-end">
          <div v-if="store.user.scope.admin" class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link"><i class="fas fa-wrench fa-fw fa-mr" />{{ $t('labels.systemSettings') }}</a>
            <div class="navbar-dropdown is-right">
              <router-link class="navbar-item" to="/categories"><i class="far fa-folder-open fa-fw fa-mr" />{{ $tc('objects.category', 2) }}</router-link>
              <router-link class="navbar-item" to="/maps"><i class="fas fa-random fa-fw fa-mr" />{{ $tc('objects.transactionMapping', 2) }}</router-link>
              <router-link class="navbar-item" to="/users"><i class="fas fa-users fa-fw fa-mr" />{{ $tc('objects.user', 2) }}</router-link>
            </div>
          </div>
          <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link"><i class="fas fa-globe-europe fa-fw fa-mr" />{{ $t('labels.language') }}</a>
            <div class="navbar-dropdown is-right">
              <a class="navbar-item" :class="{'router-link-exact-active': $i18n.locale === 'en'}" @click="setLocale('en')">English</a>
              <a class="navbar-item" :class="{'router-link-exact-active': $i18n.locale === 'fr'}" @click="setLocale('fr')">Français</a>
            </div>
          </div>
          <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link"><i class="fas fa-user fa-fw fa-mr" />{{ store.user.login }}</a>
            <div class="navbar-dropdown is-right">
              <router-link class="navbar-item" to="/profile"><i class="fas fa-user-circle fa-fw fa-mr" />{{ $t('labels.profile') }}</router-link>
              <router-link class="navbar-item" to="/patterns"><i class="fas fa-magic fa-fw fa-mr" />{{ $tc('objects.pattern', 2) }}</router-link>
              <hr class="navbar-divider">
              <router-link class="navbar-item" to="/about"><i class="fas fa-info-circle fa-fw fa-mr" />{{ $t('labels.about') }}</router-link>
              <a class="navbar-item" href="https://github.com/nioc/own-my-money/issues/new" target="_blank" rel="noreferrer"><i class="fas fa-bug fa-fw fa-mr" />{{ $t('labels.reportABug') }}</a>
              <hr class="navbar-divider">
              <a class="navbar-item" @click="logout()"><i class="fas fa-sign-out-alt fa-fw fa-mr" />{{ $t('labels.logout') }}</a>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <router-view v-slot="{Component, route}">
      <transition-router>
        <component :is="Component" :key="route.path" />
      </transition-router>
    </router-view>
    <update-pwa />
  </div>
</template>

<script>
import { setHeader } from '@/services/Http'
import Auth from '@/services/Auth'
import UpdatePwa from '@/components/UpdatePwa.vue'
import TransitionRouter from '@/components/TransitionRouter.vue'
import { configValidator } from '@/services/Validator'
import { useStore } from '@/store'
import { ConfigProgrammatic } from '@oruga-ui/oruga-next'

export default {
  name: 'App',
  components: {
    UpdatePwa,
    TransitionRouter,
  },
  setup() {
    const store = useStore()
    return { store }
  },
  data () {
    return {
      isOnline: window.navigator.onLine,
    }
  },
  mounted () {
    this.store.setConnectivity(this.isOnline)
    window.addEventListener('offline', this.notifyConnectivity)
    window.addEventListener('online', this.notifyConnectivity)
  },
  methods: {
    logout () {
      Auth.logout()
      this.store.reset()
      this.$router.replace({ name: 'login' })
    },
    toggleMenu (e) {
      e.target.classList.toggle('is-active')
      document.getElementById('navbar-menu').classList.toggle('is-active')
    },
    notifyConnectivity (event) {
      this.isOnline = event.type === 'online'
      this.store.setConnectivity(this.isOnline)
      this.$oruga.notification.open({
        message: this.isOnline ? this.$t('labels.isOnline') : this.$t('labels.isOffline'),
        variant: this.isOnline ? 'success' : 'danger',
        position: 'bottom',
        queue: false,
        duration: 1000,
        rootClass: 'toast-notification',
      })
    },
    setLocale (locale) {
      setHeader('Accept-Language', locale)
      document.querySelector('html').setAttribute('lang', locale)
      this.$i18n.locale = locale
      this.$dayjs.locale(locale)
      const localeData = this.$dayjs.localeData()
      ConfigProgrammatic.setOptions({
        locale,
        datepicker: {
          firstDayOfWeek: localeData.firstDayOfWeek(),
          monthNames: localeData.months(),
          dayNames: localeData.weekdaysMin(),
        },
      })
      configValidator({
        locale,
      })
    },
  },
}
</script>
