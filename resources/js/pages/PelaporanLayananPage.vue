<script setup>
import { ref, onMounted, watch, computed, onBeforeUnmount } from "vue";
import axios from "axios";
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();

/* =========================
   LIST VIEW (cards/grid)
========================= */
const list = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref("");
const loading = ref(false);
const perPage = 12;

async function fetchList(page = 1) {
    loading.value = true;
    try {
        const { data } = await axios.get("/api/layanan", {
            params: { page, per_page: perPage, search: search.value },
        });
        list.value = data.data ?? [];
        meta.value = {
            current_page: data.current_page ?? 1,
            last_page: data.last_page ?? 1,
            total: data.total ?? list.value.length,
        };
    } finally {
        loading.value = false;
    }
}

function gotoPage(p) {
    const target = Math.max(1, Math.min(p, meta.value.last_page));
    if (target !== meta.value.current_page) fetchList(target);
}

function formatTanggal(val) {
    if (!val) return "-";
    return new Date(val).toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    });
}

let t;
watch(search, () => {
    clearTimeout(t);
    t = setTimeout(() => fetchList(1), 400);
});
onMounted(() => fetchList(1));

/* =========================
   CREATE/EDIT MODAL + FORM
========================= */
// ⭐ Satu sumber kebenaran utk visibilitas modal
const showModal = ref(false);
// ⭐ Alias utk kompatibilitas bila ada template lama yg masih pakai showCreate
const showCreate = showModal;

const mode = ref("create"); // 'create' | 'edit'
const editingId = ref(null);

const submitting = ref(false);
const errors = ref({});

const jenisSuggestions = [
    "Konsultasi Statistik",
    "Akses Microdata",
    "Pembelian Produk Statistik",
    "Bimbingan Pojok Statistik",
    "Kunjungan Mitra",
];

const form = ref({
    kunjungan_id: "",
    petugas_id: auth?.user?.id || null,
    jenis_layanan: "",
    deskripsi: "",
    kebutuhan_data: "",
    foto_bukti: null, // File
});

// PREVIEW IMAGE
const fotoPreview = ref(null);
function onPickFile(e) {
    const file = e.target.files?.[0];
    form.value.foto_bukti = file || null;
    if (fotoPreview.value) URL.revokeObjectURL(fotoPreview.value);
    fotoPreview.value = file ? URL.createObjectURL(file) : null;
}
onBeforeUnmount(() => {
    if (fotoPreview.value) URL.revokeObjectURL(fotoPreview.value);
});

// KUNJUNGAN OPTIONS (untuk select)
const kunjunganOptions = ref([]);
const kunjunganSearch = ref("");
const loadingKunjungan = ref(false);

async function fetchKunjunganOptions() {
    loadingKunjungan.value = true;
    try {
        // prioritas hari ini
        const { data } = await axios.get("/api/kunjungan", {
            params: {
                page: 1,
                per_page: 50,
                view: "today",
                search: kunjunganSearch.value,
            },
        });
        const today = data.data ?? [];
        // ambil sedikit riwayat juga kalau hasil sedikit
        let history = [];
        if (today.length < 20) {
            const h = await axios.get("/api/kunjungan", {
                params: {
                    page: 1,
                    per_page: 30,
                    view: "history",
                    search: kunjunganSearch.value,
                },
            });
            history = h.data.data ?? [];
        }
        kunjunganOptions.value = [...today, ...history].slice(0, 50);
    } finally {
        loadingKunjungan.value = false;
    }
}

watch(kunjunganSearch, () => {
    clearTimeout(t);
    t = setTimeout(fetchKunjunganOptions, 250);
});

/* ===== open/close ===== */
function openCreate() {
    errors.value = {};
    mode.value = "create";
    editingId.value = null;
    showModal.value = true; // ⭐ pastikan flag ini yang di-set

    form.value = {
        kunjungan_id: "",
        petugas_id: auth?.user?.id || null,
        jenis_layanan: "",
        deskripsi: "",
        kebutuhan_data: "",
        foto_bukti: null,
    };
    if (fotoPreview.value) {
        URL.revokeObjectURL(fotoPreview.value);
    }
    fotoPreview.value = null;

    fetchKunjunganOptions();
}

