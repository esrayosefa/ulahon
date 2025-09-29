<script setup>
import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onBeforeUnmount,
} from "vue";
import axios from "axios";

const props = defineProps({
    userRole: { type: String, default: "admin" }, // 'viewer' | 'user' | 'admin'
});

const canManage = computed(() => ["user", "admin"].includes(props.userRole));

const q = ref("");
const page = ref(1);
const perPage = ref(10);
const total = ref(0);
const rows = ref([]);

const ui = reactive({ loading: false, error: "" });

const exportMenuOpen = ref(false);
const exportMenuRef = ref(null);

function maskWhatsapp(num) {
    if (!num) return "-";
    return String(num).replace(
        /^(\d{2}).+(\d{4})$/,
        (_, p1, p2) => `${p1}xxxxxxxx${p2}`
    );
}
function formatWaktu(ts) {
    if (!ts) return "—";
    try {
        const d = new Date(ts);
        if (Number.isNaN(d.getTime())) return ts;
        const pad = (n) => String(n).padStart(2, "0");
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(
            d.getDate()
        )} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    } catch {
        return ts;
    }
}

const paged = computed(() => ({
    page: page.value,
    per_page: perPage.value,
    search: q.value || "",
}));

const endpoint = "/api/tamu";

axios.defaults.withCredentials = true;
axios.defaults.xsrfCookieName = "XSRF-TOKEN";
axios.defaults.xsrfHeaderName = "X-XSRF-TOKEN";

async function fetchRows() {
    ui.loading = true;
    ui.error = "";
    try {
        const { data } = await axios.get(endpoint, { params: paged.value });
        rows.value = data?.data ?? [];
        total.value = data?.meta?.total ?? rows.value.length;
        page.value = data?.meta?.current_page ?? page.value;
        perPage.value = data?.meta?.per_page ?? perPage.value;
    } catch (err) {
        ui.error = err?.response?.data?.message || "Gagal memuat data tamu.";
    } finally {
        ui.loading = false;
    }
}

let t = null;
watch(q, () => {
    clearTimeout(t);
    t = setTimeout(() => {
        page.value = 1;
        fetchRows();
    }, 350);
});
watch([page, perPage], fetchRows);

onMounted(() => {
    fetchRows().catch(() => {});
    document.addEventListener("click", handleDocClick);
});
onBeforeUnmount(() => {
    document.removeEventListener("click", handleDocClick);
});

function onAdd() {
    if (!canManage.value) return;
    window.location.href = "/tamu/create";
}
function onEdit(row) {
    if (!canManage.value) return;
    window.location.href = `/tamu/${row.id}/edit`;
}
async function onDelete(row) {
    if (!canManage.value) return;
    const ok = window.confirm(`Hapus tamu "${row.nama}"?`);
    if (!ok) return;
    ui.loading = true;
    try {
        await axios.delete(`${endpoint}/${row.id}`);
        await fetchRows();
    } catch (e) {
        ui.error = e?.response?.data?.message || "Gagal menghapus tamu.";
    } finally {
        ui.loading = false;
    }
}

function toggleExportMenu() {
    exportMenuOpen.value = !exportMenuOpen.value;
}
function onExport(type) {
    const base = "/api/tamu/export";
    const url =
        type === "pdf"
            ? `${base}/pdf?search=${encodeURIComponent(q.value || "")}`
            : `${base}/csv?search=${encodeURIComponent(q.value || "")}`;
    window.open(url, "_blank");
    exportMenuOpen.value = false;
}
function handleDocClick(e) {
    if (!exportMenuRef.value) return;
    if (!exportMenuRef.value.contains(e.target)) exportMenuOpen.value = false;
}

const lastPage = computed(() =>
    Math.max(1, Math.ceil(total.value / perPage.value))
);
function goto(p) {
    const target = Math.min(Math.max(1, p), lastPage.value);
    if (target !== page.value) {
        page.value = target;
        fetchRows();
    }
}
</script>

