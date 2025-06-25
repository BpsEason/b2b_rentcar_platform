<template>
  <div class="auth-container">
    <h1>註冊</h1>
    <form @submit.prevent="handleRegister">
      <div class="form-group">
        <label for="name">姓名:</label>
        <input type="text" id="name" v-model="name" required>
      </div>
      <div class="form-group">
        <label for="email">電子郵件:</label>
        <input type="email" id="email" v-model="email" required>
      </div>
      <div class="form-group">
        <label for="password">密碼:</label>
        <input type="password" id="password" v-model="password" required>
      </div>
      <div class="form-group">
        <label for="password_confirmation">確認密碼:</label>
        <input type="password" id="password_confirmation" v-model="password_confirmation" required>
      </div>
      <button type="submit" :disabled="loading">
        {{ loading ? '註冊中...' : '註冊' }}
      </button>
      <p v-if="error" class="error-message">{{ error }}</p>
    </form>
    <p class="switch-auth">已經有帳號了？ <router-link to="/login">立即登入</router-link></p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store/auth';

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const loading = ref(false);
const error = ref(null);
const router = useRouter();
const authStore = useAuthStore();

const handleRegister = async () => {
  loading.value = true;
  error.value = null;
  try {
    await authStore.register(name.value, email.value, password.value, password_confirmation.value);
    router.push('/login'); // 註冊成功後跳轉到登入頁面
  } catch (err) {
    error.value = err.message || '註冊失敗，請稍後再試。';
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
.form-group input[type="text"],
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
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 1.1em;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
.auth-container button:hover {
  background-color: #218838;
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
  color: #28a745;
  text-decoration: none;
}
.switch-auth a:hover {
  text-decoration: underline;
}
</style>
