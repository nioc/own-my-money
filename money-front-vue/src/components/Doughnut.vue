<script>
import { Doughnut, mixins } from 'vue-chartjs'
const { reactiveProp } = mixins

export default {
  extends: Doughnut,
  mixins: [reactiveProp],
  props: {
    chartData: {
      type: Object,
      required: true
    },
    chartLabels: {
      type: Array,
      required: false
    }
  },
  data () {
    return {
      options: {
        legend: {
          display: true
        },
        responsive: true,
        maintainAspectRatio: false,
        elements: {
        }
      }
    }
  },
  mounted () {
    this.renderChart(this.chartData, this.options)
  }
}
</script>
