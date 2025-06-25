import { defineStore } from 'pinia';
import axios from 'axios';
import Cookies from 'js-cookie';
import { jwtDecode } from 'jwt-decode';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: Cookies.get('jwt_token') || null,
    user: null, // 儲存用戶資訊
  }),
  getters: {
    isLoggedIn: (state) => state.token !== null,
  },
  actions: {
    /**
     * 處理用戶登入。
     * @param {string} email - 用戶電子郵件
     * @param {string} password - 用戶密碼
     * @returns {Promise<object>} 包含 token 的響應數據
     * @throws {Error} 如果登入失敗
     */
    async login(email, password) {
      try {
        const response = await axios.post('/auth/login', { email, password });
        const token = response.data.token;
        this.setToken(token);
        await this.fetchUser(); // 登入後獲取用戶資訊
        return response.data;
      } catch (error) {
        this.clearAuth();
        const errorMessage = error.response?.data?.error || '登入失敗，請檢查您的憑證。';
        throw new Error(errorMessage);
      }
    },
    /**
     * 處理用戶註冊。
     * @param {string} name - 用戶名稱
     * @param {string} email - 用戶電子郵件
     * @param {string} password - 用戶密碼
     * @param {string} password_confirmation - 確認密碼
     * @returns {Promise<object>} 包含註冊結果的響應數據
     * @throws {Error} 如果註冊失敗
     */
    async register(name, email, password, password_confirmation) {
      try {
        const response = await axios.post('/auth/register', { name, email, password, password_confirmation });
        // 註冊成功後不直接登入，引導用戶去登入頁面
        return response.data;
      } catch (error) {
        const errorMessage = error.response?.data?.message || '註冊失敗。';
        throw new Error(errorMessage);
      }
    },
    /**
     * 處理用戶登出。
     * @returns {Promise<void>}
     */
    async logout() {
      try {
        // 通知後端登出，使其 JWT 失效（如果後端有此機制）
        if (this.token) {
          await axios.post('/auth/logout');
        }
      } catch (error) {
        console.error('後端登出失敗:', error);
        // 即使後端登出失敗，也清除本地狀態
      } finally {
        this.clearAuth();
      }
    },
    /**
     * 設定 JWT token 並儲存到 Cookie。
     * @param {string} token - JWT token
     */
    setToken(token) {
      this.token = token;
      Cookies.set('jwt_token', token, { expires: 7 }); // token 存儲 7 天
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
    /**
     * 清除所有認證相關的本地狀態和 Cookie。
     */
    clearAuth() {
      this.token = null;
      this.user = null;
      Cookies.remove('jwt_token');
      delete axios.defaults.headers.common['Authorization'];
    },
    /**
     * 從後端獲取當前用戶資訊。
     * @returns {Promise<void>}
     * @throws {Error} 如果無法獲取用戶資訊
     */
    async fetchUser() {
      if (this.token) {
        try {
          const response = await axios.post('/auth/me');
          this.user = response.data;
        } catch (error) {
          console.error('無法獲取用戶資訊:', error);
          this.clearAuth(); // 獲取失敗則清除認證資訊
          throw new Error('無法獲取用戶資訊，請重新登入。');
        }
      }
    },
    /**
     * 應用程式初始化時檢查是否有 token 並嘗試獲取用戶資訊。
     */
    initialize() {
      if (this.token) {
        this.fetchUser().catch(() => {}); // 靜默處理錯誤，防止應用程式啟動時崩潰
      }
    }
  },
});
