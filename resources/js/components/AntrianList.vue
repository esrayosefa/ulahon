<template>
    <div
        class="bg-white rounded-xl shadow p-4 h-[300px] lg:h-[360px] flex flex-col"
    >
        <h2
            class="text-lg font-bold text-gray-700 mb-3 sticky top-0 bg-white/90 backdrop-blur z-10"
        >
            Daftar Antrian Layanan
        </h2>

        <!-- wadah list bisa scroll, halaman tetap pendek -->
        <ul class="space-y-2 overflow-y-auto min-h-0 flex-1 pr-1">
            <li
                v-for="(item, index) in antrian"
                :key="index"
                class="flex items-center justify-between border-b last:border-none pb-2"
            >
                <span class="text-gray-600 truncate">{{ item.nama }}</span>
                <span class="text-blue-700 font-bold text-sm"
                    >#{{ item.nomor }}</span
                >
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
defineProps<{ antrian: Array<any> }>();
</script>
