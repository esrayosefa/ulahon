<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
import axios from "axios";

/* ========== STATE ========== */
const tab = ref("today"); // today | history

// Hari Ini
const today = ref({
    piket: null, // { id_piket, tanggal, sesi, petugas }
    report: null, // { mulai_at, selesai_at, status_kehadiran }
    session_times: {
        1: { start: "08:00:00", end: "12:00:00" },
        2: { start: "12:00:00", end: "16:00:00" },
    },
    now: new Date().toISOString(),
});
const loadingToday = ref(false);

const now = ref(new Date());
let timer = null;
onMounted(() => {
    timer = setInterval(() => (now.value = new Date()), 1000);
});
onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});

const sesiLabel = computed(() => {
    const s = today.value?.piket?.sesi;
    if (!s) return "-";
    const st = today.value.session_times[s]?.start ?? "00:00:00";
    const en = today.value.session_times[s]?.end ?? "00:00:00";
    return `${st.slice(0, 5)} - ${en.slice(0, 5)}`;
});

function fmtDate(d) {
    if (!d) return "-";
    return new Date(d).toLocaleDateString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });
}
function fmtTime(d) {
    if (!d) return "-";
    const t = new Date(d);
    return t.toLocaleTimeString("id-ID", { hour12: false });
}

const startPlan = computed(() => {
    const p = today.value.piket;
    if (!p) return null;
    const st = today.value.session_times[p.sesi]?.start ?? "00:00:00";
    return new Date(`${p.tanggal}T${st}`);
});
const endPlan = computed(() => {
    const p = today.value.piket;
    if (!p) return null;
    const en = today.value.session_times[p.sesi]?.end ?? "00:00:00";
    return new Date(`${p.tanggal}T${en}`);
});

/** Determinasi fase UI:
 * - noSchedule: tidak piket hari ini
 * - before: sebelum jam mulai
 * - startable: sudah tiba, belum mulai
 * - running: sudah mulai, belum selesai
 * - finished: sudah selesai
 */
const phase = computed(() => {
    const p = today.value.piket;
    if (!p) return "noSchedule";
    const rep = today.value.report;
    const n = now.value;
    if (rep?.selesai_at) return "finished";
    if (rep?.mulai_at) return "running";
    if (n < startPlan.value) return "before";
    return "startable";
});

const countdownText = computed(() => {
    if (!startPlan.value) return "";
    let diff = Math.floor((startPlan.value - now.value) / 1000);
    if (diff < 0) diff = 0;
    const h = String(Math.floor(diff / 3600)).padStart(2, "0");
    const m = String(Math.floor((diff % 3600) / 60)).padStart(2, "0");
    const s = String(diff % 60).padStart(2, "0");
    if (h === "00") return `${parseInt(m) ? m + " menit " : ""}${s} detik lagi`;
    return `${h}:${m}:${s} lagi`;
});

async function loadToday() {
    loadingToday.value = true;
    try {
        const { data } = await axios.get("/api/piket/report/today");
        today.value = data;
    } finally {
        loadingToday.value = false;
    }
}

/* ========== ACTIONS ========== */
const starting = ref(false);
async function startSession() {
    if (!today.value.piket) return;
    starting.value = true;
    try {
        await axios.post("/api/piket/report/start", {
            id_piket: today.value.piket.id_piket,
        });
        await loadToday();
    } catch (e) {
        alert(e?.response?.data?.message || "Gagal memulai sesi.");
    } finally {
        starting.value = false;
    }
}

const finishing = ref(false);
const showFinishModal = ref(false);
const finishNote = ref("");

async function finishSession() {
    if (!today.value.piket) return;
    finishing.value = true;
    try {
        await axios.post("/api/piket/report/finish", {
            id_piket: today.value.piket.id_piket,
            catatan: finishNote.value || null,
        });
        showFinishModal.value = false;
        finishNote.value = "";
        await loadToday();
    } catch (e) {
        alert(e?.response?.data?.message || "Gagal mengakhiri sesi.");
    } finally {
        finishing.value = false;
    }
}

/* ========== RIWAYAT ========== */
const search = ref("");
const historyList = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const loadingHistory = ref(false);
const perPage = 12;

