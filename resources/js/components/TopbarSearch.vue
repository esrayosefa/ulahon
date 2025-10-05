<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const q = ref("");
const inputEl = ref(null);

// cek apakah route "search" tersedia (untuk global result page)
const hasSearchRoute = router.getRoutes().some((r) => r.name === "search");

function go() {
    const term = q.value.trim();
    if (!term) return;

    if (hasSearchRoute) {
        router.push({ name: "search", query: { q: term, scope: "all" } });
    } else {
        // fallback aman: arahkan ke daftar tamu dengan filter lokal
        router.push({ name: "daftar-tamu", query: { search: term } });
    }
}

// shortcut "/" untuk fokus ke search
function handleKeydown(e) {
    const tag = document.activeElement?.tagName;
    if (tag === "INPUT" || tag === "TEXTAREA") return;
    if (e.key === "/") {
        e.preventDefault();
        inputEl.value?.focus();
    }
}

onMounted(() => window.addEventListener("keydown", handleKeydown));
onBeforeUnmount(() => window.removeEventListener("keydown", handleKeydown));
</script>

<template>
    <form @submit.prevent="go" class="relative block w-full max-w-3xl">
        <input
            ref="inputEl"
            v-model="q"
            type="search"
            placeholder="Cari tamu, kunjungan, layanan… (tekan / untuk fokus)"
            class="w-full rounded-full border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-slate-700 outline-none ring-1 ring-transparent focus:ring-2 focus:ring-blue-400"
        />
        <button type="submit" class="hidden">Go</button>
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
    </form>
</template>
