import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

import GuestLayout from "@/layouts/GuestLayout.vue";
import AuthLayout from "@/layouts/AuthLayout.vue";

import LoginPage from "@/pages/LoginPage.vue";
import DashboardPage from "@/pages/DashboardPage.vue";

const routes = [
    {
        path: "/",
        redirect: "/dashboard",
        component: AuthLayout,
        children: [
            { path: "/dashboard", name: "dashboard", component: DashboardPage },
        ],
    },
    {
        path: "/login",
        component: GuestLayout,
        children: [{ path: "", name: "login", component: LoginPage }],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const auth = useAuthStore();

    const isGuestRoute = to.path === "/login";

    if (!auth.user && !isGuestRoute) return next("/login");
    if (auth.user && isGuestRoute) return next("/dashboard");

    next();
});

export default router;
