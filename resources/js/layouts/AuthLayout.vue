<script setup>
import Sidebar from "@/components/Sidebar.vue";
import Topbar from "@/components/Topbar.vue";
</script>

<template>
    <div class="min-h-screen w-full flex bg-slate-50">
        <!-- Sidebar (sekali) -->
        <aside class="shrink-0">
            <Sidebar />
        </aside>

        <!-- Kolom kanan -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="border-b bg-white">
                <Topbar />
            </header>

            <!-- Konten halaman -->
            <main class="flex-1 min-w-0">
                <router-view />
            </main>
        </div>
    </div>
</template>
