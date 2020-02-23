<script>
import { Bar, mixins } from 'vue-chartjs'
const { reactiveProp } = mixins

export default {
  extends: Bar,
  mixins: [reactiveProp],
  props: {
    chartData: {
      type: Object,
      required: true,
    },
    labelCallback: {
      type: Function,
      required: false,
      default: null,
    },
    chartOptions: {
      type: Object,
      required: false,
      default () {
        return {
          legend: {
            display: false,
          },
          maintainAspectRatio: false,
          spanGaps: false,
          elements: {
            line: {
              tension: 0.000001,
            },
          },
          plugins: {
            filler: {
              propagate: false,
            },
          },
          tooltips: {
            callbacks: {
            },
          },
          scales: {
            xAxes: [{
              type: 'time',
              stacked: true,
              ticks: {
                autoSkip: false,
                maxRotation: 0,
              },
              gridLines: {
                offsetGridLines: true,
              },
            }],
            yAxes: [{
              id: 'y-axis-1',
              type: 'linear',
              ticks: {
              },
            }],
          },
        }
      },
    },
  },
  watch: {
    chartData () {
      this.renderChart(this.chartData, this.chartOptions)
    },
  },
  mounted () {
    if (this.labelCallback !== null && typeof this.labelCallback === 'function') {
      this.chartOptions.tooltips.callbacks = { label: this.labelCallback }
    }
    this.chartData.datasets.forEach((dataset) => {
      if (dataset.yAxisID === 'y-axis-2' && this.chartOptions.scales.yAxes.length < 2) {
        this.chartOptions.scales.yAxes.push({
          id: 'y-axis-2',
          type: 'linear',
          position: 'right',
          ticks: {
            beginAtZero: true,
          },
          gridLines: {
            drawOnChartArea: false,
          },
        })
      }
    })
    this.renderChart(this.chartData, this.chartOptions)
  },
}
</script>
