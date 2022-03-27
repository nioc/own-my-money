<template>
  <div>
    <canvas ref="canvas" :width="width" :height="height" />
  </div>
</template>

<script>
import { Chart, BarElement, LineElement, PointElement, BarController, LineController,  LinearScale, TimeScale, Tooltip } from 'chart.js'
import 'chartjs-adapter-dayjs-3'

Chart.register(BarElement, LineElement, PointElement, BarController, LineController,  LinearScale, TimeScale, Tooltip)

export default {
  name: 'LineChart',
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
    width: {
      type: Number,
      default: 400,
    },
    height: {
      type: Number,
      default: 400,
    },
  },
  data () {
    return {
      chart: null,
      isRendering: false,
      chartOptions: {
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
            },
          },
        },
        maintainAspectRatio: false,
        spanGaps: false,
        elements: {
          line: {
            tension: 0.000001,
          },
        },
        scales: {
          x: {
            type: 'time',
            stacked: true,
            ticks: {
              autoSkip: false,
              maxRotation: 0,
            },
            grid: {
              offset: true,
            },
          },
          y: {
            type: 'linear',
            ticks: {
            },
          },
        },
      },
    }
  },
  watch: {
    chartData () {
      this.render()
    },
  },
  mounted () {
    if (this.labelCallback !== null && typeof this.labelCallback === 'function') {
      this.chartOptions.plugins.tooltip.callbacks = { label: this.labelCallback }
    }
    this.chartData.datasets.forEach((dataset) => {
      if (dataset.yAxisID === 'y1' && !Object.prototype.hasOwnProperty.call(this.chartOptions.scales, 'y1')) {
        this.chartOptions.scales.y1 = {
          type: 'linear',
          position: 'right',
          beginAtZero: true,
          grid: {
            drawOnChartArea: false,
          },
        }
      }
    })
    this.render()
  },
  beforeUnmount () {
    if (this.chart) {
      this.chart.destroy()
    }
  },
  methods: {
    async render () {
      if (this.isRendering) {
        return
      }
      this.isRendering = true
      const ctx = this.$refs.canvas.getContext('2d')
      if (this.chart) {
        await this.chart.destroy()
      }
      this.$nextTick(function () {
        this.chart = new Chart(ctx, {
          type: 'line',
          data: this.chartData,
          options: this.chartOptions,
        })
        this.isRendering = false
      })
    },
  },
}
</script>
