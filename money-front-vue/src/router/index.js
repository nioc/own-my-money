import Vue from 'vue'
import Router from 'vue-router'
import Accounts from '@/components/Accounts'
import Account from '@/components/Account'
import Login from '@/components/Login'
import Profile from '@/components/Profile'
import Categories from '@/components/Categories'
import Categorie from '@/components/Category'
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
    },
    {
      path: '/categories',
      name: 'categories',
      component: Categories,
      meta: {title: 'Edit categories and subcategories'}
    },
    {
      path: '/categories/:id',
      name: 'category',
      component: Categorie,
      props: { isCategory: true },
      meta: {title: 'Edit categorie :id'}
    },
    {
      path: '/categories/:pid/subcategories/:id',
      name: 'subcategory',
      component: Categorie,
      props: { isCategory: false },
      meta: {title: 'Edit subcategorie :id'}
    }
  ]
})
