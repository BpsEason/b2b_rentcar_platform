import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import VehicleListView from '../views/VehicleListView.vue';
import BookingListView from '../views/BookingListView.vue';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';
import VehicleSearch from '../views/VehicleSearch.vue'; # 引入 VehicleSearch
import { useAuthStore } from '../store/auth';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: HomeView
  },
  {
    path: '/vehicles',
    name: 'Vehicles',
    component: VehicleListView
  },
  {
    path: '/vehicles/search', # 添加搜尋車輛路由
    name: 'VehicleSearch',
    component: VehicleSearch
  },
  {
    path: '/bookings',
    name: 'Bookings',
    component: BookingListView,
    meta: { requiresAuth: true } # 需要認證
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginView
  },
  {
    path: '/register',
    name: 'Register',
    component: RegisterView
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  if (to.meta.requiresAuth && !authStore.token) {
    next('/login'); // 重定向到登入頁面
  } else {
    next();
  }
});

export default router;
