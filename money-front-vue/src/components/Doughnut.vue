<template>
  <div>
    <canvas ref="canvas" :width="width" :height="height" />
  </div>
</template>

<script>
import { Chart, ArcElement, DoughnutController, Legend, Tooltip, Filler } from 'chart.js'
Chart.register(ArcElement, DoughnutController, Legend, Tooltip, Filler)

Chart.defaults.font.family = '"Font Awesome 5 Free Solid", "Font Awesome 5 Free Regular", "Font Awesome 5 Brands Regular", "Helvetica Neue", "Helvetica", "Arial", sans-serif'

export default {
  name: 'DoughnutChart',
  props: {
    chartData: {
      type: Object,
      required: true,
    },
    options: {
      type: Object,
      required: true,
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
    }
  },
  watch: {
    chartData () {
      this.render()
    },
  },
  mounted () {
    this.render()
  },
  beforeUnmount () {
    if (this.chart) {
      this.chart.destroy() 
    }
  },
  methods: {
    render () {
      const ctx = this.$refs.canvas.getContext('2d')
      if (this.chart) {
        this.chart.destroy()
      }
      this.chart = new Chart(ctx, {
        type: 'doughnut',
        data: this.chartData,
        options: this.options,
      })
    },
  },
}
</script>
