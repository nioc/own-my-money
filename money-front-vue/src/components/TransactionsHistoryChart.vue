<template>
  <div v-if="isLoaded" class="box">
    <header class="title chart-header">
      <h2 class="title is-marginless">{{ title }}</h2>
      <a v-if="isClosable" class="delete is-large" :title="$t('actions.close')" @click="isLoaded = false" />
    </header>
    <div v-if="isIndependent" class="field is-grouped is-grouped-multiline is-block-mobile">
      <div class="control">
        <div class="select">
          <select v-model="search.duration" @change="applyDuration">
            <option value="P1W">{{ $tc('objects.lastWeek', 1) }}</option>
            <option value="P1M">{{ $tc('objects.lastMonth', 1) }}</option>
            <option value="P3M">{{ $tc('objects.lastMonth', 3) }}</option>
            <option value="P6M">{{ $tc('objects.lastMonth', 6) }}</option>
            <option value="P1Y">{{ $tc('objects.lastYear', 1) }}</option>
            <option value="P2Y">{{ $tc('objects.lastYear', 2) }}</option>
          </select>
        </div>
      </div>
      <div class="control">
        <o-datepicker v-model="search.periodStart" placeholder="Start date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" lists-class="field has-addons" />
      </div>
      <div class="control">
        <o-datepicker v-model="search.periodEnd" placeholder="End date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" lists-class="field has-addons" />
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
        <button class="button" :class="{'is-loading': isLoading}" :disabled="isLoading" @click="applyFilter"><span class="icon"><i class="fas fa-sync-alt" /></span><span>{{ $t('actions.refresh') }}</span></button>
      </div>
    </div>
    <line-chart :chart-data="chartData" :label-callback="labelCallback" />
  </div>
</template>

<script>
import LineChart from '@/components/Line.vue'

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
        periodStart: this.$dayjs(today).subtract(this.$dayjs.duration('P3M')).toDate(),
        periodEnd: today,
      },
    }
  },
  mounted () {
    this.$bus.on('transactions-date-filtered', this.handleTransactionsDateFilteredHistory)
    if (this.date) {
      this.search.duration = this.date.duration
      this.search.periodStart = this.date.periodStart
      this.search.periodEnd = this.date.periodEnd
      this.search.timeUnit = this.date.timeUnit
    }
    this.requestData()
  },
  beforeUnmount () {
    this.$bus.off('transactions-date-filtered', this.handleTransactionsDateFilteredHistory)
  },
  methods: {
    applyDuration (a) {
      if (a.target.value) {
        this.$bus.emit('transactions-date-filtered', {
          duration: this.search.duration,
          timeUnit: this.search.timeUnit,
          periodStart: this.$dayjs(this.search.currentDate).subtract(this.$dayjs.duration(this.search.duration)).toDate(),
          periodEnd: this.search.currentDate,
        })
      }
    },
    applyFilter () {
      this.$bus.emit('transactions-date-filtered', this.search)
      this.requestData()
    },
    async requestData () {
      this.isLoading = true
      const options = {
        params: {
          isRecurringOnly: this.search.isRecurringOnly,
          periodStart: this.$dayjs(this.search.periodStart).format('X'),
          periodEnd: this.$dayjs(this.search.periodEnd).format('X'),
        },
      }
      if (this.search.timeUnit !== '') {
        options.params.timeUnit = this.search.timeUnit
      }
      try {
        const response = await this.$http.get(this.chartEndpoint, options)
        const vm = this
        this.labelCallback = function (context) {
          return context.dataset.label + ': ' + vm.$n(context.dataset.data[context.dataIndex], 'currency')
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
              yAxisID: 'y1',
            },
            {
              data: response.data.values.map((point) => point.debit),
              label: this.$t('labels.debits'),
              type: 'bar',
              barThickness: 'flex',
              backgroundColor: 'rgba(255, 99, 132, 0.6)',
              borderColor: 'rgb(255, 99, 132)',
              yAxisID: 'y',
            },
            {
              data: response.data.values.map((point) => point.credit),
              label: this.$t('labels.credits'),
              type: 'bar',
              barThickness: 'flex',
              backgroundColor: 'rgba(66, 185, 131, 0.6)',
              borderColor: 'rgb(66, 185, 131)',
              yAxisID: 'y',
            },
            {
              data: response.data.values.map((point) => point.debitRecurring),
              label: this.$t('labels.debits') + this.$t('labels.recurring'),
              type: 'bar',
              barThickness: 'flex',
              backgroundColor: 'rgba(255, 99, 132, 0.4)',
              borderColor: 'rgb(255, 99, 132)',
              yAxisID: 'y',
            },
            {
              data: response.data.values.map((point) => point.creditRecurring),
              label: this.$t('labels.credits') + this.$t('labels.recurring'),
              type: 'bar',
              barThickness: 'flex',
              backgroundColor: 'rgba(66, 185, 131, 0.4)',
              borderColor: 'rgb(66, 185, 131)',
              yAxisID: 'y',
            },
          ],
          labels: response.data.values.map((point) => {
            // workaround for parsing week number (https://github.com/iamkun/dayjs/issues/999)
            const weekDate = point.date.split('W')
            let date
            if (weekDate.length > 1) {
              date = this.$dayjs().year(weekDate[0]).isoWeek(weekDate[1]).startOf('week')
            } else {
              date = this.$dayjs(point.date)
            }
            return date.toISOString()
          }),
        }
        this.search.periodStart = this.$dayjs(response.data.periodStart).toDate()
        this.search.periodEnd = this.$dayjs(response.data.periodEnd).toDate()
        this.isLoaded = true
      } catch (error) {
        console.log(error)
      }
      this.isLoading = false
    },
    handleTransactionsDateFilteredHistory (search) {
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
    },
  },
}
</script>
