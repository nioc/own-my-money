<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fas fa-home', text: $t('labels.home')},
          {link: '/about', icon: 'fas fa-info-circle', text: $t('labels.about'), isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $t('labels.about') }}</h1>
        <p class="subtitle has-text-grey">Own my money</p>
        <div v-if="user.scope.admin" class="field">
          <div class="control">
            <div class="select">
              <select v-model="releaseChannel" name="parent" :disabled="!isOnline || !releaseChannelLoaded" :title="$t('labels.releaseChannel')" @change="setSetting('releaseChannel', releaseChannel)">
                <option value="stable">{{ $t('labels.stable') }}</option>
                <option value="beta">{{ $t('labels.beta') }}</option>
              </select>
            </div>
          </div>
        </div>
        <div v-if="isLoaded" class="content field is-grouped is-grouped-multiline">
          <div class="control">
            <div class="tags has-addons" :title="`Version Git: ${gitVersion}`"><span class="tag is-dark">Installed version</span><span class="tag" :class="[isUpToDate ? 'is-success': 'is-danger']">{{ version.installed }}</span></div>
          </div>
          <div v-if="!isUpToDate" class="control">
            <div class="tags has-addons"><span class="tag is-dark">Latest version</span><span class="tag is-info">{{ version.latest }}</span></div>
          </div>
          <div class="control">
            <a href="https://github.com/nioc/own-my-money/releases/latest" target="_blank" rel="noreferrer">Changelog</a>
          </div>
        </div>
        <section v-if="!isUpToDate && user.scope.admin" class="content">
          <div v-if="!version.isContainerized">
            <button class="button is-primary" :class="{'is-loading': isUpdating}" :disabled="!isOnline || isUpdating" @click="update"><span class="icon"><i class="fa fa-wrench" /></span><span>{{ $t('actions.updateTo') }} {{ version.latest }}</span></button>
            <!-- eslint-disable-next-line vue/require-v-for-key-->
            <pre v-if="updateLogs" class="section content"><span v-for="updateLog in updateLogs" class="is-block">{{ $dayjs(updateLog.timestamp).format("HH:mm:ss") }}   {{ updateLog.message }}</span></pre>
          </div>
          <!-- eslint-disable-next-line vue/no-v-html -->
          <div v-else class="notification is-danger is-light" v-html="$t('labels.containerUpdateText')" />
        </section>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <section class="content" v-html="$t('labels.aboutText')" />
      </div>
    </div>
  </section>
</template>

<script>
import Auth from '@/services/Auth'
import Breadcrumb from '@/components/Breadcrumb.vue'

export default {
  name: 'About',
  components: {
    Breadcrumb,
  },
  data () {
    return {
      version: {
        installed: '',
        latest: '',
        isContainerized: false,
      },
      isUpToDate: true,
      user: Auth.getProfile(),
      updateLogs: null,
      isLoaded: false,
      isUpdating: false,
      releaseChannel: null,
      releaseChannelLoaded: false,
      // eslint-disable-next-line no-undef
      gitVersion: GITVERSION,
    }
  },
  computed: {
    isOnline () {
      return this.$store.isOnline
    },
  },
  mounted () {
    this.get()
    this.getSetting('releaseChannel')
  },
  methods: {
    async get () {
      try {
        const response = await this.$http.get('setup/versions/latest')
        this.version = response.data
        if (this.version.installed !== this.version.latest) {
          this.isUpToDate = false
        }
      } catch (error) {
        console.log(error)
      }
      this.isLoaded = true
    },
    async getSetting (key) {
      if (this.user.scope.admin) {
        try {
          const response = await this.$http.get(`settings/${key}`)
          switch (key) {
          case 'releaseChannel':
            this.releaseChannelLoaded = true
            this.releaseChannel = response.data.value
            break
          }
        } catch (error) {
          console.error(error)
        }
      }
    },
    async setSetting (key, value) {
      if (this.user.scope.admin) {
        try {
          const response = await this.$http.put(`settings/${key}`, { key, value })
          switch (key) {
          case 'releaseChannel':
            this.releaseChannel = response.data.value
            this.get()
            break
          }
        } catch (error) {
          console.error(error)
        }
      }
    },
    async update() {
      this.updateLogs = ''
      this.isUpdating = true
      try {
        const response = await this.$http.post('setup/versions/latest')
        this.updateLogs = response.data
        await this.get()
      } catch (error) {
        this.updateLogs = error.message
      }
      this.isUpdating = false
    },
  },
}
</script>
