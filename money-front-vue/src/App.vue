<template>
  <div id="app">
    <nav class="navbar is-dark">
      <div class="navbar-brand">
        <router-link class="navbar-item" to="/"><i class="fa fa-money fa-fw"/>&nbsp;OwnMyMoney</router-link>
      </div>
      <div class="navbar-menu">
        <div class="navbar-start">
          <router-link class="navbar-item" to="/accounts">Accounts</router-link>
        </div>
        <div class="navbar-end">
          <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link"><i class="fa fa-wrench fa-fw"/>&nbsp;System settings</a>
          </div>
          <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link"><i class="fa fa-user fa-fw"/>&nbsp;User</a>
            <div class="navbar-dropdown is-right">
              <a class="navbar-item"><i class="fa fa-user-circle fa-fw"/>&nbsp;Profile</a>
              <hr class="navbar-divider">
              <a class="navbar-item"><i class="fa fa-sign-out fa-fw"/>&nbsp;Logout</a>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: 'app'
}
</script>