<template>
    <div class="min-h-full">
        <div
            class="rounded-3xl bg-gradient-to-br from-sky-50 to-rose-50 p-6 ring-1 ring-slate-100"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <h1 class="text-2xl font-semibold text-slate-800">
                    Daftar Tamu
                </h1>

                <div class="flex items-center gap-3">
                    <label class="relative block w-[560px] max-w-[70vw]">
                        <input
                            v-model="q"
                            type="text"
                            placeholder="Cari nama, instansi, dll"
                            class="w-full rounded-full border border-slate-200 bg-white/80 px-10 py-3 shadow-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-blue-400"
                        />
                        <svg
                            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                        >
                            <circle
                                cx="11"
                                cy="11"
                                r="7"
                                stroke-width="2"
                            ></circle>
                            <line
                                x1="21"
                                y1="21"
                                x2="16.65"
                                y2="16.65"
                                stroke-width="2"
                            ></line>
                        </svg>
                    </label>

                    <button
                        :disabled="!canManage"
                        @click="onAdd"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white shadow-sm disabled:opacity-50"
                        title="Tambah data tamu"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                        >
                            <line
                                x1="12"
                                y1="5"
                                x2="12"
                                y2="19"
                                stroke-width="2"
                            />
                            <line
                                x1="5"
                                y1="12"
                                x2="19"
                                y2="12"
                                stroke-width="2"
                            />
                        </svg>
                        Tambah
                    </button>

                    <!-- Unduh: PDF / CSV -->
                    <div class="relative" ref="exportMenuRef">
                        <button
                            @click.stop="toggleExportMenu"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 shadow-sm hover:bg-slate-50"
                            title="Unduh Daftar Tamu"
                            aria-haspopup="menu"
                            :aria-expanded="exportMenuOpen"
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
                            Unduh Daftar Tamu
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.17l3.71-2.94a.75.75 0 11.94 1.16l-4.24 3.36a.75.75 0 01-.94 0L5.25 8.39a.75.75 0 01-.02-1.18z"
                                />
                            </svg>
                        </button>

                        <div
                            v-show="exportMenuOpen"
                            class="absolute right-0 z-20 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                            role="menu"
                        >
                            <button
                                @click.stop="onExport('pdf')"
                                class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                role="menuitem"
                            >
                                Ekspor sebagai PDF
                            </button>
                            <button
                                @click.stop="onExport('csv')"
                                class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                role="menuitem"
                            >
                                Ekspor sebagai CSV (Excel)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 h-px w-full bg-slate-200/70"></div>
        </div>

        <div
            class="mt-6 overflow-hidden rounded-3xl bg-white ring-1 ring-slate-100 shadow-sm"
        >
            <div v-if="ui.loading" class="w-full">
                <div class="h-1 w-full overflow-hidden bg-slate-100">
                    <div
                        class="h-1 w-1/3 animate-[loading_1.2s_ease-in-out_infinite] bg-blue-400"
                    ></div>
                </div>
            </div>

            <div v-if="ui.error" class="p-6 text-rose-600">{{ ui.error }}</div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="bg-slate-50 text-sm text-slate-600">
                        <tr class="border-b border-slate-100">
                            <th class="px-6 py-4 font-medium">Nama</th>
                            <th class="px-6 py-4 font-medium">No. Whatsapp</th>
                            <th class="px-6 py-4 font-medium">Email</th>
                            <th class="px-6 py-4 font-medium">Asal Instansi</th>
                            <th class="px-6 py-4 font-medium">Jenis Kelamin</th>
                            <th class="px-6 py-4 font-medium">
                                Waktu Kunjungan
                            </th>
                            <th class="px-6 py-4 text-right font-medium">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <tr v-if="!ui.loading && rows.length === 0">
                            <td
                                colspan="7"
                                class="px-6 py-10 text-center text-slate-500"
                            >
                                Tidak ada data tamu.
                            </td>
                        </tr>

                        <tr
                            v-for="row in rows"
                            :key="row.id"
                            class="hover:bg-slate-50/40"
                        >
                            <td class="px-6 py-4 text-slate-800">
                                {{ row.nama }}
                            </td>
                            <td class="px-6 py-4 tabular-nums text-slate-700">
                                {{ maskWhatsapp(row.no_hp) }}
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ row.email || "—" }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 bg-slate-50 text-slate-700 ring-slate-100"
                                >
                                    {{ row.asal_instansi || "—" }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ row.jenis_kelamin || "—" }}
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ formatWaktu(row.waktu_kunjungan) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button
                                        :disabled="!canManage"
                                        @click="onEdit(row)"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-40"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        :disabled="!canManage"
                                        @click="onDelete(row)"
                                        class="rounded-lg bg-rose-500 px-3 py-1.5 text-sm text-white shadow-sm hover:bg-rose-600 disabled:opacity-40"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t border-slate-100 p-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="text-sm text-slate-500">
                    Menampilkan
                    <select
                        v-model.number="perPage"
                        class="mx-1 rounded-md border border-slate-200 bg-white px-2 py-1 text-sm"
                    >
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                    </select>
                    dari
                    <span class="font-medium text-slate-700">{{ total }}</span>
                    tamu
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="goto(page - 1)"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                    >
                        Sebelumnya
                    </button>
                    <span class="text-sm text-slate-600"
                        >Hal.
                        <span class="font-medium text-slate-800">{{
                            page
                        }}</span>
                        / {{ lastPage }}</span
                    >
                    <button
                        @click="goto(page + 1)"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                    >
                        Berikutnya
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes loading {
    0% {
        transform: translateX(-100%);
    }
    50% {
        transform: translateX(50%);
    }
    100% {
        transform: translateX(200%);
    }
}
</style>
