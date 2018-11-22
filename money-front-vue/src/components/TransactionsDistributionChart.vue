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
    <doughnut :chartData="chartData"></doughnut>
  </div>
</template>

<script>
import Config from './../services/Config'
import Doughnut from '@/components/Doughnut'
import CategoriesFactory from './../services/Categories'
export default {
  components: {
    Doughnut
  },
  mixins: [CategoriesFactory],
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
        let values = response.data.values
        let colors = ['#42b983', '#292f36', '#4ecdc4', '#0b3954', '#ff6663', '#7d7c84', '#7180ac', '#2b4570', '#c84630', '#81a684', '#466060', '#c9cba3', '#e26d5c', '#2a4747', '#157a6e', '#ee6c4d']
        let count = values.length
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
        this.chartData = {
          datasets: [
            {
              backgroundColor: colors,
              data: values.map(point => point.amount)
            }
          ]
        }
        let labels
        if (response.data.key === 'categories') {
          labels = values.map(point => this.categoriesAndSubcategoriesLookup[point.key] ? this.categoriesAndSubcategoriesLookup[point.key].label : point.key)
        } else {
          labels = values.map(point => point.key)
        }
        this.chartData.labels = labels
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
    this.getCategories(true)
  }
}
</script>