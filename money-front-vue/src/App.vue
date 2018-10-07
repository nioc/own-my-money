<template>
  <div id="app">
    <nav class="navbar is-dark is-fixed-top" v-if="user.authenticated">
      <div class="navbar-brand">
        <router-link class="navbar-item" to="/"><i class="fa fa-money fa-fw"/>&nbsp;OwnMyMoney</router-link>
        <a role="button" id="navbar-burger" class="navbar-burger" @click="toggleMenu" aria-label="menu" aria-expanded="false">
          <span aria-hidden="true" class="is-primary"></span>
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
        </a>
      </div>
      <div class="navbar-menu" id="navbar-menu">
        <div class="navbar-start">
          <router-link class="navbar-item" to="/accounts">Accounts</router-link>
        </div>
        <div class="navbar-end">
          <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link"><i class="fa fa-wrench fa-fw"/>&nbsp;System settings</a>
            <div class="navbar-dropdown is-right">
              <router-link class="navbar-item" to="/categories"><i class="fa fa-folder-open-o fa-fw"/>&nbsp;Categories</router-link>
            </div>
          </div>
          <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link"><i class="fa fa-user fa-fw"/>&nbsp;{{ this.user.login }}</a>
            <div class="navbar-dropdown is-right">
              <router-link class="navbar-item" to="/profile"><i class="fa fa-user-circle fa-fw"/>&nbsp;Profile</router-link>
              <hr class="navbar-divider">
              <a class="navbar-item" @click="logout()"><i class="fa fa-sign-out fa-fw"/>&nbsp;Logout</a>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <router-view></router-view>
  </div>
</template>

<script>
import Auth from './services/Auth'
import Bus from './services/Bus'
export default {
  name: 'app',
  data () {
    return {
      user: Auth.getProfile()
    }
  },
  methods: {
    logout () {
      this.user = Auth.logout()
      this.$router.replace({name: 'login'})
    },
    toggleMenu (e) {
      e.target.classList.toggle('is-active')
      document.getElementById('navbar-menu').classList.toggle('is-active')
    }
  },
  mounted: function () {
    Bus.$on('user-logged', (user) => {
      this.user = user
    })
  }
}
</script>
