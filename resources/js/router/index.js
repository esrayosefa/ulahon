import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

import GuestLayout from "@/layouts/GuestLayout.vue";
import AuthLayout from "@/layouts/AuthLayout.vue";

import LoginPage from "@/pages/LoginPage.vue";
import DashboardPage from "@/pages/DashboardPage.vue";
import DaftarTamuPage from "@/pages/DaftarTamuPage.vue";

const routes = [
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
                meta: { roles: ["viewer", "user", "admin"] }, // semua role boleh lihat
            },
        ],
    },

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

    // fallback
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
