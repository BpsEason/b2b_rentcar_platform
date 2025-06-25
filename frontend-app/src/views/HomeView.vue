<template>
  <div class="home">
    <h1>歡迎來到租車平台</h1>
    <p>在這裡您可以輕鬆租賃心儀的車輛。</p>
    <div class="quick-links">
      <router-link to="/vehicles" class="button">查看所有車輛</router-link>
      <router-link to="/vehicles/search" class="button">搜尋車輛</router-link> # 添加搜尋車輛按鈕
      <router-link to="/login" class="button" v-if="!isLoggedIn">立即登入</router-link>
      <router-link to="/register" class="button" v-if="!isLoggedIn">立即註冊</router-link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../store/auth';

const authStore = useAuthStore();
const isLoggedIn = computed(() => authStore.token !== null);
</script>

<style scoped>
.home {
  padding: 20px;
}
.quick-links {
  margin-top: 30px;
}
.button {
  display: inline-block;
  background-color: #42b983;
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  text-decoration: none;
  margin: 0 10px;
  transition: background-color 0.3s ease;
}
.button:hover {
  background-color: #36a071;
}
</style>