async function fetchHistory(page = 1) {
    loadingHistory.value = true;
    try {
        const { data } = await axios.get("/api/piket/report/history", {
            params: { page, per_page: perPage, search: search.value },
        });
        historyList.value = data.data ?? [];
        meta.value = {
            current_page: data.current_page ?? 1,
            last_page: data.last_page ?? 1,
            total: data.total ?? historyList.value.length,
        };
    } finally {
        loadingHistory.value = false;
    }
}

function gotoPage(p) {
    const t = Math.max(1, Math.min(p, meta.value.last_page));
    if (t !== meta.value.current_page) fetchHistory(t);
}

function badgeClass(st) {
    switch (st) {
        case "tepat_waktu":
            return "bg-emerald-100 text-emerald-700";
        case "terlambat_a":
            return "bg-violet-100 text-violet-700";
        case "terlambat_b":
            return "bg-slate-300 text-slate-700";
        case "tidak_hadir":
            return "bg-red-100 text-red-700";
        default:
            return "bg-slate-100 text-slate-600";
    }
}
function badgeText(st) {
    return (
        {
            tepat_waktu: "Tepat Waktu",
            terlambat_a: "Terlambat A",
            terlambat_b: "Terlambat B",
            tidak_hadir: "Tidak Hadir",
        }[st] || "-"
    );
}

let sTimer;
onMounted(() => {
    loadToday();
    fetchHistory(1);
    // debounce search
    sTimer = null;
});
onBeforeUnmount(() => {
    if (sTimer) clearTimeout(sTimer);
});

function onSearchInput() {
    clearTimeout(sTimer);
    sTimer = setTimeout(() => fetchHistory(1), 350);
}
</script>

