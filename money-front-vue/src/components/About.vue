<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: 'Home'},
          {link: '/about', icon: 'fa-info-circle', text: 'About', isActive: true}
        ]">
      </breadcrumb>
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">About</h1>
        <p class="subtitle has-text-grey">Own my money</p>
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
          <button class="button is-primary" @click="update" :class="{ 'is-loading': isUpdating }" :disabled="isUpdating"><span class="icon"><i class="fa fa-wrench"/></span><span>Update to {{ version.latest }}</span></button>
          <pre class="section content" v-if="updateLogs"><span class="is-block" v-for="updateLog in updateLogs">{{ updateLog.timestamp | moment("HH:mm:ss") }}   {{ updateLog.message }}</span></pre>
        </section>
        <section class="content">
          <p>Own my money is a simple way to track your bank activity on any device connected to the web (pc, smartphone, tablet, ...).</p>
          <p>If you have any issues or feature requests, please create an <a href="https://github.com/nioc/own-my-money/issues" target="_blank">issue</a>.</p>
          <p>This project is licensed under the <a href="https://www.gnu.org/licenses/quick-guide-gplv3.html" target="_blank">GNU General Public License v3.0</a>.</p>
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
      // resources
      rVersions: this.$resource(Config.API_URL + 'setup/versions/latest')
    }
  },
  methods: {
    get () {
      this.rVersions.query()
        .then(response => {
          this.version = response.body
          if (this.version.installed !== this.version.latest) {
            this.isUpToDate = false
          }
        }, response => {
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
    update () {
      this.updateLogs = ''
      this.isUpdating = true
      this.rVersions.save()
        .then(response => {
          this.updateLogs = response.body
          this.get()
        }, response => {
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
  mounted: function () {
    this.get()
  }
}
</script>
