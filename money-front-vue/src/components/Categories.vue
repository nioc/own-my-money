<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fas fa-home', text: $t('labels.home')},
          {link: '/categories', icon: 'far fa-folder-open', text: $tc('objects.category', 2), isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.category', 2) }}</h1>
        <p class="subtitle has-text-grey">{{ $t('labels.categoriesLabel') }}</p>
        <ul class="menu-list">
          <li v-for="category in store.categories" :key="category.id" :class="{'item-disabled': !category.status}">
            <router-link :to="{name: 'category', params: {id: category.id}}"><span class="icon mr-2"><i :class="category.icon" /></span><span>{{ category.label }}<span v-if="!category.isBudgeted" class="icon has-text-grey-light ml-2" :title="$t('labels.isNotBudgeted')"><i class="fas fa-bell-slash" /></span></span></router-link>
            <ul>
              <li v-for="subcategory in category.sub" :key="subcategory.id" :class="{'item-disabled': !subcategory.status}">
                <router-link :to="{name: 'subcategory', params: {id: subcategory.id, pid: category.id}}">{{ subcategory.label }}</router-link>
              </li>
              <li>
                <router-link :to="{name: 'subcategory', params: {id: 'new', pid: category.id}}" class="button is-secondary is-small is-inline-flex mt-3"><span class="icon"><i class="fas fa-plus-square" /></span><span>{{ $t('actions.addSubcategory') }}</span></router-link>
              </li>
            </ul>
          </li>
          <li>
            <router-link :to="{name: 'category', params: {id: 'new'}}" class="button is-primary is-inline-flex mt-3"><span class="icon"><i class="fas fa-plus-square" /></span><span>{{ $t('actions.addCategory') }}</span></router-link>
          </li>
        </ul>
        <o-loading :active="isLoading" :full-page="false" />
      </div>
    </div>
  </section>
</template>

<script>
import Breadcrumb from '@/components/Breadcrumb.vue'
import { useStore } from '@/store'

export default {
  name: 'Categories',
  components: {
    Breadcrumb,
  },
  setup() {
    const store = useStore()
    return { store }
  },
  data () {
    return {
      isLoading: false,
    }
  },
  mounted () {
    this.get()
  },
  methods: {
    async get () {
      this.isLoading = true
      await this.store.loadCategories()
      this.isLoading = false
    },
  },
}
</script>

<style scoped>
.item-disabled a {
  text-decoration: line-through;
  color: hsl(0, 0%, 48%);
}
</style>
