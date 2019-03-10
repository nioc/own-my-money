<script>
import { Doughnut, mixins } from 'vue-chartjs'
import Chart from 'chart.js'
Chart.defaults.global.defaultFontFamily = '"FontAwesome", "Helvetica Neue", "Helvetica", "Arial", sans-serif'
const { reactiveProp } = mixins

export default {
  extends: Doughnut,
  mixins: [reactiveProp],
  props: {
    chartData: {
      type: Object,
      required: true
    },
    options: {
      type: Object,
      required: true
    }
  },
  mounted () {
    this.renderChart(this.chartData, this.options)
  },
  watch: {
    chartData () {
      this.renderChart(this.chartData, this.options)
    }
  }
}
</script>
