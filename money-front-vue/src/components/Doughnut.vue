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
    labelCallback: {
      type: Function,
      required: false
    },
    onClick: {
      type: Function,
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
        tooltips: {
          callbacks: {
            label: this.labelCallback
          }
        },
        onClick: this.onClick
      }
    }
  },
  mounted () {
    this.renderChart(this.chartData, this.options)
  }
}
</script>
