<template>
  <o-table :data="connections" :mobile-cards="false" :striped="true" narrowed default-sort="creation" default-sort-direction="desc">
    <o-table-column v-slot="props" field="creation" :label="$t('fieldnames.date')" sortable>
      {{ $dayjs(props.row.creation).format("L LTS") }}
    </o-table-column>
    <o-table-column v-slot="props" field="ip" :label="$t('fieldnames.ipAddress')" sortable>
      {{ props.row.ip }}
    </o-table-column>
    <o-table-column v-slot="props" field="userAgent" :label="$t('fieldnames.userAgent')" sortable>
      <span :title="props.row.userAgent">{{ props.row.browser }} / {{ props.row.os }}<span v-if="props.row.device"> / {{ props.row.device }}</span></span>
    </o-table-column>
  </o-table>
</template>

<script>
import UAParser from 'ua-parser-js'

export default {
  name: 'UserConnections',
  props: {
    id: {
      type: Number,
      required: true,
    },
  },
  data () {
    return {
      connections: [],
    }
  },
  mounted () {
    this.get()
  },
  methods: {
    async get () {
      try {
        const response = await this.$http.get(`users/${this.id}/tokens`)
        const parser = new UAParser()
        this.connections = response.data.map((connection) => {
          parser.setUA(connection.userAgent)
          const userAgent = parser.getResult()
          connection.browser = `${userAgent.browser.name} ${userAgent.browser.major}`
          connection.os = `${userAgent.os.name} ${userAgent.os.version}`
          if (userAgent.device.model !== undefined) {
            connection.device = userAgent.device.model
          }
          return connection
        })
      } catch (error) {
        // @todo : handle error
        console.log(error)
      }
    },
  },
}
</script>
