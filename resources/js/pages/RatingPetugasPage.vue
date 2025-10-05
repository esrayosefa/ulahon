<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const search = ref("");
const loading = ref(false);
const items = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 12 });

async function fetchRatings(page = 1) {
    loading.value = true;
    try {
        const { data } = await axios.get("/api/feedback/ratings/summary", {
            params: {
                page,
                per_page: meta.value.per_page,
                search: search.value,
            },
        });
        items.value = data.data ?? [];
        meta.value = {
            current_page: data.current_page ?? 1,
            last_page: data.last_page ?? 1,
            total: data.total ?? items.value.length,
            per_page: data.per_page ?? 12,
        };
    } finally {
        loading.value = false;
    }
}

let t = null;
function onSearch() {
    clearTimeout(t);
    t = setTimeout(() => fetchRatings(1), 350);
}

function fotoOf(p) {
    return p?.foto || "/images/default-avatar.png";
}

function hpOf(p) {
    return p?.hp || "-";
}
onMounted(() => fetchRatings(1));
</script>

<template>
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-slate-800">
                Rating Petugas
            </h1>
            <div class="w-full max-w-xl">
                <input
                    v-model="search"
                    @input="onSearch"
                    type="text"
                    placeholder="Cari nama petugas…"
                    class="w-full rounded-full border border-slate-200 bg-white py-2.5 px-4 text-slate-700 outline-none focus:ring-2 focus:ring-blue-400"
                />
            </div>
        </div>

        <!-- GRID -->
        <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
        >
            <div
                v-for="it in items"
                :key="it.petugas_id"
                class="rounded-2xl bg-white shadow p-6"
            >
                <div class="flex flex-col items-center text-center">
                    <img
                        :src="fotoOf(it.petugas)"
                        class="h-20 w-20 rounded-full object-cover"
                        alt=""
                    />

                    <div class="mt-3 text-base font-semibold text-slate-800">
                        {{ it.petugas?.name || "-" }}
                    </div>

                    <a
                        v-if="hpOf(it.petugas) !== '-'"
                        class="text-sm text-violet-600 hover:underline"
                        :href="`tel:${hpOf(it.petugas)}`"
                    >
                        {{ hpOf(it.petugas) }}
                    </a>

                    <div
                        v-if="it.komentar"
                        class="mt-3 text-sm text-slate-600 line-clamp-3"
                    >
                        {{ it.komentar }}
                    </div>

                    <div class="mt-4 text-sm font-semibold text-slate-800">
                        {{ (it.avg_nilai ?? 0).toFixed(1) }}/5
                        <span class="text-slate-500 font-normal"
                            >({{ it.total_ulasan }} ulasan)</span
                        >
                    </div>
                </div>
            </div>

            <div
                v-if="!loading && items.length === 0"
                class="col-span-full text-center text-slate-500"
            >
                Tidak ada data.
            </div>
        </div>

        <!-- PAGINATION -->
        <div class="flex items-center justify-between text-sm">
            <div class="text-slate-600">
                Halaman {{ meta.current_page }} dari {{ meta.last_page }} ·
                Total {{ meta.total }}
            </div>
            <div class="flex gap-2">
                <button
                    class="rounded-lg border px-3 py-1.5 disabled:opacity-50"
                    :disabled="meta.current_page <= 1"
                    @click="fetchRatings(meta.current_page - 1)"
                >
                    Sebelumnya
                </button>
                <button
                    class="rounded-lg border px-3 py-1.5 disabled:opacity-50"
                    :disabled="meta.current_page >= meta.last_page"
                    @click="fetchRatings(meta.current_page + 1)"
                >
                    Berikutnya
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* utility untuk clamp teks komentar */
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
