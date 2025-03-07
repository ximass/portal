<template> <v-container style="padding-top: 50px;">
    <h1>Dashboard</h1>
    <div class="chart-wrapper">
      <apexchart type="line" height="350" :options="lineChartOptions" :series="lineChartSeries" />
    </div>
    <div class="chart-wrapper">
      <apexchart type="bar" height="350" :options="barChartOptions" :series="barChartSeries" />
    </div>

  </v-container>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

export default defineComponent({
  name: 'Dashboard', components: { apexchart: VueApexCharts, }, setup() {
    const lineChartSeries = [{ name: 'Visitas', data: [10, 41, 35, 51, 49, 62, 69, 79, 91, 148], },]; const lineChartOptions = { chart: { id: 'line-chart', }, xaxis: { categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out',], }, title: { text: 'Visitas Mensais', align: 'left', }, colors: ['#2196F3'], };
    const barChartSeries = [{ name: 'Vendas', data: [80, 90, 70, 100, 65, 80, 120, 110, 95, 105], },]; const barChartOptions = { chart: { id: 'bar-chart', }, xaxis: { categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out',], }, title: { text: 'Vendas Mensais', align: 'left', }, colors: ['#4CAF50'], }; return { lineChartSeries, lineChartOptions, barChartSeries, barChartOptions, };
  },
}); 
</script>

<style scoped>
.chart-wrapper {
  margin-bottom: 40px;
}
</style>