<template>
    <div class="flex min-h-screen">
        <div
            class="flex-1 flex flex-col bg-gradient-to-b from-blue-100 to-orange-100"
        >
            <div class="p-6 space-y-6">
                <div
                    v-if="isLoading"
                    class="text-center p-8 bg-white rounded-xl shadow"
                >
                    <p class="text-lg font-semibold text-blue-700">
                        Memuat data dashboard...
                    </p>
                </div>

                <div
                    v-else-if="error"
                    class="text-center p-8 bg-red-100 border border-red-400 text-red-700 rounded-xl shadow"
                >
                    <p class="font-bold mb-2">Gagal memuat data!</p>
                    <p class="text-sm">
                        Mohon periksa server atau API: {{ error }}
                    </p>
                </div>

                <div v-else>
                    <section class="bg-white rounded-xl shadow p-4">
                        <h2 class="font-bold text-lg text-gray-700 mb-4">
                            Petugas Piket Hari Ini
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <p
                                v-if="sesiAktif.length === 0"
                                class="col-span-2 text-center text-gray-500 italic"
                            >
                                Tidak ada petugas piket untuk sesi ini.
                            </p>
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

const isLoading = ref(true);
const error = ref(null);

const sesiAktif = computed(() =>
    // ✅ Safety Check 1: Pastikan petugas.value adalah array sebelum memfilter
    Array.isArray(petugas.value)
        ? petugas.value.filter((p) => p.sesi === sesi.value)
        : []
);

onMounted(async () => {
    isLoading.value = true;
    error.value = null;

    try {
        const [petugasRes, antrianRes, harianRes, bulananRes] =
            await Promise.all([
                axios.get("/api/petugas"),
                axios.get("/api/antrian"),
                axios.get("/api/statistik/mingguan"),
                axios.get("/api/statistik/bulanan"),
            ]);

        // ✅ Safety Check 2: Pastikan data API yang diterima adalah array/objek yang valid
        petugas.value = Array.isArray(petugasRes.data) ? petugasRes.data : [];
        antrian.value = antrianRes.data || [];
        labelsHarian.value = harianRes.data?.labels || [];
        dataHarian.value = harianRes.data?.data || [];
        labelsBulanan.value = bulananRes.data?.labels || [];
        dataBulanan.value = bulananRes.data?.data || [];
    } catch (err) {
        console.error("Gagal memuat data dashboard:", err);

        error.value = `Gagal terhubung ke API. (${
            err.response?.statusText ||
            err.message ||
            "Kesalahan tidak diketahui"
        })`;
    } finally {
        isLoading.value = false;
    }
});

const activeBtn = "bg-blue-700 text-white px-4 py-1 rounded shadow";
const inactiveBtn = "bg-gray-200 text-gray-600 px-4 py-1 rounded";
</script>
