<template>
    <div class="h-full">
        <canvas ref="canvas" />
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

// Pendaftaran komponen Chart.js tetap diperlukan
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

// ✅ Inisialisasi ref yang akan terikat ke elemen <canvas>
const canvas = ref(null);
let chart = null;

const renderChart = () => {
    // Safety check: Pastikan elemen canvas sudah tersedia di DOM
    if (!canvas.value) {
        console.warn("Canvas element not available for chart rendering.");
        return;
    }

    // Hapus chart lama jika ada sebelum membuat yang baru
    if (chart) chart.destroy();

    // ✅ Inisialisasi chart baru menggunakan konteks canvas
    chart = new Chart(canvas.value, {
        type: "line",
        data: {
            labels: props.labels,
            datasets: [
                {
                    // Asumsi: Anda menggunakan chart ini untuk data mingguan
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

// ✅ Panggil renderChart saat komponen terpasang ke DOM
onMounted(renderChart);

// ✅ Panggil renderChart setiap kali data atau label berubah
watch(() => [props.labels, props.data], renderChart, { deep: true });
</script>

<style scoped>
div {
    /* Atur height agar Chart.js dapat menghitung dimensinya */
    height: 220px;
}
</style>
