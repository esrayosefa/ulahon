<template>
    <div class="flex h-screen overflow-hidden">
        <!-- Konten Utama -->
        <div
            class="flex-1 flex flex-col overflow-y-auto bg-gradient-to-b from-blue-100 to-orange-100"
        >
            <div class="p-6 space-y-6 flex-1">
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
                <div class="grid grid-cols-12 gap-6">
                    <!-- Antrian: 1 kolom penuh di mobile, 4 kolom di lg -->
                    <AntrianList
                        class="col-span-12 lg:col-span-4"
                        :antrian="antrian"
                    />

                    <!-- Statistik Harian -->
                    <StatistikChart
                        class="col-span-12 lg:col-span-4"
                        title="Statistik Harian"
                        :labels="labelsHarian"
                        :data="dataHarian"
                        type="line"
                    />

                    <!-- Statistik Bulanan -->
                    <StatistikChart
                        class="col-span-12 lg:col-span-4"
                        title="Statistik Bulanan"
                        :labels="labelsBulanan"
                        :data="dataBulanan"
                        type="bar"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import PetugasCard from "@/components/PetugasCard.vue";
import AntrianList from "@/components/AntrianList.vue";
import StatistikChart from "@/components/StatistikChart.vue";
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const sesi = ref(1);
const petugas = ref([]);
const antrian = ref([]);
const dataHarian = ref([]);
const labelsHarian = ref([]);
const dataBulanan = ref([]);
const labelsBulanan = ref([]);

const sesiAktif = computed(() =>
    petugas.value.filter((p) => p.sesi === sesi.value)
);

onMounted(async () => {
    try {
        const petugasRes = await axios.get("/api/petugas");
        petugas.value = petugasRes.data;

        const antrianRes = await axios.get("/api/antrian");
        antrian.value = antrianRes.data;

        const harianRes = await axios.get("/api/statistik/mingguan");
        labelsHarian.value = harianRes.data.labels;
        dataHarian.value = harianRes.data.data;

        const bulananRes = await axios.get("/api/statistik/bulanan");
        labelsBulanan.value = bulananRes.data.labels;
        dataBulanan.value = bulananRes.data.data;
    } catch (err) {
        console.error("Gagal memuat data dashboard:", err);
    }
});

const activeBtn = "bg-blue-700 text-white px-4 py-1 rounded shadow";
const inactiveBtn = "bg-gray-200 text-gray-600 px-4 py-1 rounded";
</script>
