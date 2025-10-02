<template>
    <header
        class="h-16 px-6 flex items-center justify-between border-b bg-white shadow-sm"
    >
        <div class="flex items-center space-x-2 w-full max-w-md relative">
            <input
                v-model="query"
                type="text"
                placeholder="Cari sesuatu..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring focus:border-blue-400"
                @keydown.enter="handleSearch"
            />
            <i
                class="lucide lucide-search absolute right-3 top-2.5 text-gray-500 w-4 h-4"
            ></i>
        </div>

        <div class="relative" @click="toggleDropdown">
            <div class="flex items-center space-x-3 cursor-pointer select-none">
                <span class="text-sm font-medium text-gray-700">{{
                    // ✅ Akses langsung dari store auth.user
                    auth.user?.name || "Pengguna"
                }}</span>
                <img
                    :src="auth.user?.foto || '/images/default-avatar.png'"
                    alt="User Avatar"
                    class="w-10 h-10 rounded-full object-cover border"
                />
            </div>

            <div
                v-if="showDropdown"
                class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg z-50"
            >
                <button
                    @click="logout"
                    class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                >
                    Logout
                </button>
            </div>
        </div>
    </header>
</template>

<script setup>
import { useAuthStore } from "@/stores/auth";
import { ref } from "vue";
import { useRouter } from "vue-router";
// import axios from "axios"; // Hapus import axios jika hanya digunakan untuk logout

const router = useRouter();
const auth = useAuthStore();
// const user = auth.user; // Hapus baris ini
const query = ref("");
const showDropdown = ref(false);

const handleSearch = () => {
    console.log("Search:", query.value);
    // optionally: router.push(`/search?q=${query.value}`)
};

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const logout = async () => {
    try {
        // ✅ Panggil aksi logout dari store, yang sudah diatur untuk memanggil API
        await auth.logout();
        router.push("/login");
    } catch (err) {
        console.error("Logout gagal:", err);
    }
};
</script>

<style scoped></style>
