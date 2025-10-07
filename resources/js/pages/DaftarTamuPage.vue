<script setup>
import { ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();

// ENDPOINT (samakan bila perlu)
const API_BASE = "/api/tamu";
const EXPORT_PDF = "/api/tamu/export/pdf";

const q = ref("");
const loading = ref(false);
const rows = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const perPage = 10;

async function fetchRows(page = 1) {
    loading.value = true;
    try {
        const res = await axios.get(API_BASE, {
            params: { page, per_page: perPage, search: q.value },
        });
        rows.value =
            res.data?.data ??
            res.data?.items ??
            (Array.isArray(res.data) ? res.data : []);
        meta.value = {
            current_page:
                res.data?.current_page ?? res.data?.meta?.current_page ?? page,
            last_page: res.data?.last_page ?? res.data?.meta?.last_page ?? 1,
            total:
                res.data?.total ?? res.data?.meta?.total ?? rows.value.length,
        };
    } catch (e) {
        console.error("Gagal memuat tamu:", e);
        rows.value = [];
        meta.value = { current_page: 1, last_page: 1, total: 0 };
    } finally {
        loading.value = false;
    }
}

// debounce search
let t;
watch(q, () => {
    clearTimeout(t);
    t = setTimeout(() => fetchRows(1), 400);
});
onMounted(() => fetchRows(1));

function gotoPage(p) {
    const target = Math.max(1, Math.min(p, meta.value.last_page));
    if (target !== meta.value.current_page) fetchRows(target);
}
function downloadPdf() {
    const url = `${EXPORT_PDF}?search=${encodeURIComponent(q.value)}`;
    window.open(url, "_blank");
}
async function removeRow(id) {
    if (!confirm("Hapus data tamu ini?")) return;
    await axios.delete(`${API_BASE}/${id}`);
    fetchRows(meta.value.current_page);
}
function goCreate() {
    try {
        router.push({ name: "tamu-create" });
    } catch {
        alert("Form tambah tamu belum tersedia.");
    }
}
function goEdit(id) {
    try {
        router.push({ name: "tamu-edit", params: { id } });
    } catch {
        alert("Form edit tamu belum tersedia.");
    }
}

/* ===== Mapping kolom (tanpa ubah backend) ===== */
const val = (v, f = "-") => (v === null || v === undefined || v === "" ? f : v);
function colNama(r) {
    return val(r.nama ?? r.nama_tamu ?? r.name ?? r.tamu?.nama);
}
function colWa(r) {
    const w =
        r.no_hp ?? r.nomor_whatsapp ?? r.whatsapp ?? r.no_wa ?? r.tamu?.no_hp;
    return val(w);
}
function colInstansi(r) {
    // kosong => tampil '-' (supaya tidak semua 'Umum')
    return r.asal_instansi ?? r.instansi ?? r.tamu?.asal_instansi ?? "";
}
function colJenis(r) {
    return val(r.jenis_kunjungan ?? r.keperluan ?? r.layanan?.jenis_layanan);
}

/* Badge warna */
function pillClass(s) {
    const v = (s || "").toLowerCase();
    if (v.includes("umum")) return "bg-green-50 text-green-700";
    if (v.includes("bappenas")) return "bg-violet-50 text-violet-700";
    if (v.includes("kominfo")) return "bg-indigo-50 text-indigo-700";
    if (v.includes("kesehat")) return "bg-rose-50 text-rose-700";
    if (v.includes("pendidik")) return "bg-blue-50 text-blue-700";
    return "bg-slate-50 text-slate-600";
}
</script>

<template>
    <!-- FULL WIDTH + radial background (tidak pakai container) -->
    <div
        class="min-h-full bg-[radial-gradient(120%_120%_at_0%_0%,#FFE2C7_0%,#F7F1EC_45%,#EEF4FF_100%)]"
    >
        <!-- Header + actions: persis style Daftar Kunjungan -->
        <div class="rounded-3xl bg-gradient-to-b from-blue-50 to-orange-50">
            <div
                class="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between"
            >
                <h1 class="text-2xl font-semibold text-slate-800">
                    Daftar Tamu
                </h1>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search -->
                    <label class="relative block w-[560px] max-w-[72vw]">
                        <input
                            v-model="q"
                            type="text"
                            placeholder="Hinted search text"
                            class="w-full rounded-full border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-slate-700 outline-none ring-1 ring-transparent placeholder:text-slate-400 focus:ring-2 focus:ring-blue-400"
                        />
                        <svg
                            class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <path
                                d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                        </svg>
                    </label>

                    <!-- Tambah (warna sama dengan Kunjungan) -->
                    <button
                        @click="goCreate"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-800"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                        >
                            <path
                                d="M12 5v14M5 12h14"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                        </svg>
                        Tambah
                    </button>

                    <!-- Unduh PDF (outline putih abu seperti Kunjungan) -->
                    <button
                        @click="downloadPdf"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                        >
                            <path
                                d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"
                            />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        Unduh Daftar Tamu PDF
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="p-6">
                <div class="overflow-x-auto rounded-2xl bg-white shadow">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="px-6 py-3">Nama Tamu</th>
                                <th class="px-6 py-3">Nomor WhatsApp</th>
                                <th class="px-6 py-3">Asal Instansi</th>
                                <th class="px-6 py-3">Jenis Kunjungan</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Loading -->
                            <tr v-if="loading">
                                <td
                                    colspan="5"
                                    class="px-6 py-10 text-center text-slate-500"
                                >
                                    Memuat data…
                                </td>
                            </tr>

                            <!-- Empty -->
                            <tr v-else-if="rows.length === 0">
                                <td
                                    colspan="5"
                                    class="px-6 py-10 text-center text-slate-500"
                                >
                                    Tidak ada data tamu.
                                </td>
                            </tr>

                            <!-- Rows -->
                            <tr
                                v-else
                                v-for="r in rows"
                                :key="r.id"
                                class="hover:bg-slate-50/40"
                            >
                                <td class="px-6 py-4 text-slate-800">
                                    {{ colNama(r) }}
                                </td>
                                <td
                                    class="px-6 py-4 tabular-nums text-slate-700"
                                >
                                    {{ colWa(r) }}
                                </td>
                                <td class="px-6 py-4">
                                    <template v-if="colInstansi(r)">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-medium"
                                            :class="pillClass(colInstansi(r))"
                                        >
                                            {{ colInstansi(r) }}
                                        </span>
                                    </template>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-6 py-4 text-slate-800">
                                    {{ colJenis(r) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        @click="goEdit(r.id)"
                                        class="mr-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-200"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="removeRow(r.id)"
                                        class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex items-center justify-between text-sm">
                    <div class="text-slate-600">
                        Halaman {{ meta.current_page }} dari
                        {{ meta.last_page }} · Total {{ meta.total }} data
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="gotoPage(meta.current_page - 1)"
                            :disabled="meta.current_page <= 1"
                            class="rounded-lg border border-slate-200 px-3 py-1.5 disabled:opacity-50"
                        >
                            Sebelumnya
                        </button>
                        <button
                            @click="gotoPage(meta.current_page + 1)"
                            :disabled="meta.current_page >= meta.last_page"
                            class="rounded-lg border border-slate-200 px-3 py-1.5 disabled:opacity-50"
                        >
                            Berikutnya
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