<template>
    <div class="p-6 space-y-4">
        <h1 class="text-2xl font-semibold text-slate-800">Laporan Piket</h1>

        <!-- Tabs -->
        <div class="flex gap-6 border-b">
            <button
                class="pb-2 -mb-px border-b-2"
                :class="
                    tab === 'today'
                        ? 'border-emerald-500 text-emerald-600'
                        : 'border-transparent text-slate-500'
                "
                @click="tab = 'today'"
            >
                Hari Ini
            </button>
            <button
                class="pb-2 -mb-px border-b-2"
                :class="
                    tab === 'history'
                        ? 'border-emerald-500 text-emerald-600'
                        : 'border-transparent text-slate-500'
                "
                @click="tab = 'history'"
            >
                Riwayat
            </button>
        </div>

        <!-- ===================== HARI INI ===================== -->
        <section v-if="tab === 'today'">
            <div class="mt-4">
                <!-- Header tanggal & sesi -->
                <div
                    class="rounded-2xl bg-blue-700 text-white shadow p-6 flex items-center justify-between"
                >
                    <div class="text-2xl font-semibold">
                        {{
                            today.piket
                                ? fmtDate(today.piket.tanggal)
                                : "Tidak ada piket hari ini"
                        }}
                    </div>
                    <div class="text-xl font-semibold">
                        {{ today.piket ? sesiLabel : "" }}
                    </div>
                </div>

                <!-- Timeline kondisi -->
                <div class="mt-6 pl-6 relative">
                    <div
                        class="absolute left-3 top-0 bottom-0 w-0.5 bg-slate-200"
                    ></div>

                    <!-- Tidak ada jadwal -->
                    <div
                        v-if="!today.piket && !loadingToday"
                        class="text-slate-500 mt-6"
                    >
                        Anda tidak memiliki jadwal piket hari ini.
                    </div>

                    <!-- Kondisi: sebelum jam mulai -->
                    <div
                        v-if="today.piket && phase === 'before'"
                        class="relative mb-8"
                    >
                        <div
                            class="absolute -left-3 top-2 h-2 w-2 rounded-full bg-sky-500"
                        ></div>
                        <div class="text-slate-700 font-medium">
                            Waktu Piket Akan Tiba
                        </div>
                        <div
                            class="mt-3 inline-block rounded-xl bg-white shadow px-4 py-3 text-orange-500 font-semibold"
                        >
                            {{ countdownText }}
                        </div>
                    </div>

                    <!-- Kondisi: sudah tiba, belum mulai -->
                    <div
                        v-if="today.piket && phase === 'startable'"
                        class="relative mb-8"
                    >
                        <div
                            class="absolute -left-3 top-2 h-2 w-2 rounded-full bg-sky-500"
                        ></div>
                        <div class="text-slate-700 font-medium">
                            Waktu Piket Sudah Tiba
                        </div>
                        <div class="mt-3 flex items-center gap-4">
                            <div
                                class="inline-block rounded-xl bg-white shadow px-4 py-3 text-orange-500 font-bold text-xl"
                            >
                                {{
                                    now.toLocaleTimeString("id-ID", {
                                        hour12: false,
                                    })
                                }}
                            </div>
                            <button
                                class="rounded-md bg-orange-500 hover:bg-orange-600 text-white px-4 py-2"
                                :disabled="starting"
                                @click="startSession"
                            >
                                {{ starting ? "Memulai…" : "Mulai Sesi" }}
                            </button>
                        </div>
                    </div>

                    <!-- Kondisi: sedang berlangsung -->
                    <div
                        v-if="today.piket && phase === 'running'"
                        class="relative mb-8"
                    >
                        <div
                            class="absolute -left-3 top-2 h-2 w-2 rounded-full bg-emerald-500"
                        ></div>
                        <div class="text-slate-700 font-medium">
                            Piket Berlangsung
                        </div>
                        <div class="mt-3 flex items-center gap-4">
                            <div
                                class="inline-block rounded-xl bg-white shadow px-4 py-3 text-orange-500 font-bold text-xl"
                            >
                                {{
                                    now.toLocaleTimeString("id-ID", {
                                        hour12: false,
                                    })
                                }}
                            </div>
                            <button
                                class="rounded-md bg-orange-500 hover:bg-orange-600 text-white px-4 py-2"
                                @click="showFinishModal = true"
                            >
                                Akhiri Sesi dan Isi Laporan
                            </button>
                        </div>

                        <div class="mt-6 text-slate-600">
                            <div class="mb-2">
                                Piket Dimulai:
                                <span class="font-medium">{{
                                    fmtTime(today.report?.mulai_at)
                                }}</span>
                            </div>
                            <div>
                                Rencana Selesai:
                                <span class="font-medium">{{
                                    endPlan
                                        ? endPlan.toLocaleTimeString("id-ID", {
                                              hour12: false,
                                          })
                                        : "-"
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kondisi: selesai -->
                    <div
                        v-if="today.piket && phase === 'finished'"
                        class="relative mb-8"
                    >
                        <div
                            class="absolute -left-3 top-2 h-2 w-2 rounded-full bg-slate-500"
                        ></div>
                        <div class="text-slate-700 font-medium">
                            Piket Dimulai
                        </div>
                        <div
                            class="mt-2 inline-block rounded-xl bg-white shadow px-4 py-3 text-orange-500 font-semibold"
                        >
                            {{ fmtTime(today.report?.mulai_at) }}
                        </div>

                        <div class="mt-6 text-slate-700 font-medium">
                            Status Kehadiran:
                            <span
                                class="ml-2 px-2 py-1 rounded text-xs"
                                :class="
                                    badgeClass(today.report?.status_kehadiran)
                                "
                                >{{
                                    badgeText(today.report?.status_kehadiran)
                                }}</span
                            >
                        </div>

                        <div class="mt-4">
                            Piket Diakhiri:
                            <span class="font-medium">{{
                                fmtTime(today.report?.selesai_at)
                            }}</span>
                        </div>

                        <div v-if="today.report?.catatan" class="mt-4">
                            <div class="text-slate-700 font-medium mb-1">
                                Catatan:
                            </div>
                            <div
                                class="rounded-md border bg-white p-3 text-slate-700"
                            >
                                {{ today.report.catatan }}
                            </div>
                        </div>
                    </div>

                    <!-- titik terakhir -->
                    <div class="relative">
                        <div
                            class="absolute -left-3 top-1 h-2 w-2 rounded-full bg-slate-400"
                        ></div>
                        <div class="text-slate-700">Piket Dibuat</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== RIWAYAT ===================== -->
        <section v-else>
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1">
                    <input
                        v-model="search"
                        @input="onSearchInput"
                        type="text"
                        placeholder="Cari nama, tanggal, sesi, atau status…"
                        class="w-full max-w-xl rounded-full border border-slate-200 bg-white py-2.5 pl-4 pr-4 text-slate-700 outline-none focus:ring-2 focus:ring-blue-400"
                    />
                </div>

                <!-- Tombol PDF bisa diaktifkan jika endpoint siap -->
                <button
                    class="rounded-lg border px-3 py-2 text-slate-600 hover:bg-slate-50"
                    disabled
                >
                    Unduh Laporan Piket PDF
                </button>
            </div>

            <div class="mt-4 rounded-2xl bg-white shadow overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-slate-50 text-slate-600 text-sm">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama Petugas</th>
                            <th class="px-4 py-3 text-left">Tanggal Piket</th>
                            <th class="px-4 py-3 text-left">Sesi</th>
                            <th class="px-4 py-3 text-left">Jam Mulai Piket</th>
                            <th class="px-4 py-3 text-left">
                                Jam Selesai Piket
                            </th>
                            <th class="px-4 py-3 text-left">
                                Status Kehadiran
                            </th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="row in historyList"
                            :key="row.id_laporan"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-4 py-3">
                                {{ row.petugas?.name || "-" }}
                            </td>
                            <td class="px-4 py-3">
                                {{ fmtDate(row.tanggal) }}
                            </td>
                            <td class="px-4 py-3">{{ row.sesi }}</td>
                            <td class="px-4 py-3 font-semibold">
                                {{ fmtTime(row.mulai_at) }}
                            </td>
                            <td class="px-4 py-3 font-semibold">
                                {{ fmtTime(row.selesai_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-1 rounded text-xs"
                                    :class="badgeClass(row.status_kehadiran)"
                                >
                                    {{ badgeText(row.status_kehadiran) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    class="rounded-md bg-slate-100 px-3 py-1.5 text-sm hover:bg-slate-200"
                                >
                                    Detail
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!loadingHistory && historyList.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-8 text-center text-slate-500"
                            >
                                Tidak ada data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-3 flex items-center justify-between text-sm">
                <div class="text-slate-600">
                    Halaman {{ meta.current_page }} dari {{ meta.last_page }} ·
                    Total {{ meta.total }}
                </div>
                <div class="flex gap-2">
                    <button
                        class="rounded-lg border px-3 py-1.5 disabled:opacity-50"
                        :disabled="meta.current_page <= 1"
                        @click="gotoPage(meta.current_page - 1)"
                    >
                        Sebelumnya
                    </button>
                    <button
                        class="rounded-lg border px-3 py-1.5 disabled:opacity-50"
                        :disabled="meta.current_page >= meta.last_page"
                        @click="gotoPage(meta.current_page + 1)"
                    >
                        Berikutnya
                    </button>
                </div>
            </div>
        </section>

        <!-- Modal Akhiri Sesi -->
        <teleport to="body">
            <transition name="fade">
                <div
                    v-if="showFinishModal"
                    class="fixed inset-0 z-[100] flex items-center justify-center"
                >
                    <div
                        class="absolute inset-0 bg-slate-900/50"
                        @click="showFinishModal = false"
                    ></div>
                    <div
                        class="relative z-10 w-[92vw] max-w-xl rounded-2xl bg-white shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between px-5 py-4 border-b"
                        >
                            <h3 class="text-lg font-semibold">
                                Akhiri Sesi & Isi Laporan
                            </h3>
                            <button
                                class="rounded-md p-1.5 hover:bg-slate-100"
                                @click="showFinishModal = false"
                                aria-label="Tutup"
                            >
                                <svg
                                    class="h-5 w-5 text-slate-500"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                >
                                    <path
                                        d="M6 18L18 6M6 6l12 12"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </button>
                        </div>

                        <form
                            class="px-5 py-4 space-y-4"
                            @submit.prevent="finishSession"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 mb-1"
                                    >Catatan (opsional)</label
                                >
                                <textarea
                                    v-model="finishNote"
                                    rows="4"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:border-blue-400"
                                    placeholder="Ringkas layanan, kejadian penting, dsb."
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    class="rounded-lg border px-4 py-2 hover:bg-slate-50"
                                    @click="showFinishModal = false"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    class="rounded-lg bg-orange-500 px-4 py-2 text-white hover:bg-orange-600"
                                    :disabled="finishing"
                                >
                                    {{
                                        finishing ? "Menyimpan…" : "Akhiri Sesi"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </transition>
        </teleport>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
