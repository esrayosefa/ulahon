<template>
    <router-link
        :to="to"
        class="flex items-center space-x-3 hover:text-blue-700 transition"
        :class="{ 'font-bold text-blue-900': isActive }"
    >
        <img
            :src="`/icons/${icon}-${isActive ? 'active' : 'inactive'}.png`"
            class="w-6 h-6"
            alt="icon"
        />
        <span v-if="!collapsed">{{ label }}</span>
    </router-link>
</template>

<script setup>
import { computed, inject } from "vue";
import { useRoute } from "vue-router";

const props = defineProps({
    icon: String,
    label: String,
    to: String,
});

const route = useRoute();
const isActive = computed(() => route.path === props.to);
const collapsed = inject("sidebarCollapsed", false);
</script>
