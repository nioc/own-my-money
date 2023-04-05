<template>
  <div v-if="isLoaded" class="box" style="height: 100%;">
    <header class="title chart-header">
      <h2 class="title is-marginless">{{ title }}</h2>
      <a v-if="isClosable" class="delete is-large" :title="$t('actions.close')" @click="isLoaded = false" />
    </header>
    <div v-if="isIndependent" class="field is-grouped is-grouped-multiline is-block-mobile">
      <div class="control">
        <o-datepicker v-model="search.periodStart" placeholder="Start date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" lists-class="field has-addons" />
      </div>
      <div class="control">
        <o-datepicker v-model="search.periodEnd" placeholder="End date" icon="calendar" editable :max-date="search.currentDate" required :disabled="isLoading" lists-class="field has-addons" />
      </div>
      <div class="control">
        <button class="button" :class="{'is-loading': isLoading}" :disabled="isLoading" @click="applyFilter"><span class="icon"><i class="fas fa-sync-alt" /></span><span>{{ $t('actions.refresh') }}</span></button>
      </div>
    </div>
    <doughnut v-if="chartData.labels.length > 0" :chart-data="chartData" :options="options" />
    <div v-else class="content has-text-grey has-text-centered pt-6">
      <p>{{ $t('labels.nothingToDisplay') }}</p>
    </div>
  </div>
</template>

<script>
import Doughnut from '@/components/Doughnut.vue'
import { useStore } from '@/store'
import { mapState } from 'pinia'

export default {
  components: {
    Doughnut,
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
  computed: {
    ...mapState(useStore, ['categoriesAndSubcategoriesLookup']),
  },
  mounted () {
    this.$bus.on('transactions-date-filtered', this.handleTransactionsDateFilteredDistribution)
    if (this.date) {
      this.search.periodStart = this.date.periodStart
      this.search.periodEnd = this.date.periodEnd
    }
    this.requestData()
  },
  beforeUnmount () {
    this.$bus.off('transactions-date-filtered', this.handleTransactionsDateFilteredDistribution)
  },
  methods: {
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
      try {
        const response = await this.$http.get(this.chartEndpoint, options)
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
        const vm = this
        if (response.data.key === 'categories') {
          onClick = function (evt, elements, chart) {
            if (elements.length > 0) {
              const key = values[elements[0].index].key
              if (key) {
                // send event for parent update (may be displaying subcategories distribution)
                const label = chart.config.data.labels[elements[0].index].toString().replace(/[^ -~]+ /g, '')
                vm.$bus.emit('category-selected', { key, label })
              }
            }
          }
        } else if (response.data.key === 'subcategories') {
          onClick = function (evt, elements, chart) {
            if (elements.length > 0) {
              const key = values[elements[0].index].key
              if (key) {
                // send event for parent update (may be displaying subcategories transaction history)
                const label = chart.config.data.labels[elements[0].index].toString()
                vm.$bus.emit('subcategory-selected', { key, label })
              }
            }
          }
        }
        const labelCallback = function (context) {
          const sum = context.dataset.data.reduce((a, b) => a + b, 0)
          const value = context.raw
          const label = context.label.replace(/[^ -~]+ /g, '')
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
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
            },
            tooltip: {
              callbacks: {
                title: () => '',
                label: labelCallback,
              },
            },
          },
          onClick,
        }
        this.search.periodStart = this.$dayjs(response.data.periodStart).toDate()
        this.search.periodEnd = this.$dayjs(response.data.periodEnd).toDate()
        this.isLoaded = true
      } catch (error) {
        console.log(error)
      }
      this.isLoading = false
    },
    handleTransactionsDateFilteredDistribution (search) {
      if ((this.search.periodStart.getTime() !== search.periodStart.getTime()) || (this.search.periodEnd.getTime() !== search.periodEnd.getTime()) || (this.search.isRecurringOnly !== search.isRecurringOnly)) {
        this.search.periodStart = search.periodStart
        this.search.periodEnd = search.periodEnd
        if (search.isRecurringOnly !== undefined) {
          this.search.isRecurringOnly = search.isRecurringOnly
        }
        this.requestData()
      }
    },
  },
}
</script>
