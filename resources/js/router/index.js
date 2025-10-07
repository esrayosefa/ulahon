import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

import GuestLayout from "@/layouts/GuestLayout.vue";
import AuthLayout from "@/layouts/AuthLayout.vue";

import LoginPage from "@/pages/LoginPage.vue";
import DashboardPage from "@/pages/DashboardPage.vue";
import DaftarTamuPage from "@/pages/DaftarTamuPage.vue";

const routes = [
    // ===== Protected area =====
    {
        path: "/",
        component: AuthLayout,
        meta: { requiresAuth: true },
        children: [
            { path: "", redirect: { name: "dashboard" } },

            {
                path: "dashboard",
                name: "dashboard",
                component: DashboardPage,
                meta: { roles: ["viewer", "user", "admin"] },
            },
            {
                path: "daftar-tamu",
                name: "daftar-tamu",
                component: DaftarTamuPage,
                meta: { roles: ["viewer", "user", "admin"] },
            },
            {
                path: "kunjungan", // ⬅️ no leading slash
                name: "kunjungan",
                component: () => import("@/pages/KunjunganPage.vue"),
                meta: {
                    requiresAuth: true,
                    roles: ["viewer", "user", "admin"],
                },
            },
            {
                path: "search", // ⬅️ no leading slash
                name: "search",
                component: () => import("@/pages/SearchResultsPage.vue"), // ⬅️ samakan dengan nama file kamu
                meta: {
                    requiresAuth: true,
                    roles: ["viewer", "user", "admin"],
                },
            },

            {
                path: "layanan",
                name: "layanan",
                component: () => import("@/pages/PelaporanLayananPage.vue"),
                meta: {
                    requiresAuth: true,
                    roles: ["viewer", "user", "admin"],
                },
            },

            {
                path: "piket",
                name: "piket",
                component: () => import("@/pages/JadwalPiketPage.vue"),
                meta: {
                    requiresAuth: true,
                    roles: ["viewer", "user", "admin"],
                },
            },

            {
                path: "laporan",
                name: "laporaj",
                component: () => import("@/pages/LaporanPiketPage.vue"),
                meta: { requiresAuth: true, roles: ["admin", "user"] },
            },

            {
                path: "rating",
                name: "rating",
                component: () => import("@/pages/RatingPetugasPage.vue"),
                meta: {
                    requiresAuth: true,
                    roles: ["admin", "user", "viewer"],
                },
            },
        ],
    },

    // ===== Public area =====
    {
        path: "/login",
        component: GuestLayout,
        children: [
            {
                path: "",
                name: "login",
                component: LoginPage,
                meta: { guestOnly: true },
            },
        ],
    },

    // ===== Fallback =====
    { path: "/:pathMatch(.*)*", redirect: "/" },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const auth = useAuthStore();

    const requiresAuth = to.matched.some((r) => r.meta?.requiresAuth);
    const guestOnly = to.matched.some((r) => r.meta?.guestOnly);

    if (requiresAuth && !auth.user) return next({ name: "login" });
    if (guestOnly && auth.user) return next({ name: "dashboard" });

    if (
        to.meta?.roles &&
        auth.user &&
        !to.meta.roles.includes(auth.user.role)
    ) {
        return next({ name: "dashboard" });
    }

    next();
});

export default router;
