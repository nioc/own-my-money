<template>
  <div class="box" v-if="isLoaded">
    <p class="title">{{ title }}</p>
      <div class="field is-grouped is-grouped-multiline is-block-mobile">
      <div class="control">
        <b-datepicker placeholder="Start date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" v-model="search.periodStart"></b-datepicker>
      </div>
      <div class="control">
        <b-datepicker placeholder="End date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" v-model="search.periodEnd"></b-datepicker>
      </div>
      <div class="control">
        <button class="button" :class="{ 'is-loading': isLoading }" @click="requestData" :disabled="isLoading"><span class="icon"><i class="fa fa-refresh"></i></span><span>Refresh</span></button>
      </div>
    </div>
    <line-chart :chartData="chartData"></line-chart>
  </div>
</template>

<script>
import Config from './../services/Config'
import LineChart from '@/components/Line'
export default {
  components: {
    LineChart
  },
  props: {
    title: {
      type: String,
      required: true
    },
    chartEndpoint: {
      type: String,
      required: true
    }
  },
  data () {
    const today = new Date()
    return {
      isLoading: false,
      isLoaded: false,
      chartData: null,
      search: {
        currentDate: today,
        periodStart: new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30),
        periodEnd: today
      }
    }
  },
  methods: {
    requestData () {
      this.isLoading = true
      let options = {
        params: {
          periodStart: this.$moment(this.search.periodStart).format('X'),
          periodEnd: this.$moment(this.search.periodEnd).format('X')
        }
      }
      this.$http.get(Config.API_URL + this.chartEndpoint, options).then(response => {
        this.chartData = {
          datasets: [
            {
              data: response.data.values.map(point => point.debit),
              label: 'Debits',
              backgroundColor: 'rgba(255, 99, 132, 0.5)',
              borderColor: 'rgb(255, 99, 132)',
              pointBackgroundColor: 'rgba(255, 99, 132, 0.8)'
            },
            {
              data: response.data.values.map(point => point.credit),
              label: 'Credits',
              backgroundColor: 'rgba(66, 185, 131, 0.5)',
              borderColor: 'rgb(66, 185, 131)',
              pointBackgroundColor: 'rgba(66, 185, 131, 0.8)'
            }
          ],
          labels: response.data.values.map(point => this.$moment(point.date))
        }
        this.search.periodStart = this.$moment(response.data.periodStart).toDate()
        this.search.periodEnd = this.$moment(response.data.periodEnd).toDate()
        this.isLoading = false
        this.isLoaded = true
      }, response => {
        if (response.body.message) {
          console.log(response.body.message)
          return
        }
        console.log(response.status + ' - ' + response.statusText)
      })
    }
  },
  mounted () {
    this.requestData()
  }
}
</script>
