import Vue from 'vue'
import Router from 'vue-router'
import Accounts from '@/components/Accounts'
import Account from '@/components/Account'
import Login from '@/components/Login'
import Profile from '@/components/Profile'
import Home from '@/components/Home'
Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home,
      meta: {title: 'Home'}
    },
    {
      path: '/accounts',
      name: 'accounts',
      component: Accounts,
      meta: {title: 'Accounts'}
    },
    {
      path: '/accounts/:id',
      name: 'account',
      component: Account,
      meta: {title: 'Account :id'}
    },
    {
      path: '/login',
      name: 'login',
      component: Login,
      meta: {title: 'Login'}
    },
    {
      path: '/profile',
      name: 'profile',
      component: Profile,
      meta: {title: 'Profile'}
    }
  ]
})
