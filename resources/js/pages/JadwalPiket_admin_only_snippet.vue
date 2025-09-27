<!-- Di JadwalPiketPetugasPage.vue -->
<script setup>
import { computed } from 'vue'
import { useAuth } from '@/stores/auth'
const auth = useAuth()
const isAdmin = computed(() => auth.user?.role === 'admin')
</script>

<template>
  <section class="container py-10 space-y-6">
    <h2 class="text-2xl font-semibold">Jadwal Piket Petugas</h2>
    <div class="card p-6">
      <!-- tabel/kalender jadwal di sini -->
      <div class="mt-4 flex gap-3" v-if="isAdmin">
        <button class="btn-accent">ACC Jadwal</button>
        <button class="btn-primary">Tukar Jadwal</button>
      </div>
    </div>
  </section>
</template>
