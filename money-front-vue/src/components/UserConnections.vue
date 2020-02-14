<template>
  <b-table :data=connections :mobile-cards="false" :striped="true" narrowed default-sort="creation" default-sort-direction="desc">
    <template slot-scope="props">
      <b-table-column field="creation" :label="$t('fieldnames.date')" sortable>
        {{ props.row.creation | moment("L LTS") }}
      </b-table-column>
      <b-table-column field="ip" :label="$t('fieldnames.ipAddress')" sortable>
        {{ props.row.ip }}
      </b-table-column>
      <b-table-column field="userAgent" :label="$t('fieldnames.userAgent')" sortable :title="props.row.userAgent">
        {{ props.row.browser }} / {{ props.row.os }}<span v-if="props.row.device"> / {{ props.row.device }}</span>
      </b-table-column>
    </template>
  </b-table>
</template>

<script>
import UAParser from 'ua-parser-js'
import Config from './../services/Config'
export default {
  props: {
    id: {
      type: Number,
      required: true
    }
  },
  data () {
    return {
      connections: [],
      // resources
      rConnections: this.$resource(Config.API_URL + 'users/' + this.id + '/tokens')
    }
  },
  methods: {
    get () {
      this.rConnections.query()
        .then((response) => {
          this.connections = response.body
          const parser = new UAParser()
          this.connections.forEach(function (connection) {
            parser.setUA(connection.userAgent)
            const userAgent = parser.getResult()
            connection.browser = userAgent.browser.name + ' ' + userAgent.browser.major
            connection.os = userAgent.os.name + ' ' + userAgent.os.version
            if (userAgent.device.model) {
              connection.device = userAgent.device.model
            }
          })
        }, (response) => {
          // @todo : handle error
          if (response.body.message) {
            console.log(response.body.message)
            return
          }
          console.log(response.status + ' - ' + response.statusText)
        })
    }
  },
  mounted () {
    this.get()
  }
}
</script>
