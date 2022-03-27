import { createRouter, createWebHashHistory } from 'vue-router'
import Accounts from '@/components/Accounts.vue'
import Account from '@/components/Account.vue'
import Login from '@/components/Login.vue'
import Home from '@/components/Home.vue'

export default new createRouter({
  history: createWebHashHistory(),
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
      component: () => import('@/components/Setup.vue'),
      meta: {
        title: 'Setup server',
      },
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('@/components/About.vue'),
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
      component: () => import('@/components/Profile.vue'),
      meta: {
        title: 'Profile',
      },
    },
    {
      path: '/patterns',
      name: 'patterns',
      component: () => import('@/components/Patterns.vue'),
      meta: {
        title: 'Patterns',
      },
    },
    {
      path: '/users',
      name: 'users',
      component: () => import('@/components/Users.vue'),
      meta: {
        title: 'Manage users',
      },
    },
    {
      path: '/maps',
      name: 'maps',
      component: () => import('@/components/Maps.vue'),
      meta: {
        title: 'Edit maps',
      },
    },
    {
      path: '/maps/new',
      name: 'newMap',
      component: () => import('@/components/Map.vue'),
      props: { isNew: true },
      meta: {
        transitionName: 'slide',
        title: 'Edit map :code',
      },
    },
    {
      path: '/maps/:code',
      name: 'map',
      component: () => import('@/components/Map.vue'),
      props: { isNew: false },
      meta: {
        transitionName: 'slide',
        title: 'Edit map :code',
      },
    },
    {
      path: '/categories',
      name: 'categories',
      component: () => import('@/components/Categories.vue'),
      meta: {
        title: 'Edit categories and subcategories',
      },
    },
    {
      path: '/categories/:id',
      name: 'category',
      component: () => import('@/components/Category.vue'),
      props: { isCategory: true },
      meta: {
        transitionName: 'slide',
        title: 'Edit category :id',
      },
    },
    {
      path: '/categories/:pid/subcategories/:id',
      name: 'subcategory',
      component: () => import('@/components/Category.vue'),
      props: { isCategory: false },
      meta: {
        transitionName: 'slide',
        title: 'Edit subcategory :id',
      },
    },
  ],
})
