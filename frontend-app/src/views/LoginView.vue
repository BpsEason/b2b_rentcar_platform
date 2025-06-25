<template>
  <div class="auth-container">
    <h1>登入</h1>
    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <label for="email">電子郵件:</label>
        <input type="email" id="email" v-model="email" required>
      </div>
      <div class="form-group">
        <label for="password">密碼:</label>
        <input type="password" id="password" v-model="password" required>
      </div>
      <button type="submit" :disabled="loading">
        {{ loading ? '登入中...' : '登入' }}
      </button>
      <p v-if="error" class="error-message">{{ error }}</p>
    </form>
    <p class="switch-auth">還沒有帳號嗎？ <router-link to="/register">立即註冊</router-link></p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store/auth';

const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref(null);
const router = useRouter();
const authStore = useAuthStore();

const handleLogin = async () => {
  loading.value = true;
  error.value = null;
  try {
    await authStore.login(email.value, password.value);
    router.push('/bookings'); // 登入成功後跳轉到訂單頁面
  } catch (err) {
    error.value = err.message || '登入失敗，請檢查您的憑證。';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-container {
  max-width: 400px;
  margin: 50px auto;
  padding: 30px;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  background-color: #fff;
}
.auth-container h1 {
  color: #333;
  margin-bottom: 25px;
}
.form-group {
  margin-bottom: 20px;
  text-align: left;
}
.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: bold;
  color: #555;
}
.form-group input[type="email"],
.form-group input[type="password"] {
  width: calc(100% - 20px);
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 1em;
}
.auth-container button {
  width: 100%;
  padding: 12px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 1.1em;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
.auth-container button:hover {
  background-color: #0056b3;
}
.auth-container button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}
.error-message {
  color: #dc3545;
  margin-top: 15px;
  font-weight: bold;
}
.switch-auth {
  margin-top: 20px;
  font-size: 0.95em;
  color: #666;
}
.switch-auth a {
  color: #007bff;
  text-decoration: none;
}
.switch-auth a:hover {
  text-decoration: underline;
}
</style>
