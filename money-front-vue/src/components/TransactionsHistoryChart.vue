<template>
  <div v-if="isLoaded" class="box">
    <header class="title chart-header">
      <h2 class="title is-marginless">{{ title }}</h2>
      <a v-if="isClosable" class="delete is-large" :title="this.$t('actions.close')" @click="isLoaded = false" />
    </header>
    <div v-if="isIndependent" class="field is-grouped is-grouped-multiline is-block-mobile">
      <div class="control">
        <div class="select">
          <select v-model="search.duration">
            <option value="P1W">{{ $tc('objects.lastWeek', 1) }}</option>
            <option value="P1M">{{ $tc('objects.lastMonth', 1) }}</option>
            <option value="P3M">{{ $tc('objects.lastMonth', 3) }}</option>
            <option value="P6M">{{ $tc('objects.lastMonth', 6) }}</option>
            <option value="P1Y">{{ $tc('objects.lastYear', 1) }}</option>
          </select>
        </div>
      </div>
      <div class="control">
        <b-datepicker v-model="search.periodStart" placeholder="Start date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" />
      </div>
      <div class="control">
        <b-datepicker v-model="search.periodEnd" placeholder="End date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" />
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
        <div class="select">
          <select v-model="search.isRecurringOnly">
            <option :value="false">{{ $t('labels.all') }}</option>
            <option :value="true">{{ $t('labels.isRecurring') }}</option>
          </select>
        </div>
      </div>
      <div class="control">
        <button class="button" :class="{'is-loading': isLoading}" :disabled="isLoading" @click="applyFilter"><span class="icon"><i class="fa fa-refresh" /></span><span>{{ $t('actions.refresh') }}</span></button>
      </div>
    </div>
    <line-chart :chart-data="chartData" :label-callback="labelCallback" />
  </div>
</template>

<script>
import Config from './../services/Config'
import LineChart from '@/components/Line'
import Bus from '@/services/Bus'
export default {
  components: {
    LineChart,
  },
  props: {
    title: {
      type: String,
      required: true,
    },
    chartEndpoint: {
      type: String,
      required: true,
    },
    isIndependent: {
      type: Boolean,
      default: true,
      required: false,
    },
    date: {
      type: Object,
      default: null,
      required: false,
    },
    isClosable: {
      type: Boolean,
      default: false,
      required: false,
    },
  },
  data () {
    const today = new Date()
    today.setHours(0, 0, 0)
    today.setMilliseconds(0)
    return {
      isLoading: false,
      isLoaded: false,
      chartData: null,
      labelCallback: null,
      search: {
        duration: 'P3M',
        timeUnit: '',
        isRecurringOnly: false,
        currentDate: today,
        periodStart: this.$moment(today).subtract(this.$moment.duration('P3M')).toDate(),
        periodEnd: today,
      },
    }
  },
  watch: {
    'search.duration' () {
      if (this.search.duration) {
        Bus.$emit('transactions-date-filtered', {
          duration: this.search.duration,
          timeUnit: this.search.timeUnit,
          periodStart: this.$moment(this.search.currentDate).subtract(this.$moment.duration(this.search.duration)).toDate(),
          periodEnd: this.search.currentDate,
        })
      }
    },
  },
  mounted () {
    Bus.$on('transactions-date-filtered', (search) => {
      if (search.duration) {
        this.search.duration = search.duration
      }
      if ((this.search.periodStart.getTime() !== search.periodStart.getTime()) || (this.search.periodEnd.getTime() !== search.periodEnd.getTime()) || (this.search.timeUnit !== search.timeUnit) || (this.search.isRecurringOnly !== search.isRecurringOnly)) {
        this.search.periodStart = search.periodStart
        this.search.periodEnd = search.periodEnd
        if (search.timeUnit) {
          this.search.timeUnit = search.timeUnit
        }
        if (search.isRecurringOnly !== undefined) {
          this.search.isRecurringOnly = search.isRecurringOnly
        }
        this.requestData()
      }
    })
    if (this.date) {
      this.search.duration = this.date.duration
      this.search.periodStart = this.date.periodStart
      this.search.periodEnd = this.date.periodEnd
      this.search.timeUnit = this.date.timeUnit
    }
    this.requestData()
  },
  methods: {
    applyFilter () {
      Bus.$emit('transactions-date-filtered', this.search)
      this.requestData()
    },
    requestData () {
      this.isLoading = true
      const options = {
        params: {
          isRecurringOnly: this.search.isRecurringOnly,
          periodStart: this.$moment(this.search.periodStart).format('X'),
          periodEnd: this.$moment(this.search.periodEnd).format('X'),
        },
      }
      if (this.search.timeUnit !== '') {
        options.params.timeUnit = this.search.timeUnit
      }
      this.$http.get(Config.API_URL + this.chartEndpoint, options).then((response) => {
        const vm = this
        this.labelCallback = function (tooltipItem, data) {
          return data.datasets[tooltipItem.datasetIndex].label + ': ' + vm.$n(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index], 'currency')
        }
        this.chartData = {
          datasets: [
            {
              data: response.data.values.map((point) => point.balance),
              label: this.$t('labels.balances'),
              type: 'line',
              backgroundColor: 'rgba(0, 0, 0, 0)',
              borderColor: 'rgb(122, 122, 122)',
              pointBackgroundColor: 'rgba(122, 122, 122, 0.8)',
              radius: 2,
              lineTension: 0.1,
              yAxisID: 'y-axis-2',
            },
            {
              data: response.data.values.map((point) => point.debit),
              label: this.$t('labels.debits'),
              type: 'bar',
              barThickness: 'flex',
              backgroundColor: 'rgba(255, 99, 132, 0.6)',
              borderColor: 'rgb(255, 99, 132)',
              yAxisID: 'y-axis-1',
            },
            {
              data: response.data.values.map((point) => point.credit),
              label: this.$t('labels.credits'),
              type: 'bar',
              barThickness: 'flex',
              backgroundColor: 'rgba(66, 185, 131, 0.6)',
              borderColor: 'rgb(66, 185, 131)',
              yAxisID: 'y-axis-1',
            },
            {
              data: response.data.values.map((point) => point.debitRecurring),
              label: this.$t('labels.debits') + this.$t('labels.recurring'),
              type: 'bar',
              barThickness: 'flex',
              backgroundColor: 'rgba(255, 99, 132, 0.4)',
              borderColor: 'rgb(255, 99, 132)',
              yAxisID: 'y-axis-1',
            },
            {
              data: response.data.values.map((point) => point.creditRecurring),
              label: this.$t('labels.credits') + this.$t('labels.recurring'),
              type: 'bar',
              barThickness: 'flex',
              backgroundColor: 'rgba(66, 185, 131, 0.4)',
              borderColor: 'rgb(66, 185, 131)',
              yAxisID: 'y-axis-1',
            },
          ],
          labels: response.data.values.map((point) => this.$moment(point.date)),
        }
        this.search.periodStart = this.$moment(response.data.periodStart).toDate()
        this.search.periodEnd = this.$moment(response.data.periodEnd).toDate()
        this.isLoading = false
        this.isLoaded = true
      }, (response) => {
        this.isLoading = false
        if (response.body.message) {
          console.log(response.body.message)
          return
        }
        console.log(response.status + ' - ' + response.statusText)
      })
    },
  },
}
</script>
