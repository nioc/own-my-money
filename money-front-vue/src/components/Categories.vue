<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: this.$t('labels.home')},
          {link: '/categories', icon: 'fa-folder-open-o', text: this.$tc('objects.category', 2), isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.category', 2) }}</h1>
        <p class="subtitle has-text-grey">{{ $t('labels.categoriesLabel') }}</p>
        <ul class="menu-list">
          <li v-for="category in categories" :key="category.id" :class="{'item-disabled': !category.status}">
            <router-link :to="{name: 'category', params: {id: category.id}}"><i class="fa fa-fw fa-mr" :class="category.icon" />{{ category.label }}&nbsp;<span v-if="!category.isBudgeted" class="has-text-grey-light" :title="$t('labels.isNotBudgeted')"><i class="fa fa-fw fa-bell-slash-o" /></span></router-link>
            <ul>
              <li v-for="subcategory in category.sub" :key="subcategory.id" :class="{'item-disabled': !subcategory.status}">
                <router-link :to="{name: 'subcategory', params: {id: subcategory.id, pid: category.id}}">{{ subcategory.label }}</router-link>
              </li>
              <li>
                <router-link :to="{name: 'subcategory', params: {id: 'new', pid: category.id}}" class="has-text-grey-light"><i class="fa fa-fw fa-plus-square-o" />{{ $t('actions.addSubcategory') }}</router-link>
              </li>
            </ul>
          </li>
          <li>
            <router-link :to="{name: 'category', params: {id: 'new'}}" class="has-text-grey-light"><i class="fa fa-fw fa-plus-square-o" />{{ $t('actions.addCategory') }}</router-link>
          </li>
        </ul>
        <b-loading :is-full-page="false" :active.sync="isLoading" />
      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
export default {
  name: 'Categories',
  components: {
    Breadcrumb,
  },
  data () {
    return {
      rCategories: this.$resource(Config.API_URL + 'categories{/id}'),
      categories: [],
      isLoading: false,
    }
  },
  mounted () {
    this.get()
  },
  methods: {
    get () {
      this.isLoading = true
      this.rCategories.query({ status: 'all' })
        .then((response) => {
          this.categories = response.body
          // put categories in local storage for future usage
          localStorage.setItem('categories', JSON.stringify(this.categories))
        }, (response) => {
        // @TODO : add error handling
          console.error(response)
        })
        .finally(function () {
          // remove loading overlay when API replies
          this.isLoading = false
        })
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
