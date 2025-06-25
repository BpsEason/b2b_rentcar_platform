import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
import axios from 'axios'
import Cookies from 'js-cookie'
import { useAuthStore } from './store/auth' # 引入 authStore

// 添加變數檢查和警告 (根據優化建議)
if (!import.meta.env.VITE_APP_LARAVEL_API_URL) {
    console.warn('VITE_APP_LARAVEL_API_URL 未配置，將使用預設值 http://localhost:8000/api');
}
if (!import.meta.env.VITE_APP_FASTAPI_API_URL) {
    console.warn('VITE_APP_FASTAPI_API_URL 未配置，將使用預設值 http://localhost:8001/api');
}

const app = createApp(App)
const pinia = createPinia()

app.use(router)
app.use(pinia)

// 將 Pinia store 註冊到應用程式中
const authStore = useAuthStore()

// 配置 Axios 基礎 URL
// Laravel Mix 或 Vite 會自動替換 VITE_APP_LARAVEL_API_URL
axios.defaults.baseURL = import.meta.env.VITE_APP_LARAVEL_API_URL || 'http://localhost:8000/api';

// Axios 請求攔截器，添加 JWT token
axios.interceptors.request.use(config => {
    const token = Cookies.get('jwt_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, error => {
    return Promise.reject(error);
});

// Axios 響應攔截器，處理 401 錯誤
axios.interceptors.response.use(response => {
    return response;
}, error => {
    if (error.response && error.response.status === 401) {
        // Token 過期或無效，執行登出操作
        authStore.logout(); // 使用 Pinia store 的 logout action
        router.push('/login');
    }
    return Promise.reject(error);
});

// 初始化認證狀態 (如果頁面重新載入，從 cookie 恢復狀態)
authStore.initialize();

app.mount('#app');
