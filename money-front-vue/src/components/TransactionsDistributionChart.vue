<template>
  <div v-if="isLoaded" class="box" style="height: 100%;">
    <header class="title chart-header">
      <h2 class="title is-marginless">{{ title }}</h2>
      <a v-if="isClosable" class="delete is-large" :title="this.$t('actions.close')" @click="isLoaded = false" />
    </header>
    <div v-if="isIndependent" class="field is-grouped is-grouped-multiline is-block-mobile">
      <div class="control">
        <b-datepicker v-model="search.periodStart" placeholder="Start date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" />
      </div>
      <div class="control">
        <b-datepicker v-model="search.periodEnd" placeholder="End date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" />
      </div>
      <div class="control">
        <button class="button" :class="{'is-loading': isLoading}" :disabled="isLoading" @click="applyFilter"><span class="icon"><i class="fa fa-refresh" /></span><span>{{ $t('actions.refresh') }}</span></button>
      </div>
    </div>
    <doughnut :chart-data="chartData" :options="options" />
  </div>
</template>

<script>
import Config from './../services/Config'
import Bus from '@/services/Bus'
import Doughnut from '@/components/Doughnut'
import CategoriesFactory from './../services/Categories'
export default {
  components: {
    Doughnut,
  },
  mixins: [CategoriesFactory],
  props: {
    title: {
      type: String,
      required: true,
    },
    chartEndpoint: {
      type: String,
      required: true,
    },
    date: {
      type: Object,
      default: null,
      required: false,
    },
    isIndependent: {
      type: Boolean,
      default: true,
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
      options: null,
      search: {
        isRecurringOnly: false,
        currentDate: today,
        periodStart: new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30),
        periodEnd: today,
      },
    }
  },
  mounted () {
    Bus.$on('transactions-date-filtered', (search) => {
      if ((this.search.periodStart.getTime() !== search.periodStart.getTime()) || (this.search.periodEnd.getTime() !== search.periodEnd.getTime()) || (this.search.isRecurringOnly !== search.isRecurringOnly)) {
        this.search.periodStart = search.periodStart
        this.search.periodEnd = search.periodEnd
        if (search.isRecurringOnly !== undefined) {
          this.search.isRecurringOnly = search.isRecurringOnly
        }
        this.requestData()
      }
    })
    this.getCategories(true)
    if (this.date) {
      this.search.periodStart = this.date.periodStart
      this.search.periodEnd = this.date.periodEnd
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
      this.$http.get(Config.API_URL + this.chartEndpoint, options).then((response) => {
        const values = response.data.values
        let colors = ['#42b983', '#292f36', '#4ecdc4', '#0b3954', '#ff6663', '#7d7c84', '#7180ac', '#2b4570', '#c84630', '#81a684', '#466060', '#c9cba3', '#e26d5c', '#2a4747', '#157a6e', '#ee6c4d']
        const count = values.length
        let lightness
        if (response.data.type === 'debit') {
          colors = []
          for (let i = 0; i < count; i++) {
            lightness = 61 + (i) / count * 39
            colors.push('hsla(348, 100%, ' + lightness + '%, 1)')
          }
        } else if (response.data.type === 'credit') {
          colors = []
          for (let i = 0; i < count; i++) {
            lightness = 49 + (i) / count * 41
            colors.push('hsla(153, 47%, ' + lightness + '%, 1)')
          }
        }
        let onClick = null
        if (response.data.key === 'categories') {
          onClick = function (evt) {
            const index = this.chart.getElementsAtEvent(evt)[0]
            if (index) {
              const key = values[index._index].key
              if (key) {
                // send event for parent update (may be displaying subcategories distribution)
                Bus.$emit('category-selected', { key: key, label: this.chart.data.labels[index._index].replace(/[^ -~]+ /g, '') })
              }
            }
          }
        } else if (response.data.key === 'subcategories') {
          onClick = function (evt) {
            const index = this.chart.getElementsAtEvent(evt)[0]
            if (index) {
              const key = values[index._index].key
              if (key) {
                // send event for parent update (may be displaying subcategories transaction history)
                Bus.$emit('subcategory-selected', { key: key, label: this.chart.data.labels[index._index] })
              }
            }
          }
        }
        const vm = this
        const labelCallback = function (tooltipItem, data) {
          const sum = data.datasets[tooltipItem.datasetIndex].data.reduce((a, b) => a + b, 0)
          const value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]
          const label = data.labels[tooltipItem.index].replace(/[^ -~]+ /g, '')
          return label + ': ' + vm.$n(value, 'currency') + ' (' + Math.round(100 * value / sum) + '%)'
        }
        this.chartData = {
          datasets: [
            {
              backgroundColor: colors,
              data: values.map((point) => point.amount),
            },
          ],
        }
        let labels
        if (response.data.key === 'categories' || response.data.key === 'subcategories') {
          labels = values.map((point) => {
            let unicodeContent = ''
            if (this.categoriesAndSubcategoriesLookup[point.key]) {
              if (this.categoriesAndSubcategoriesLookup[point.key].icon) {
                // to get unicode content from CSS class, create a styled element then get content and remove it
                const icon = this.categoriesAndSubcategoriesLookup[point.key].icon
                const tempElement = document.createElement('i')
                tempElement.className = icon
                document.body.appendChild(tempElement)
                unicodeContent = window.getComputedStyle(tempElement, ':before').content.replace(/'|"/g, '') + ' '
                tempElement.parentNode.removeChild(tempElement)
              }
              return unicodeContent + this.categoriesAndSubcategoriesLookup[point.key].label
            }
            if (point.key === null) {
              return this.$t('labels.uncategorizedTransaction')
            }
            return point.key
          })
        } else {
          labels = values.map((point) => point.key)
        }
        this.chartData.labels = labels
        this.options = {
          legend: {
            display: true,
          },
          responsive: true,
          maintainAspectRatio: false,
          tooltips: {
            callbacks: {
              label: labelCallback,
            },
          },
          onClick: onClick,
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
