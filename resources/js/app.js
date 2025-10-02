import { createApp } from "vue";
import router from "./router";
import { createPinia } from "pinia";
import App from "./App.vue";
import "../css/app.css";
import { useAuthStore } from "./stores/auth"; // ✅ Import store

const app = createApp(App);
const pinia = createPinia();

app.use(router);
app.use(pinia);

// ✅ PERBAIKAN: Panggil checkAuthStatus segera setelah Pinia dibuat
const authStore = useAuthStore();
authStore.checkAuthStatus();

app.mount("#app");
