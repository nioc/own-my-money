import Vue from 'vue'
import Router from 'vue-router'
import Accounts from '@/components/Accounts'
import Account from '@/components/Account'
import Login from '@/components/Login'
import Profile from '@/components/Profile'
import Patterns from '@/components/Patterns'
import Users from '@/components/Users'
import Categories from '@/components/Categories'
import Categorie from '@/components/Category'
import Mappings from '@/components/Maps'
import Mapping from '@/components/Map'
import Home from '@/components/Home'
import Setup from '@/components/Setup'
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
      path: '/setup',
      name: 'setup',
      component: Setup,
      meta: {title: 'Setup server'}
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
      path: '/patterns',
      name: 'patterns',
      component: Patterns,
      meta: {title: 'Patterns'}
    },
    {
      path: '/users',
      name: 'users',
      component: Users,
      meta: {title: 'Manage users'}
    },
    {
      path: '/maps',
      name: 'maps',
      component: Mappings,
      meta: {title: 'Edit maps'}
    },
    {
      path: '/maps/new',
      name: 'newMap',
      component: Mapping,
      props: { isNew: true },
      meta: {title: 'Edit map :code'}
    },
    {
      path: '/maps/:code',
      name: 'map',
      component: Mapping,
      props: { isNew: false },
      meta: {title: 'Edit map :code'}
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
