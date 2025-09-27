<template>
    <aside
        :class="[
            'min-h-screen bg-gradient-to-b from-[#FBD0A2] to-[#FBD0A2]/80 p-4 rounded-tr-[2.5rem] transition-all duration-300',
            collapsed ? 'w-20' : 'w-64',
        ]"
    >
        <div class="flex flex-col space-y-6">
            <button @click="toggleMenu" class="self-start text-blue-900">
                <img
                    :src="`/icons/hamburger-${
                        collapsed ? 'active' : 'inactive'
                    }.png`"
                    class="w-6 h-6"
                    alt="Toggle Sidebar"
                />
            </button>

            <nav
                class="flex flex-col space-y-4 text-blue-900 font-semibold text-[15px]"
            >
                <SidebarItem
                    icon="dashboard"
                    label="Dashboard"
                    to="/dashboard"
                />
                <SidebarItem icon="tamu" label="Daftar Tamu" to="/tamu" />
                <SidebarItem
                    icon="kunjungan"
                    label="Kunjungan"
                    to="/kunjungan"
                />
                <SidebarItem
                    icon="layanan"
                    label="Pelaporan Layanan"
                    to="/layanan"
                />
                <SidebarItem
                    icon="piket"
                    label="Jadwal Piket Petugas"
                    to="/piket"
                />
                <SidebarItem
                    icon="laporan"
                    label="Laporan Piket"
                    to="/laporan"
                />
                <SidebarItem
                    icon="rating"
                    label="Rating Petugas"
                    to="/rating"
                />
            </nav>
        </div>
    </aside>
</template>

<script setup>
import { ref, provide } from "vue";
import SidebarItem from "./SidebarItem.vue";

const collapsed = ref(false);
const toggleMenu = () => {
    collapsed.value = !collapsed.value;
};

provide("sidebarCollapsed", collapsed);
</script>

<style scoped></style>
