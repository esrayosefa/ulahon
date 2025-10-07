<template>
    <!-- full viewport + anti scroll -->
    <div
        class="h-svh overflow-hidden grid grid-cols-1 lg:grid-cols-[1fr_auto] bg-gradient-to-b from-blue-100 to-orange-200"
    >
        <!-- KIRI: FORM — dipusatkan & lebih besar -->
        <section class="flex items-center justify-center px-6 md:px-10">
            <div class="w-full max-w-[640px]">
                <!-- << form lebih besar -->
                <div class="flex flex-col items-center">
                    <img :src="logoPath" alt="Logo Ulahon" class="w-28 mb-5" />
                    <h2 class="text-2xl font-semibold mb-1 text-slate-800">
                        Selamat pagi :)
                    </h2>
                    <p class="text-sm text-gray-600 mb-7">
                        Silakan masuk terlebih dahulu
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <input
                        v-model.trim="form.username"
                        type="text"
                        placeholder="Username"
                        autocomplete="username"
                        required
                        class="w-full h-12 px-4 text-base border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="Password"
                        autocomplete="current-password"
                        required
                        class="w-full h-12 px-4 text-base border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <button
                        type="submit"
                        class="w-full h-12 bg-blue-800 text-white rounded-xl hover:bg-blue-900 transition tracking-[0.35em] uppercase"
                    >
                        M a s u k
                    </button>
                </form>

                <p
                    v-if="auth.error"
                    class="text-red-600 text-sm mt-4 text-center"
                >
                    {{ auth.error }}
                </p>
            </div>
        </section>

        <!-- KANAN: FOTO — mepet kanan, ada jarak atas–bawah -->
        <section
            class="hidden lg:flex items-center justify-end py-6 md:py-8 lg:py-10 pr-0"
        >
            <!-- fotomu sudah rounded dari sumbernya -->
            <img
                :src="bgPath"
                alt="Layanan Statistik Terpadu"
                class="block h-auto w-[620px] max-w-[46vw] select-none"
            />
        </section>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const auth = useAuthStore();

const form = ref({ username: "", password: "" });

// pakai gambar dari public/images seperti kode lama
const logoPath = computed(() => "/images/ulahon-logo.png");
const bgPath = computed(() => "/images/bps-login.png");

const submit = async () => {
    const success = await auth.login(form.value);
    if (success) router.push("/dashboard");
};
</script>
