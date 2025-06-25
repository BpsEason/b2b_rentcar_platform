<template>
  <div id="app">
    <nav>
      <router-link to="/">首頁</router-link> |
      <router-link to="/vehicles/search">搜尋車輛</router-link> | # 添加搜尋車輛連結
      <router-link to="/vehicles">車輛列表</router-link> |
      <router-link to="/bookings">我的訂單</router-link> |
      <router-link to="/login" v-if="!isLoggedIn">登入</router-link>
      <a href="#" @click.prevent="logout" v-else>登出</a>
    </nav>
    <router-view />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from './store/auth';

const authStore = useAuthStore();
const router = useRouter();

const isLoggedIn = computed(() => authStore.token !== null);

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
nav {
  margin-bottom: 20px;
}
nav a {
  font-weight: bold;
  color: #2c3e50;
}
nav a.router-link-exact-active {
  color: #42b983;
}
</style>
