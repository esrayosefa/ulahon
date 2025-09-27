<template>
    <div>
        <canvas ref="canvas"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import {
    Chart,
    BarElement,
    BarController,
    CategoryScale,
    LinearScale,
    Title,
} from "chart.js";

Chart.register(BarElement, BarController, CategoryScale, LinearScale, Title);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false, // penting!
    // ...opsi lainnya
};

const props = defineProps({
    labels: Array,
    data: Array,
});

const canvas = ref(null);
let chart = null;

const renderChart = () => {
    if (chart) chart.destroy();
    chart = new Chart(canvas.value, {
        type: "bar",
        data: {
            labels: props.labels,
            datasets: [
                {
                    label: "Statistik Bulanan",
                    data: props.data,
                    backgroundColor: "#f97316",
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

onMounted(renderChart);
watch(() => [props.labels, props.data], renderChart);
</script>

<style scoped>
div {
    height: 220px;
}
</style>
