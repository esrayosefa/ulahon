<template>
    <div class="flex min-h-screen">
        <div
            class="flex-1 flex flex-col bg-gradient-to-b from-blue-100 to-orange-100"
        >
            <div class="p-6 space-y-6">
                <!-- Petugas Piket -->
                <section class="bg-white rounded-xl shadow p-4">
                    <h2 class="font-bold text-lg text-gray-700 mb-4">
                        Petugas Piket Hari Ini
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <PetugasCard
                            v-for="(petugas, index) in sesiAktif"
                            :key="index"
                            :nama="petugas.nama"
                            :jam="petugas.jam"
                            :foto="petugas.foto"
                        />
                    </div>

                    <div class="mt-4 flex justify-center space-x-2">
                        <button
                            @click="sesi = 1"
                            :class="sesi === 1 ? activeBtn : inactiveBtn"
                        >
                            Sesi 1
                        </button>
                        <button
                            @click="sesi = 2"
                            :class="sesi === 2 ? activeBtn : inactiveBtn"
                        >
                            Sesi 2
                        </button>
                    </div>
                </section>

                <!-- Statistik & Antrian -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <AntrianList :antrian="antrian" />
                    <StatistikChart
                        title="Statistik Harian"
                        :labels="labelsHarian"
                        :data="dataHarian"
                    />
                    <StatistikChart
                        title="Statistik Bulanan"
                        :labels="labelsBulanan"
                        :data="dataBulanan"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import PetugasCard from "@/components/PetugasCard.vue";
import AntrianList from "@/components/AntrianList.vue";
import StatistikChart from "@/components/StatistikChart.vue";

const sesi = ref(1);
const petugas = ref([]);
const antrian = ref([]);
const labelsHarian = ref([]);
const dataHarian = ref([]);
const labelsBulanan = ref([]);
const dataBulanan = ref([]);

const sesiAktif = computed(() =>
    petugas.value.filter((p) => p.sesi === sesi.value)
);

onMounted(async () => {
    try {
        const [petugasRes, antrianRes, harianRes, bulananRes] =
            await Promise.all([
                axios.get("/api/petugas"),
                axios.get("/api/antrian"),
                axios.get("/api/statistik/mingguan"),
                axios.get("/api/statistik/bulanan"),
            ]);

        petugas.value = petugasRes.data;
        antrian.value = antrianRes.data;
        labelsHarian.value = harianRes.data.labels;
        dataHarian.value = harianRes.data.data;
        labelsBulanan.value = bulananRes.data.labels;
        dataBulanan.value = bulananRes.data.data;
    } catch (err) {
        console.error("Gagal memuat data dashboard:", err);
    }
});

const activeBtn = "bg-blue-700 text-white px-4 py-1 rounded shadow";
const inactiveBtn = "bg-gray-200 text-gray-600 px-4 py-1 rounded";
</script>
