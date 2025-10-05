<script setup>
import { ref, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";

const route = useRoute();
const q = ref(route.query.q || "");
const scope = ref(route.query.scope || "all");
const result = ref({ tamu: [], kunjungan: [] });
const loading = ref(false);

async function fetchAll() {
    if (!q.value) {
        result.value = { tamu: [], kunjungan: [] };
        return;
    }
    loading.value = true;
    try {
        const { data } = await axios.get("/api/search", {
            params: { q: q.value, scope: scope.value },
        });
        result.value = data;
    } finally {
        loading.value = false;
    }
}
watch(() => route.fullPath, fetchAll);
onMounted(fetchAll);
</script>

<template>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-semibold text-slate-800">
            Hasil untuk “{{ q }}”
        </h1>

        <div v-if="loading" class="text-slate-500">Memuat…</div>

        <section v-else class="space-y-6">
            <div v-if="scope === 'all' || scope === 'tamu'">
                <h2 class="mb-2 font-medium text-slate-700">Tamu</h2>
                <div
                    v-if="(result.tamu || []).length === 0"
                    class="text-slate-500 text-sm"
                >
                    Tidak ada.
                </div>
                <ul v-else class="divide-y rounded-xl bg-white shadow">
                    <li v-for="t in result.tamu" :key="t.id" class="p-4">
                        <div class="font-medium text-slate-800">
                            {{ t.nama }}
                        </div>
                        <div class="text-sm text-slate-600">
                            {{ t.no_hp }} · {{ t.asal_instansi || "Umum" }}
                        </div>
                    </li>
                </ul>
            </div>

            <div v-if="scope === 'all' || scope === 'kunjungan'">
                <h2 class="mb-2 font-medium text-slate-700">Kunjungan</h2>
                <div
                    v-if="(result.kunjungan || []).length === 0"
                    class="text-slate-500 text-sm"
                >
                    Tidak ada.
                </div>
                <ul v-else class="divide-y rounded-xl bg-white shadow">
                    <li v-for="k in result.kunjungan" :key="k.id" class="p-4">
                        <div class="font-medium text-slate-800">
                            {{ k.tamu?.nama }}
                        </div>
                        <div class="text-sm text-slate-600">
                            {{ k.layanan?.jenis_layanan ?? k.keperluan ?? "-" }}
                        </div>
                    </li>
                </ul>
            </div>
        </section>
    </div>
</template>
