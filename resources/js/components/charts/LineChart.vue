<template>
    <div class="h-full">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Title,
} from "chart.js";

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Title
);

const props = defineProps({
    labels: Array,
    data: Array,
});

const canvas = ref(null);
let chart = null;

const renderChart = () => {
    if (chart) chart.destroy();
    chart = new Chart(canvas.value, {
        type: "line",
        data: {
            labels: props.labels,
            datasets: [
                {
                    label: "Statistik Mingguan",
                    data: props.data,
                    fill: false,
                    borderColor: "#4f46e5",
                    tension: 0.2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false, // penting!
    // ...opsi lainnya
};

onMounted(renderChart);
watch(() => [props.labels, props.data], renderChart);
</script>

<style scoped>
div {
    height: 220px;
}
</style>
