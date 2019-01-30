<template>
  <div class="box" v-if="isLoaded">
    <p class="title">{{ title }}</p>
    <div v-if="isIndependent" class="field is-grouped is-grouped-multiline is-block-mobile">
      <div class="control">
        <div class="select">
          <select v-model="quickDate">
            <option value="P1W">{{ $tc('objects.lastWeek', 1) }}</option>
            <option value="P1M">{{ $tc('objects.lastMonth', 1) }}</option>
            <option value="P3M">{{ $tc('objects.lastMonth', 3) }}</option>
            <option value="P6M">{{ $tc('objects.lastMonth', 6) }}</option>
            <option value="P1Y">{{ $tc('objects.lastYear', 1) }}</option>
          </select>
        </div>
      </div>
      <div class="control">
        <b-datepicker placeholder="Start date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" v-model="search.periodStart"></b-datepicker>
      </div>
      <div class="control">
        <b-datepicker placeholder="End date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" v-model="search.periodEnd"></b-datepicker>
      </div>
      <div class="control">
        <div class="select">
          <select v-model="search.timeUnit">
            <option value="">{{ $t('labels.automatic') }}</option>
            <option value="D">{{ $t('labels.daily') }}</option>
            <option value="W">{{ $t('labels.weekly') }}</option>
            <option value="M">{{ $t('labels.monthly') }}</option>
          </select>
        </div>
      </div>
      <div class="control">
        <button class="button" :class="{ 'is-loading': isLoading }" @click="applyFilter" :disabled="isLoading"><span class="icon"><i class="fa fa-refresh"></i></span><span>{{ $t('actions.refresh') }}</span></button>
      </div>
    </div>
    <line-chart :chartData="chartData" :labelCallback="labelCallback"></line-chart>
  </div>
</template>

<script>
import Config from './../services/Config'
import LineChart from '@/components/Line'
import Bus from '@/services/Bus'
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
    },
    isIndependent: {
      type: Boolean,
      default: true,
      required: false
    },
    date: {
      required: false
    }
  },
  data () {
    const today = new Date()
    today.setHours(0, 0, 0)
    return {
      isLoading: false,
      isLoaded: false,
      chartData: null,
      labelCallback: null,
      quickDate: 'P3M',
      search: {
        timeUnit: '',
        currentDate: today,
        periodStart: this.$moment(today).subtract(this.$moment.duration(this.quickDate)).toDate(),
        periodEnd: today
      }
    }
  },
  methods: {
    applyFilter () {
      Bus.$emit('transactions-date-filtered', this.search)
      this.requestData()
    },
    requestData () {
      this.isLoading = true
      let options = {
        params: {
          periodStart: this.$moment(this.search.periodStart).format('X'),
          periodEnd: this.$moment(this.search.periodEnd).format('X')
        }
      }
      if (this.search.timeUnit !== '') {
        options.params.timeUnit = this.search.timeUnit
      }
      this.$http.get(Config.API_URL + this.chartEndpoint, options).then(response => {
        let vm = this
        this.labelCallback = function (tooltipItem, data) {
          return data.datasets[tooltipItem.datasetIndex].label + ': ' + vm.$n(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index], 'currency')
        }
        this.chartData = {
          datasets: [
            {
              data: response.data.values.map(point => point.debit),
              label: this.$t('labels.debits'),
              backgroundColor: 'rgba(255, 99, 132, 0.5)',
              borderColor: 'rgb(255, 99, 132)',
              pointBackgroundColor: 'rgba(255, 99, 132, 0.8)'
            },
            {
              data: response.data.values.map(point => point.credit),
              label: this.$t('labels.credits'),
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
  watch: {
    quickDate () {
      if (this.quickDate) {
        Bus.$emit('transactions-date-filtered', {
          periodStart: this.$moment(this.search.currentDate).subtract(this.$moment.duration(this.quickDate)).toDate(),
          periodEnd: this.search.currentDate
        })
      }
    }
  },
  mounted () {
    Bus.$on('transactions-date-filtered', (search) => {
      if ((this.search.periodStart.getTime() !== search.periodStart.getTime()) || (this.search.periodEnd.getTime() !== search.periodEnd.getTime()) || (this.search.timeUnit !== search.timeUnit)) {
        this.search.periodStart = search.periodStart
        this.search.periodEnd = search.periodEnd
        if (search.timeUnit) {
          this.search.timeUnit = search.timeUnit
        }
        this.requestData()
      }
    })
    if (this.date) {
      this.search.periodStart = this.date.periodStart
      this.search.periodEnd = this.date.periodEnd
      this.search.timeUnit = this.date.timeUnit
    }
    this.requestData()
  }
}
</script>
