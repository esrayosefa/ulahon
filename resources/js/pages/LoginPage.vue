<template>
    <div class="min-h-screen flex flex-col sm:flex-row">
        <!-- Kiri: Form login -->
        <div
            class="flex flex-col justify-center items-center sm:w-1/2 w-full px-8 py-10 bg-gradient-to-b from-blue-100 to-orange-200"
        >
            <img :src="logoUrl" alt="Logo Ulahon" class="w-24 mb-4" />

            <h2 class="text-lg font-semibold mb-1">Selamat pagi :)</h2>
            <p class="text-sm text-gray-600 mb-6">
                Silakan masuk terlebih dahulu
            </p>

            <form @submit.prevent="submit" class="w-full max-w-sm space-y-4">
                <input
                    v-model="form.username"
                    type="text"
                    placeholder="Username"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                />
                <input
                    v-model="form.password"
                    type="password"
                    placeholder="Password"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                />
                <button
                    type="submit"
                    class="w-full bg-blue-800 text-white py-2 rounded hover:bg-blue-900 transition"
                >
                    M a s u k
                </button>
            </form>

            <p v-if="auth.error" class="text-red-600 text-sm mt-4">
                {{ auth.error }}
            </p>
        </div>

        <!-- Kanan: Gambar -->
        <div class="hidden sm:block sm:w-1/2">
            <img
                :src="bgUrl"
                alt="Layanan Statistik Terpadu"
                class="w-full h-full object-cover rounded-l-[3rem]"
            />
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

// ✅ URL gambar agar Vite tidak error saat build
const logoUrl = new URL("/images/ulahon-logo.png", import.meta.url).href;
const bgUrl = new URL("/images/bps-login.png", import.meta.url).href;

const router = useRouter();
const auth = useAuthStore();

const form = ref({
    username: "",
    password: "",
});

const submit = async () => {
    const success = await auth.login(form.value);
    if (success) {
        router.push("/dashboard");
    }
};
</script>
