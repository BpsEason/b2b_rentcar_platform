import { mount } from '@vue/test-utils';
import { createRouter, createWebHistory } from 'vue-router'; # 引入路由相關
import { createPinia } from 'pinia'; # 引入 Pinia
import { nextTick } from 'vue'; # 引入 nextTick

import VehicleListView from '../src/views/VehicleListView.vue';
import axios from 'axios'; # 引入 axios

// Mock axios get 方法
vi.mock('axios', () => ({
  default: {
    get: vi.fn((url) => {
      if (url === '/vehicles') {
        return Promise.resolve({
          data: [
            { id: 1, make: 'Toyota', model: 'Camry', year: '2022', license_plate: 'ABC-123', daily_rate: 100, status: 'available', location: { name: '台北店' } },
            { id: 2, make: 'Honda', model: 'CRV', year: '2023', license_plate: 'DEF-456', daily_rate: 120, status: 'available', location: { name: '台中店' } },
          ]
        });
      }
      return Promise.resolve({ data: [] });
    }),
  },
}));

// 設置一個假的路由和 Pinia 實例，以便測試組件可以運行
const router = createRouter({
  history: createWebHistory(),
  routes: [{ path: '/', component: VehicleListView }] # 簡單路由配置
});
const pinia = createPinia();

describe('VehicleListView', () => {
  beforeEach(() => {
    // 重置 mock 函數的調用歷史，確保每次測試都是乾淨的
    axios.get.mockClear();
  });

  test('renders vehicle list', async () => {
      const wrapper = mount(VehicleListView, {
          global: {
              plugins: [router, pinia], # 注入路由和 Pinia
          }
      });

      // 等待數據加載完成
      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 0)); // 等待異步操作完成

      expect(wrapper.find('h1').text()).toBe('可用車輛');
      expect(wrapper.findAll('.vehicle-card').length).toBe(2);
      expect(wrapper.text()).toContain('Toyota Camry');
      expect(axios.get).toHaveBeenCalledWith('/vehicles'); // 驗證 axios.get 被調用
  });

  test('displays loading state', () => {
      const wrapper = mount(VehicleListView, {
          global: {
              plugins: [router, pinia],
          }
      });
      expect(wrapper.text()).toContain('載入中...');
  });

  test('displays error message on fetch failure', async () => {
      axios.get.mockImplementationOnce(() => Promise.reject(new Error('Network Error')));

      const wrapper = mount(VehicleListView, {
          global: {
              plugins: [router, pinia],
          }
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 0)); // 等待異步操作完成

      expect(wrapper.text()).toContain('無法載入車輛列表。');
  });
});
