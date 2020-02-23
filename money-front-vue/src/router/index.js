import Vue from 'vue'
import Router from 'vue-router'
import Accounts from '@/components/Accounts'
import Account from '@/components/Account'
import Login from '@/components/Login'
import Home from '@/components/Home'
Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home,
      meta: {
        title: 'Home',
      },
    },
    {
      path: '/setup',
      name: 'setup',
      component: () => import(/* webpackChunkName: "user-settings" */ '@/components/Setup'),
      meta: {
        title: 'Setup server',
      },
    },
    {
      path: '/about',
      name: 'about',
      component: () => import(/* webpackChunkName: "user-settings" */ '@/components/About'),
      meta: {
        title: 'About',
      },
    },
    {
      path: '/accounts',
      name: 'accounts',
      component: Accounts,
      meta: {
        title: 'Accounts',
      },
    },
    {
      path: '/accounts/:id',
      name: 'account',
      component: Account,
      meta: {
        transitionName: 'slide',
        title: 'Account :id',
      },
    },
    {
      path: '/login',
      name: 'login',
      component: Login,
      meta: {
        title: 'Login',
      },
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import(/* webpackChunkName: "user-settings" */ '@/components/Profile'),
      meta: {
        title: 'Profile',
      },
    },
    {
      path: '/patterns',
      name: 'patterns',
      component: () => import(/* webpackChunkName: "user-settings" */ '@/components/Patterns'),
      meta: {
        title: 'Patterns',
      },
    },
    {
      path: '/users',
      name: 'users',
      component: () => import(/* webpackChunkName: "system-settings" */ '@/components/Users'),
      meta: {
        title: 'Manage users',
      },
    },
    {
      path: '/maps',
      name: 'maps',
      component: () => import(/* webpackChunkName: "system-settings" */ '@/components/Maps'),
      meta: {
        title: 'Edit maps',
      },
    },
    {
      path: '/maps/new',
      name: 'newMap',
      component: () => import(/* webpackChunkName: "system-settings" */ '@/components/Map'),
      props: { isNew: true },
      meta: {
        transitionName: 'slide',
        title: 'Edit map :code',
      },
    },
    {
      path: '/maps/:code',
      name: 'map',
      component: () => import(/* webpackChunkName: "system-settings" */ '@/components/Map'),
      props: { isNew: false },
      meta: {
        transitionName: 'slide',
        title: 'Edit map :code',
      },
    },
    {
      path: '/categories',
      name: 'categories',
      component: () => import(/* webpackChunkName: "system-settings" */ '@/components/Categories'),
      meta: {
        title: 'Edit categories and subcategories',
      },
    },
    {
      path: '/categories/:id',
      name: 'category',
      component: () => import(/* webpackChunkName: "system-settings" */ '@/components/Category'),
      props: { isCategory: true },
      meta: {
        transitionName: 'slide',
        title: 'Edit category :id',
      },
    },
    {
      path: '/categories/:pid/subcategories/:id',
      name: 'subcategory',
      component: () => import(/* webpackChunkName: "system-settings" */ '@/components/Category'),
      props: { isCategory: false },
      meta: {
        transitionName: 'slide',
        title: 'Edit subcategory :id',
      },
    },
  ],
})