function openEdit(item) {
    errors.value = {};
    mode.value = "edit";
    editingId.value = item.id;
    showModal.value = true; // ⭐

    form.value = {
        kunjungan_id: item.kunjungan_id,
        petugas_id: auth?.user?.id || item.petugas_id || null,
        jenis_layanan: item.jenis_layanan || "",
        deskripsi: item.deskripsi || "",
        kebutuhan_data: item.kebutuhan_data || "",
        foto_bukti: null, // hanya jika user pilih file baru
    };

    if (fotoPreview.value) {
        URL.revokeObjectURL(fotoPreview.value);
    }
    fotoPreview.value = item.foto_bukti_url || null;

    fetchKunjunganOptions();
}

function closeModal() {
    showModal.value = false;
}

/* ===== submit (create or edit) ===== */
async function submitForm() {
    errors.value = {};
    submitting.value = true;
    try {
        const fd = new FormData();
        fd.append("kunjungan_id", String(form.value.kunjungan_id || ""));
        fd.append("petugas_id", String(form.value.petugas_id || ""));
        fd.append("jenis_layanan", form.value.jenis_layanan || "");
        if (form.value.deskripsi) fd.append("deskripsi", form.value.deskripsi);
        if (form.value.kebutuhan_data)
            fd.append("kebutuhan_data", form.value.kebutuhan_data);
        if (form.value.foto_bukti)
            fd.append("foto_bukti", form.value.foto_bukti); // hanya terkirim kalau user pilih file

        if (mode.value === "create") {
            await axios.post("/api/layanan", fd, {
                headers: { "Content-Type": "multipart/form-data" },
            });
        } else {
            // pakai method spoofing agar aman di server
            await axios.post(
                `/api/layanan/${editingId.value}?_method=PUT`,
                fd,
                {
                    headers: { "Content-Type": "multipart/form-data" },
                }
            );
        }

        closeModal();
        fetchList(meta.value.current_page || 1);
    } catch (err) {
        const v = err?.response?.data?.errors;
        if (v) errors.value = v;
        else alert("Gagal menyimpan. Cek koneksi atau server.");
    } finally {
        submitting.value = false;
    }
}

const disableSubmit = computed(() => {
    const requiredFilled =
        !!form.value.kunjungan_id &&
        !!form.value.petugas_id &&
        !!form.value.jenis_layanan;
    return submitting.value || !requiredFilled;
});

/* ===== delete ===== */
async function removeItem(id) {
    if (!confirm("Hapus laporan layanan ini?")) return;
    await axios.delete(`/api/layanan/${id}`);
    // jika halaman kosong setelah hapus, mundurkan 1 halaman
    const afterDeleteCount = list.value.length - 1;
    if (afterDeleteCount <= 0 && meta.value.current_page > 1) {
        fetchList(meta.value.current_page - 1);
    } else {
        fetchList(meta.value.current_page);
    }
}

/* ===== esc untuk menutup modal ===== */
function onKeydown(e) {
    if (e.key === "Escape" && showModal.value) closeModal();
}
onMounted(() => window.addEventListener("keydown", onKeydown));
onBeforeUnmount(() => window.removeEventListener("keydown", onKeydown));
</script>

