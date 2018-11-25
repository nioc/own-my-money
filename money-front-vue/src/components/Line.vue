<script>
import { Line, mixins } from 'vue-chartjs'
const { reactiveProp } = mixins

export default {
  extends: Line,
  mixins: [reactiveProp],
  props: {
    chartData: {
      type: Object,
      required: true
    },
    chartOptions: {
      type: Object,
      required: false,
      default () {
        return {
          legend: {
            display: false
          },
          maintainAspectRatio: false,
          spanGaps: false,
          elements: {
            line: {
              tension: 0.000001
            }
          },
          plugins: {
            filler: {
              propagate: false
            }
          },
          scales: {
            xAxes: [{
              type: 'time',
              ticks: {
                autoSkip: false,
                maxRotation: 0
              }
            }]
          }
        }
      }
    }
  },
  mounted () {
    this.renderChart(this.chartData, this.chartOptions)
  }
}
</script>
