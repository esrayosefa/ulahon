<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();
const role = computed(() => auth.user?.role || "viewer");
const isAdmin = computed(() => role.value === "admin");
const isViewer = computed(() => role.value === "viewer");

/* =========================
   FETCH OVERVIEW
========================= */
const loading = ref(false);
const overview = ref({
    session_times: { 1: "08.00–12.00", 2: "12.00–16.00" },
    today: { 1: null, 2: null },
    week: [],
    mine: [],
});

async function fetchOverview() {
    loading.value = true;
    try {
        const { data } = await axios.get("/api/piket/overview");
        overview.value = data;
    } finally {
        loading.value = false;
    }
}

function fmtDate(d) {
    if (!d) return "-";
    return new Date(d).toLocaleDateString("id-ID", {
        weekday: "long",
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
}
function getPk(it) {
    // dukung id dari berbagai nama kolom: id | id_piket
    return it?.id ?? it?.id_piket ?? null;
}

onMounted(async () => {
    await fetchOverview();
    if (isAdmin.value) await fetchPending();
});

/* =========================
   USER: AJUKAN TUKAR
========================= */
const showSwap = ref(false);
const swapSubmitting = ref(false);
const swapErrors = ref({});
const swapForm = ref({
    id_piket_awal: null,
    id_piket_tukar: "",
    alasan: "",
});

const swapCandidates = computed(() => {
    const curr = swapForm.value.id_piket_awal;
    return (overview.value.week || []).filter((it) => getPk(it) !== curr);
});

function labelPiket(it) {
    const tgl = fmtDate(it.tanggal);
    const sesiTxt = typeof it.sesi !== "undefined" ? `Sesi ${it.sesi}` : "-";
    const petugas = it.petugas?.name || "-";
    return `${tgl} · ${sesiTxt} · ${petugas}`;
}

function openSwap(item) {
    swapErrors.value = {};
    swapForm.value = {
        id_piket_awal: getPk(item),
        id_piket_tukar: "",
        alasan: "",
    };
    showSwap.value = true;
}
function closeSwap() {
    showSwap.value = false;
}

async function submitSwap() {
    swapErrors.value = {};
    swapSubmitting.value = true;
    try {
        await axios.post("/api/piket/swap-requests", {
            id_piket_awal: swapForm.value.id_piket_awal,
            id_piket_tukar: swapForm.value.id_piket_tukar,
            alasan: swapForm.value.alasan || null,
        });
        closeSwap();
        alert("Pengajuan tukar dikirim.");
    } catch (e) {
        const v = e?.response?.data?.errors;
        if (v) swapErrors.value = v;
        else alert("Gagal mengirim pengajuan.");
    } finally {
        swapSubmitting.value = false;
    }
}

/* =========================
   ADMIN: PENDING REQUESTS
========================= */
const pending = ref([]);
const pendingMeta = ref({ current_page: 1, last_page: 1, total: 0 });
const loadingPending = ref(false);

function getSwapId(it) {
    return it?.id ?? it?.id_tukar ?? null;
}

async function fetchPending(page = 1) {
    if (!isAdmin.value) return;
    loadingPending.value = true;
    try {
        const { data } = await axios.get("/api/piket/swap-requests", {
            params: { status: "pending", per_page: 10, page },
        });
        pending.value = data.data ?? [];
        pendingMeta.value = {
            current_page: data.current_page ?? 1,
            last_page: data.last_page ?? 1,
            total: data.total ?? pending.value.length,
        };
    } finally {
        loadingPending.value = false;
    }
}

function gotoPending(p) {
    const t = Math.max(1, Math.min(p, pendingMeta.value.last_page));
    if (t !== pendingMeta.value.current_page) fetchPending(t);
}

const showDecide = ref(false);
const decideMode = ref("approve"); // approve | reject
const decideItem = ref(null);
const decideSubmitting = ref(false);
const decideErrors = ref({});

function openDecide(item, mode) {
    decideErrors.value = {};
    decideMode.value = mode;
    decideItem.value = item;
    showDecide.value = true;
}
function closeDecide() {
    showDecide.value = false;
}

async function submitDecide() {
    if (!decideItem.value) return;
    decideErrors.value = {};
    decideSubmitting.value = true;
    try {
        const id = getSwapId(decideItem.value);
        await axios.post(`/api/piket/swap-requests/${id}/decide`, {
            aksi: decideMode.value,
        });
        closeDecide();
        await Promise.all([
            fetchPending(pendingMeta.value.current_page),
            fetchOverview(),
        ]);
    } catch (e) {
        const v = e?.response?.data?.errors;
        if (v) decideErrors.value = v;
        else alert("Gagal menyimpan keputusan.");
    } finally {
        decideSubmitting.value = false;
    }
}
</script>

<template>
    <div class="p-6 space-y-6">
        <!-- Petugas Layanan Hari Ini -->
        <div
            class="rounded-3xl bg-gradient-to-br from-blue-50 to-orange-50 p-6 shadow"
        >
            <div class="flex justify-center">
                <div
                    class="rounded-full bg-white shadow px-6 py-2 text-blue-900 font-semibold"
                >
                    Petugas Layanan Hari Ini
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sesi 1 -->
                <div
                    class="rounded-2xl bg-white shadow p-6 flex items-center justify-between"
                >
                    <div class="flex items-center gap-4">
                        <img
                            :src="
                                overview.today['1']?.petugas?.foto ||
                                '/images/default-avatar.png'
                            "
                            class="h-24 w-24 rounded-full object-cover border"
                            alt="Petugas"
                        />
                        <div>
                            <div class="text-slate-900 font-medium">
                                {{
                                    overview.today["1"]?.petugas?.name ||
                                    "Belum dijadwalkan"
                                }}
                            </div>
                            <div class="text-sm text-slate-600">
                                {{ overview.session_times?.[1] || "Sesi 1" }}
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-slate-500">1</div>
                </div>

                <!-- Sesi 2 -->
                <div
                    class="rounded-2xl bg-white shadow p-6 flex items-center justify-between"
                >
                    <div class="flex items-center gap-4">
                        <img
                            :src="
                                overview.today['2']?.petugas?.foto ||
                                '/images/default-avatar.png'
                            "
                            class="h-24 w-24 rounded-full object-cover border"
                            alt="Petugas"
                        />
                        <div>
                            <div class="text-slate-900 font-medium">
                                {{
                                    overview.today["2"]?.petugas?.name ||
                                    "Belum dijadwalkan"
                                }}
                            </div>
                            <div class="text-sm text-slate-600">
                                {{ overview.session_times?.[2] || "Sesi 2" }}
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-slate-500">2</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Jadwal Piket Minggu Ini -->
            <div class="rounded-2xl bg-white shadow">
                <div class="px-6 pt-5 pb-3">
                    <h2 class="text-xl font-semibold text-slate-800">
                        Jadwal Piket Minggu Ini
                    </h2>
                </div>
                <div class="px-6 pb-6">
                    <div class="overflow-hidden rounded-xl border">
                        <table class="min-w-full divide-y">
                            <thead class="bg-slate-50 text-slate-600 text-sm">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tanggal</th>
                                    <th class="px-4 py-2 text-left">Sesi</th>
                                    <th class="px-4 py-2 text-left">Petugas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y bg-white">
                                <tr
                                    v-for="row in overview.week"
                                    :key="getPk(row)"
                                    class="hover:bg-slate-50"
                                >
                                    <td class="px-4 py-2">
                                        {{ fmtDate(row.tanggal) }}
                                    </td>
                                    <td class="px-4 py-2">
                                        Sesi {{ row.sesi }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ row.petugas?.name || "-" }}
                                    </td>
                                </tr>
                                <tr v-if="overview.week.length === 0">
                                    <td
                                        colspan="3"
                                        class="px-4 py-6 text-center text-slate-500"
                                    >
                                        Belum ada jadwal.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Jadwal Piket Saya (user & admin) -->
            <div v-if="!isViewer" class="rounded-2xl bg-white shadow">
                <div class="px-6 pt-5 pb-3">
                    <h2 class="text-xl font-semibold text-slate-800">
                        Jadwal Piket Saya
                    </h2>
                </div>
                <div class="px-6 pb-6">
                    <div class="overflow-hidden rounded-xl border">
                        <table class="min-w-full divide-y">
                            <thead class="bg-slate-50 text-slate-600 text-sm">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tanggal</th>
                                    <th class="px-4 py-2 text-left">Sesi</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y bg-white">
                                <tr
                                    v-for="row in overview.mine"
                                    :key="getPk(row)"
                                    class="hover:bg-slate-50"
                                >
                                    <td class="px-4 py-2">
                                        {{ fmtDate(row.tanggal) }}
                                    </td>
                                    <td class="px-4 py-2">
                                        Sesi {{ row.sesi }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <button
                                            class="rounded-lg bg-blue-700 px-3 py-1.5 text-sm text-white hover:bg-blue-800"
                                            @click="openSwap(row)"
                                        >
                                            Ajukan Tukar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="overview.mine.length === 0">
                                    <td
                                        colspan="3"
                                        class="px-4 py-6 text-center text-slate-500"
                                    >
                                        Tidak ada jadwal minggu ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel Admin: ACC Tukar (hanya admin) -->
            <div
                v-if="isAdmin"
                class="rounded-2xl bg-white shadow xl:col-span-2"
            >
                <div class="px-6 pt-5 pb-3 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-800">
                        Pengajuan Tukar (Pending)
                    </h2>
                    <div class="text-sm text-slate-500">
                        Total: {{ pendingMeta.total }}
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <div class="overflow-hidden rounded-xl border">
                        <table class="min-w-full divide-y">
                            <thead class="bg-slate-50 text-slate-600 text-sm">
                                <tr>
                                    <th class="px-4 py-2 text-left">
                                        Tanggal / Sesi
                                    </th>
                                    <th class="px-4 py-2 text-left">Pemohon</th>
                                    <th class="px-4 py-2 text-left">
                                        Dari &rarr; Ke
                                    </th>
                                    <th class="px-4 py-2 text-left">Alasan</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y bg-white">
                                <tr
                                    v-for="it in pending"
                                    :key="getSwapId(it)"
                                    class="hover:bg-slate-50"
                                >
                                    <td class="px-4 py-2">
                                        <div>
                                            {{ fmtDate(it.awal?.tanggal) }} ·
                                            Sesi {{ it.awal?.sesi }}
                                        </div>
                                        <div class="text-slate-500 text-xs">
                                            → {{ fmtDate(it.tukar?.tanggal) }} ·
                                            Sesi {{ it.tukar?.sesi }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ it.pemohon?.name || "-" }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ it.awal?.petugas?.name || "-" }}
                                        <span class="text-slate-400">→</span>
                                        {{ it.tukar?.petugas?.name || "-" }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ it.alasan || "—" }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <div class="flex gap-2 justify-end">
                                            <button
                                                class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm text-white hover:bg-emerald-700"
                                                @click="
                                                    openDecide(it, 'approve')
                                                "
                                            >
                                                Setujui
                                            </button>
                                            <button
                                                class="rounded-lg bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700"
                                                @click="
                                                    openDecide(it, 'reject')
                                                "
                                            >
                                                Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="pending.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-4 py-6 text-center text-slate-500"
                                    >
                                        Tidak ada pengajuan pending.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- pagination -->
                    <div
                        class="mt-3 flex items-center justify-end gap-2 text-sm"
                    >
                        <button
                            class="rounded-lg border px-3 py-1.5 disabled:opacity-50"
                            :disabled="pendingMeta.current_page <= 1"
                            @click="gotoPending(pendingMeta.current_page - 1)"
                        >
                            Sebelumnya
                        </button>
                        <button
                            class="rounded-lg border px-3 py-1.5 disabled:opacity-50"
                            :disabled="
                                pendingMeta.current_page >=
                                pendingMeta.last_page
                            "
                            @click="gotoPending(pendingMeta.current_page + 1)"
                        >
                            Berikutnya
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ajukan Tukar -->
        <teleport to="body">
            <transition name="fade">
                <div
                    v-if="showSwap"
                    class="fixed inset-0 z-[100] flex items-center justify-center"
                >
                    <div
                        class="absolute inset-0 bg-slate-900/50"
                        @click="closeSwap"
                    ></div>
                    <div
                        class="relative z-10 w-[92vw] max-w-xl rounded-2xl bg-white shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between px-5 py-4 border-b"
                        >
                            <h3 class="text-lg font-semibold">
                                Ajukan Tukar Piket
                            </h3>
                            <button
                                class="rounded-md p-1.5 hover:bg-slate-100"
                                @click="closeSwap"
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
                            @submit.prevent="submitSwap"
                        >
                            <div class="text-sm text-slate-700">
                                <div class="mb-2">
                                    <span class="font-medium">Piket Saya:</span>
                                    <span class="ml-1">
                                        {{
                                            fmtDate(
                                                (
                                                    overview.mine.find(
                                                        (m) =>
                                                            getPk(m) ===
                                                            swapForm.id_piket_awal
                                                    ) || {}
                                                ).tanggal
                                            )
                                        }}
                                        · Sesi
                                        {{
                                            (
                                                overview.mine.find(
                                                    (m) =>
                                                        getPk(m) ===
                                                        swapForm.id_piket_awal
                                                ) || {}
                                            ).sesi
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 mb-1"
                                    >Pilih Piket Pengganti</label
                                >
                                <select
                                    v-model="swapForm.id_piket_tukar"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:border-blue-400"
                                >
                                    <option value="" disabled>
                                        — pilih salah satu —
                                    </option>
                                    <option
                                        v-for="it in swapCandidates"
                                        :key="getPk(it)"
                                        :value="getPk(it)"
                                    >
                                        {{ labelPiket(it) }}
                                    </option>
                                </select>
                                <p
                                    v-if="swapErrors.id_piket_tukar"
                                    class="text-xs text-red-600 mt-1"
                                >
                                    {{ swapErrors.id_piket_tukar[0] }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 mb-1"
                                    >Alasan (opsional)</label
                                >
                                <textarea
                                    v-model="swapForm.alasan"
                                    rows="3"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:border-blue-400"
                                    placeholder="Contoh: dinas luar / sakit / bentrok agenda"
                                ></textarea>
                            </div>

                            <div
                                class="flex items-center justify-end gap-3 pt-2"
                            >
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-50"
                                    @click="closeSwap"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="
                                        swapSubmitting ||
                                        !swapForm.id_piket_tukar
                                    "
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60 hover:bg-blue-700"
                                >
                                    <span v-if="swapSubmitting">Mengirim…</span>
                                    <span v-else>Kirim Pengajuan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </transition>
        </teleport>

        <!-- Modal Keputusan (Admin) -->
        <teleport to="body">
            <transition name="fade">
                <div
                    v-if="showDecide"
                    class="fixed inset-0 z-[110] flex items-center justify-center"
                >
                    <div
                        class="absolute inset-0 bg-slate-900/50"
                        @click="closeDecide"
                    ></div>
                    <div
                        class="relative z-10 w-[92vw] max-w-xl rounded-2xl bg-white shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between px-5 py-4 border-b"
                        >
                            <h3 class="text-lg font-semibold">
                                {{
                                    decideMode === "approve"
                                        ? "Setujui Pengajuan"
                                        : "Tolak Pengajuan"
                                }}
                            </h3>
                            <button
                                class="rounded-md p-1.5 hover:bg-slate-100"
                                @click="closeDecide"
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
                            @submit.prevent="submitDecide"
                        >
                            <div class="text-sm text-slate-700">
                                <div>
                                    <span class="font-medium">Pemohon:</span>
                                    <span class="ml-1">{{
                                        decideItem?.pemohon?.name || "-"
                                    }}</span>
                                </div>
                                <div class="mt-1">
                                    <span class="font-medium">Dari:</span>
                                    <span class="ml-1"
                                        >{{
                                            fmtDate(decideItem?.awal?.tanggal)
                                        }}
                                        · Sesi
                                        {{ decideItem?.awal?.sesi }}</span
                                    >
                                </div>
                                <div>
                                    <span class="font-medium">Ke:</span>
                                    <span class="ml-1"
                                        >{{
                                            fmtDate(decideItem?.tukar?.tanggal)
                                        }}
                                        · Sesi
                                        {{ decideItem?.tukar?.sesi }}</span
                                    >
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end gap-3 pt-2"
                            >
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-50"
                                    @click="closeDecide"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="decideSubmitting"
                                    class="rounded-lg px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                                    :class="
                                        decideMode === 'approve'
                                            ? 'bg-emerald-600 hover:bg-emerald-700'
                                            : 'bg-red-600 hover:bg-red-700'
                                    "
                                >
                                    <span v-if="decideSubmitting"
                                        >Menyimpan…</span
                                    >
                                    <span v-else>{{
                                        decideMode === "approve"
                                            ? "Setujui"
                                            : "Tolak"
                                    }}</span>
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