<template>
    <div class="min-h-full">
        <div class="rounded-3xl bg-gradient-to-b from-blue-50 to-orange-50">
            <div
                class="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between"
            >
                <h1 class="text-2xl font-semibold text-slate-800">
                    Pelaporan Layanan
                </h1>

                <div class="flex items-center gap-3">
                    <label class="relative block w-[560px] max-w-[70vw]">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari jenis layanan, deskripsi, kebutuhan data, nama tamu…"
                            class="w-full rounded-full border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-slate-700 outline-none ring-1 ring-transparent focus:ring-2 focus:ring-blue-400"
                        />
                        <svg
                            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"
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

                    <button
                        @click.prevent="openCreate"
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
                        Tambah Laporan Pelayanan
                    </button>
                </div>
            </div>

            <!-- GRID KARTU -->
            <div class="px-6">
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <!-- Loading skeleton -->
                    <template v-if="loading">
                        <div
                            v-for="i in 6"
                            :key="i"
                            class="animate-pulse rounded-2xl bg-white shadow"
                        >
                            <div
                                class="h-48 w-full rounded-t-2xl bg-slate-200"
                            ></div>
                            <div class="p-4 space-y-3">
                                <div
                                    class="h-4 w-24 bg-slate-200 rounded"
                                ></div>
                                <div
                                    class="h-5 w-3/4 bg-slate-200 rounded"
                                ></div>
                                <div
                                    class="h-16 w-full bg-slate-200 rounded"
                                ></div>
                                <div
                                    class="h-6 w-1/2 bg-slate-200 rounded"
                                ></div>
                            </div>
                        </div>
                    </template>

                    <!-- Data -->
                    <template v-else>
                        <div
                            v-if="list.length === 0"
                            class="col-span-full text-center text-slate-500 py-16"
                        >
                            Tidak ada laporan layanan.
                        </div>

                        <article
                            v-for="it in list"
                            :key="it.id"
                            class="rounded-2xl bg-white shadow hover:shadow-md transition"
                        >
                            <div
                                class="h-48 w-full rounded-t-2xl overflow-hidden bg-slate-100"
                            >
                                <img
                                    v-if="it.foto_bukti_url"
                                    :src="it.foto_bukti_url"
                                    alt="Foto bukti"
                                    class="h-48 w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="h-48 w-full flex items-center justify-center text-slate-400"
                                >
                                    Tidak ada foto
                                </div>
                            </div>

                            <div class="p-4">
                                <div
                                    class="text-xs font-medium text-violet-700 mb-1"
                                >
                                    {{
                                        it.kunjungan?.tamu?.asal_instansi
                                            ? "Kunjungan " +
                                              it.kunjungan.tamu.asal_instansi
                                            : "Kunjungan Mitra"
                                    }}
                                </div>

                                <div
                                    class="flex items-start justify-between gap-2"
                                >
                                    <h3
                                        class="text-lg font-semibold text-slate-900 leading-snug"
                                    >
                                        {{ it.jenis_layanan }}
                                    </h3>

                                    <!-- Actions kecil -->
                                    <div class="flex items-center gap-1">
                                        <button
                                            class="rounded-md px-2 py-1 text-xs bg-slate-100 hover:bg-slate-200"
                                            @click="openEdit(it)"
                                            title="Edit"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            class="rounded-md px-2 py-1 text-xs bg-red-500 text-white hover:bg-red-600"
                                            @click="removeItem(it.id)"
                                            title="Hapus"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>

                                <p
                                    class="mt-2 text-sm text-slate-600 line-clamp-3"
                                >
                                    {{ it.deskripsi ?? "—" }}
                                </p>

                                <div
                                    v-if="it.kebutuhan_data"
                                    class="mt-3 text-xs text-slate-500"
                                >
                                    <span class="font-medium text-slate-700"
                                        >Kebutuhan data:</span
                                    >
                                    <span>{{ it.kebutuhan_data }}</span>
                                </div>

                                <div class="mt-4 flex items-center gap-3">
                                    <img
                                        :src="
                                            it.petugas?.foto ||
                                            '/images/default-avatar.png'
                                        "
                                        class="h-8 w-8 rounded-full object-cover border"
                                        alt="Petugas"
                                    />
                                    <div class="text-sm">
                                        <div class="text-slate-700">
                                            {{
                                                it.petugas?.name ??
                                                "Nama Petugas"
                                            }}
                                        </div>
                                        <div class="text-slate-500 text-xs">
                                            {{ formatTanggal(it.created_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex items-center justify-between text-sm">
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

        <!-- =======================
         MODAL TAMBAH / EDIT
         ⭐ Teleport ke <body> agar tidak ketutup layout
    ======================== -->
        <teleport to="body">
            <transition name="fade">
                <div
                    v-if="showModal"
                    class="fixed inset-0 z-[100] flex items-center justify-center"
                >
                    <div
                        class="absolute inset-0 bg-slate-900/50"
                        @click="closeModal"
                    ></div>

                    <div
                        class="relative z-10 w-[92vw] max-w-3xl rounded-2xl bg-white shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b"
                        >
                            <h3 class="text-lg font-semibold text-slate-800">
                                {{
                                    mode === "create"
                                        ? "Tambah Laporan Pelayanan"
                                        : "Edit Laporan Pelayanan"
                                }}
                            </h3>
                            <button
                                class="rounded-md p-1.5 hover:bg-slate-100"
                                @click="closeModal"
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
                            class="px-6 py-5 space-y-4"
                            @submit.prevent="submitForm"
                        >
                            <!-- Kunjungan -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 mb-1"
                                    >Kunjungan</label
                                >
                                <div class="flex gap-2">
                                    <select
                                        v-model="form.kunjungan_id"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:border-blue-400"
                                        :disabled="mode === 'edit'"
                                    >
                                        <option value="" disabled>
                                            Pilih kunjungan
                                        </option>
                                        <option
                                            v-for="k in kunjunganOptions"
                                            :key="k.id"
                                            :value="k.id"
                                        >
                                            {{ k.tamu?.nama || "—" }} —
                                            {{
                                                k.tamu?.asal_instansi || "Umum"
                                            }}
                                            —
                                            {{
                                                new Date(
                                                    k.tanggal_kunjungan ||
                                                        k.created_at
                                                ).toLocaleDateString("id-ID", {
                                                    day: "2-digit",
                                                    month: "short",
                                                })
                                            }}
                                        </option>
                                    </select>
                                    <input
                                        v-if="mode === 'create'"
                                        v-model="kunjunganSearch"
                                        type="text"
                                        placeholder="Cari kunjungan…"
                                        class="w-56 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:border-blue-400"
                                    />
                                </div>
                                <p
                                    v-if="loadingKunjungan"
                                    class="text-xs text-slate-500 mt-1"
                                >
                                    Memuat pilihan…
                                </p>
                                <p
                                    v-if="errors.kunjungan_id"
                                    class="text-xs text-red-600 mt-1"
                                >
                                    {{ errors.kunjungan_id[0] }}
                                </p>
                            </div>

                            <!-- Jenis Layanan -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 mb-1"
                                    >Jenis Layanan</label
                                >
                                <input
                                    v-model="form.jenis_layanan"
                                    list="jenis-list"
                                    type="text"
                                    placeholder="Masukkan jenis layanan"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:border-blue-400"
                                />
                                <datalist id="jenis-list">
                                    <option
                                        v-for="j in jenisSuggestions"
                                        :key="j"
                                        :value="j"
                                    />
                                </datalist>
                                <p
                                    v-if="errors.jenis_layanan"
                                    class="text-xs text-red-600 mt-1"
                                >
                                    {{ errors.jenis_layanan[0] }}
                                </p>
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 mb-1"
                                    >Deskripsi</label
                                >
                                <textarea
                                    v-model="form.deskripsi"
                                    rows="3"
                                    placeholder="Ringkas kegiatan pelayanan…"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:border-blue-400"
                                ></textarea>
                                <p
                                    v-if="errors.deskripsi"
                                    class="text-xs text-red-600 mt-1"
                                >
                                    {{ errors.deskripsi[0] }}
                                </p>
                            </div>

                            <!-- Kebutuhan Data -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 mb-1"
                                    >Kebutuhan Data (opsional)</label
                                >
                                <textarea
                                    v-model="form.kebutuhan_data"
                                    rows="2"
                                    placeholder="Contoh: PDRB kab/kota, profil kemiskinan…"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:border-blue-400"
                                ></textarea>
                                <p
                                    v-if="errors.kebutuhan_data"
                                    class="text-xs text-red-600 mt-1"
                                >
                                    {{ errors.kebutuhan_data[0] }}
                                </p>
                            </div>

                            <!-- Upload Foto Bukti -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 mb-1"
                                    >Foto Bukti Layanan (opsional)</label
                                >
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="onPickFile"
                                    class="block w-full text-sm file:mr-3 file:rounded-md file:border file:border-slate-300 file:bg-white file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-slate-50"
                                />
                                <div
                                    v-if="fotoPreview"
                                    class="mt-2 h-36 w-64 overflow-hidden rounded-md border"
                                >
                                    <img
                                        :src="fotoPreview"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <p
                                    v-if="errors.foto_bukti"
                                    class="text-xs text-red-600 mt-1"
                                >
                                    {{ errors.foto_bukti[0] }}
                                </p>
                            </div>

                            <!-- Hidden petugas_id -->
                            <input type="hidden" :value="form.petugas_id" />

                            <!-- Actions -->
                            <div
                                class="flex items-center justify-end gap-3 pt-2"
                            >
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-50"
                                    @click="closeModal"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="disableSubmit"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60 hover:bg-blue-700"
                                >
                                    <span v-if="submitting">Menyimpan…</span>
                                    <span v-else>{{
                                        mode === "create" ? "Simpan" : "Update"
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
