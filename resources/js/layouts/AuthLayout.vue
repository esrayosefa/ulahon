<template>
    <div class="flex min-h-screen bg-gray-50">
        <Sidebar />

        <div class="flex-1 flex flex-col">
            <Topbar />

            <main class="flex-1 p-4">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from "@/components/Sidebar.vue";
import Topbar from "@/components/Topbar.vue";
</script>
