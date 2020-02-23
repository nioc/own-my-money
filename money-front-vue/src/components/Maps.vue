<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: this.$t('labels.home')},
          {link: '/maps', icon: 'fa-random', text: this.$tc('objects.transactionMapping', 2), isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.transactionMapping', 2) }}</h1>
        <p class="subtitle has-text-grey">{{ $t('labels.mapsLabel') }}</p>
        <ul class="menu-list">
          <li v-for="map in maps" :key="map.code">
            <router-link :to="{name: 'map', params: {code: map.code}}">{{ map.label }}</router-link>
          </li>
          <li>
            <router-link :to="{name: 'newMap'}" class="has-text-grey-light"><i class="fa fa-fw fa-plus-square-o" />{{ $t('actions.addMap') }}</router-link>
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
  name: 'Mappings',
  components: {
    Breadcrumb,
  },
  data () {
    return {
      rMaps: this.$resource(Config.API_URL + 'maps{/id}'),
      maps: [],
      isLoading: false,
    }
  },
  mounted () {
    this.get()
  },
  methods: {
    get () {
      this.isLoading = true
      this.rMaps.query({})
        .then((response) => {
          this.maps = response.body
          // put maps in local storage for future usage
          localStorage.setItem('maps', JSON.stringify(this.maps))
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
