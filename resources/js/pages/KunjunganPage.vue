<script setup>
import { ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();

const currentTab = ref("today"); // 'today' | 'history'
const q = ref("");
const loading = ref(false);

const rows = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const perPage = 10;

async function fetchRows(page = 1) {
    loading.value = true;
    try {
        const res = await axios.get("/api/kunjungan", {
            params: {
                page,
                per_page: perPage,
                view: currentTab.value,
                search: q.value,
            },
        });
        rows.value = res.data?.data ?? [];
        meta.value = {
            current_page: res.data?.current_page ?? 1,
            last_page: res.data?.last_page ?? 1,
            total: res.data?.total ?? rows.value.length,
        };
    } catch (err) {
        console.error("Gagal memuat kunjungan:", err);
        rows.value = [];
        meta.value = { current_page: 1, last_page: 1, total: 0 };
    } finally {
        loading.value = false;
    }
}

let searchTimer;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchRows(1), 400);
});
watch(currentTab, () => fetchRows(1));
onMounted(() => fetchRows(1));

function changeTab(tab) {
    if (currentTab.value !== tab) currentTab.value = tab;
}

function gotoPage(p) {
    const target = Math.max(1, Math.min(p, meta.value.last_page));
    if (target !== meta.value.current_page) fetchRows(target);
}

function downloadPdf() {
    const url = `/api/kunjungan/export/pdf?view=${encodeURIComponent(
        currentTab.value
    )}&search=${encodeURIComponent(q.value)}`;
    window.open(url, "_blank");
}

async function removeRow(id) {
    if (!confirm("Hapus data kunjungan ini?")) return;
    await axios.delete(`/api/kunjungan/${id}`);
    fetchRows(meta.value.current_page);
}

function goCreate() {
    // Sesuaikan nama/route form tambah milikmu jika sudah ada
    try {
        router.push({ name: "kunjungan-create" });
    } catch {
        alert("Form tambah kunjungan belum tersedia di proyek ini.");
    }
}

function formatTanggal(val) {
    if (!val) return "-";
    const d = new Date(val);
    // Riwayat menampilkan hari + tanggal; Hari Ini tidak menampilkan tanggal
    if (currentTab.value === "history") {
        return d.toLocaleDateString("id-ID", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric",
        });
    }
    return d.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
    });
}

function pillClass(instansi) {
    if (!instansi || instansi.toLowerCase() === "umum") {
        return "bg-green-50 text-green-700";
    }
    return "bg-violet-50 text-violet-700";
}
</script>

<template>
    <div class="min-h-full">
        <!-- Header + Actions -->
        <div class="rounded-3xl bg-gradient-to-b from-blue-50 to-orange-50">
            <div
                class="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between"
            >
                <h1 class="text-2xl font-semibold text-slate-800">
                    Daftar Kunjungan
                </h1>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search -->
                    <label class="relative block w-[560px] max-w-[72vw]">
                        <input
                            v-model="q"
                            type="text"
                            placeholder="Hinted search text"
                            class="w-full rounded-full border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-slate-700 outline-none ring-1 ring-transparent focus:ring-2 focus:ring-blue-400"
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

                    <!-- Tambah -->
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

                    <!-- Unduh PDF -->
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
                        Unduh Daftar Kunjungan PDF
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="px-6">
                <div class="border-b border-slate-200">
                    <nav class="-mb-px flex gap-6">
                        <button
                            @click="changeTab('today')"
                            :class="[
                                'py-3 text-sm font-medium',
                                currentTab === 'today'
                                    ? 'border-b-2 border-blue-600 text-blue-700'
                                    : 'text-slate-500 hover:text-slate-700',
                            ]"
                        >
                            Hari Ini
                        </button>
                        <button
                            @click="changeTab('history')"
                            :class="[
                                'py-3 text-sm font-medium',
                                currentTab === 'history'
                                    ? 'border-b-2 border-blue-600 text-blue-700'
                                    : 'text-slate-500 hover:text-slate-700',
                            ]"
                        >
                            Riwayat
                        </button>
                    </nav>
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
                                <th
                                    v-if="currentTab === 'history'"
                                    class="px-6 py-3"
                                >
                                    Tanggal Kunjungan
                                </th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Loading -->
                            <tr v-if="loading">
                                <td
                                    colspan="6"
                                    class="px-6 py-10 text-center text-slate-500"
                                >
                                    Memuat data…
                                </td>
                            </tr>

                            <!-- Empty -->
                            <tr v-else-if="rows.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-10 text-center text-slate-500"
                                >
                                    Tidak ada data kunjungan.
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
                                    {{ r.tamu?.nama ?? "-" }}
                                </td>
                                <td
                                    class="px-6 py-4 tabular-nums text-slate-700"
                                >
                                    {{ r.tamu?.no_hp ?? "-" }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="
                                            pillClass(r.tamu?.asal_instansi)
                                        "
                                    >
                                        {{ r.tamu?.asal_instansi ?? "Umum" }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-800">
                                    {{
                                        r.layanan?.jenis_layanan ??
                                        r.keperluan ??
                                        "-"
                                    }}
                                </td>
                                <td
                                    v-if="currentTab === 'history'"
                                    class="px-6 py-4 text-slate-700"
                                >
                                    {{
                                        formatTanggal(
                                            r.tanggal_kunjungan ?? r.created_at
                                        )
                                    }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        disabled
                                        class="mr-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-500 cursor-not-allowed"
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
