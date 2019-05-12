<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: this.$t('labels.home')},
          {link: '/about', icon: 'fa-info-circle', text: this.$t('labels.about'), isActive: true}
        ]">
      </breadcrumb>
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $t('labels.about') }}</h1>
        <p class="subtitle has-text-grey">Own my money</p>
        <div class="field" v-if="user.scope.admin">
          <div class="control">
            <div class="select">
              <select name="parent" v-model="releaseChannel" v-on:change="setSetting('releaseChannel', releaseChannel)" :disabled="!isOnline || !releaseChannelLoaded" :title="$t('labels.releaseChannel')">
                <option value="stable">{{ $t('labels.stable') }}</option>
                <option value="beta">{{ $t('labels.beta') }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="content field is-grouped is-grouped-multiline" v-if="isLoaded">
          <div class="control">
            <div class="tags has-addons"><span class="tag is-dark">Installed version</span><span class="tag" :class="[isUpToDate ? 'is-success': 'is-danger']">{{ version.installed }}</span></div>
          </div>
          <div class="control" v-if="!isUpToDate">
            <div class="tags has-addons"><span class="tag is-dark">Latest version</span><span class="tag is-info">{{ version.latest }}</span></div>
          </div>
          <div class="control">
            <a href="https://github.com/nioc/own-my-money/releases/latest" target="_blank">Changelog</a>
          </div>
        </div>
        <section class="content" v-if="!isUpToDate && this.user.scope.admin">
          <button class="button is-primary" @click="update" :class="{ 'is-loading': isUpdating }" :disabled="!isOnline || isUpdating"><span class="icon"><i class="fa fa-wrench"/></span><span>{{ $t('actions.updateTo') }} {{ version.latest }}</span></button>
          <!-- eslint-disable-next-line vue/require-v-for-key-->
          <pre class="section content" v-if="updateLogs"><span class="is-block" v-for="updateLog in updateLogs">{{ updateLog.timestamp | moment("HH:mm:ss") }}   {{ updateLog.message }}</span></pre>
        </section>
        <section class="content" v-html="$t('labels.aboutText')">
        </section>
      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
import Auth from '@/services/Auth'
import Breadcrumb from '@/components/Breadcrumb'
export default {
  name: 'about',
  components: {
    Breadcrumb
  },
  data () {
    return {
      version: {
        installed: '',
        latest: ''
      },
      isUpToDate: true,
      user: Auth.getProfile(),
      updateLogs: null,
      isLoaded: false,
      isUpdating: false,
      releaseChannel: null,
      releaseChannelLoaded: false,
      // resources
      rSettings: this.$resource(Config.API_URL + 'settings{/key}'),
      rVersions: this.$resource(Config.API_URL + 'setup/versions/latest')
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    }
  },
  methods: {
    get () {
      this.rVersions.query()
        .then((response) => {
          this.version = response.body
          if (this.version.installed !== this.version.latest) {
            this.isUpToDate = false
          }
        }, (response) => {
          if (response.body.message) {
            console.log(response.body.message)
            return
          }
          console.log(response.status + ' - ' + response.statusText)
        })
        .finally(function () {
          // remove loading overlay when API replies
          this.isLoaded = true
        })
    },
    getSetting (key) {
      if (this.user.scope.admin) {
        this.rSettings.get({ key: key })
          .then((response) => {
            switch (key) {
              case 'releaseChannel':
                this.releaseChannelLoaded = true
                this.releaseChannel = response.body.value
                break
            }
          }, (response) => {
            console.error(response)
          })
      }
    },
    setSetting (key, value) {
      if (this.user.scope.admin) {
        this.rSettings.update({ key: key }, { key: key, value: value })
          .then((response) => {
            switch (key) {
              case 'releaseChannel':
                this.releaseChannel = response.body.value
                this.get()
                break
            }
          }, (response) => {
            console.error(response)
          })
      }
    },
    update () {
      this.updateLogs = ''
      this.isUpdating = true
      this.rVersions.save()
        .then((response) => {
          this.updateLogs = response.body
          this.get()
        }, (response) => {
          if (response.body.message) {
            this.updateLogs = response.body.message
            return
          }
          console.log(response.status + ' - ' + response.statusText)
        })
        .finally(function () {
          // remove loading overlay when API replies
          this.isUpdating = false
        })
    }
  },
  mounted () {
    this.get()
    this.getSetting('releaseChannel')
  }
}
</script>
