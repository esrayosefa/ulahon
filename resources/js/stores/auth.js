import { defineStore } from "pinia";
import axios from "axios";
axios.defaults.withCredentials = true;

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        error: null,
    }),

    actions: {
        async login(form) {
            this.error = null;
            try {
                await axios.get("/sanctum/csrf-cookie");
                const res = await axios.post("/login", form);
                this.user = res.data.user;
                return true;
            } catch (err) {
                this.error = err.response?.data?.message || "Login gagal.";
                return false;
            }
        },

        async logout() {
            await axios.post("/logout");
            this.user = null;
        },

        setUser(user) {
            this.user = user;
        },
    },
});
