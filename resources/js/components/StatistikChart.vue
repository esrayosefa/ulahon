<template>
    <div
        class="bg-white rounded-xl shadow p-4 h-[300px] lg:h-[360px] flex flex-col"
    >
        <h3 class="font-semibold text-gray-800 text-base mb-3">
            {{ title }}
        </h3>

        <div class="min-h-0 flex-1">
            <component
                :is="chartComponent"
                :labels="labels"
                :data="data"
                class="h-full"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import LineChart from "../components/charts/LineChart.vue";
import BarChart from "../components/charts/BarChart.vue";

const props = defineProps<{
    title: string;
    labels: (string | number)[];
    data: number[];
    type?: "line" | "bar";
}>();

const chartComponent = computed(() =>
    props.type === "bar" ? BarChart : LineChart
);
</script>
