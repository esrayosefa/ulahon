<template>
    <div
        class="flex items-center justify-between p-4 rounded-xl shadow bg-white w-full"
    >
        <!-- Info Petugas -->
        <div class="text-left">
            <p class="text-gray-700 font-semibold text-sm">{{ nama }}</p>
            <p class="text-gray-500 text-sm">{{ jam }}</p>
        </div>

        <!-- Foto Profil -->
        <img
            :src="foto || '/images/default-user.png'"
            alt="Foto Petugas"
            class="w-16 h-16 rounded-full object-cover border-2 border-blue-300"
            @error="fallback"
        />
    </div>
</template>

<script setup>
import { ref, watchEffect } from "vue";

const props = defineProps({
    nama: String,
    jam: String,
    foto: String,
});

const foto = ref(props.foto);

function fallback(e) {
    foto.value = "/images/default-user.png";
}
</script>

<style scoped></style>
