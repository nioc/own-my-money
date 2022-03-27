<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fas fa-home', text: $t('labels.home')},
          {link: '/maps', icon: 'fas fa-random', text: $tc('objects.transactionMapping', 2), isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.transactionMapping', 2) }}</h1>
        <p class="subtitle has-text-grey">{{ $t('labels.mapsLabel') }}</p>
        <ul class="menu-list">
          <li v-for="map in store.maps" :key="map.code">
            <router-link :to="{name: 'map', params: {code: map.code}}">{{ map.label }}</router-link>
          </li>
        </ul>
        <router-link :to="{name: 'newMap'}" class="button is-primary mt-3"><span class="icon"><i class="fas fa-plus-square" /></span><span>{{ $t('actions.addMap') }}</span></router-link>
        <o-loading :active="isLoading" :full-page="false" />
      </div>
    </div>
  </section>
</template>

<script>
import Breadcrumb from '@/components/Breadcrumb.vue'
import { useStore } from '@/store'

export default {
  name: 'Mappings',
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
      await this.store.loadMaps()
      this.isLoading = false
    },
  },
}
</script>
