import { mount } from '@vue/test-utils';
import { createPinia } from 'pinia';
import LoginView from '../src/views/LoginView.vue';
import { useAuthStore } from '../src/store/auth';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { createRouter, createWebHistory } from 'vue-router';

// Mock useRouter 和 useAuthStore
vi.mock('vue-router', () => ({
  useRouter: vi.fn(() => ({
    push: vi.fn(),
  })),
  createRouter: vi.fn(() => ({
    beforeEach: vi.fn(),
    options: { routes: [] },
    addRoute: vi.fn(),
    removeRoute: vi.fn(),
    hasRoute: vi.fn(),
    getRoutes: vi.fn(),
    resolve: vi.fn(),
  })),
  createWebHistory: vi.fn(),
}));

vi.mock('../src/store/auth', () => ({
  useAuthStore: vi.fn(() => ({
    login: vi.fn(),
    token: null, // 預設 token 為 null
    initialize: vi.fn(),
  })),
}));

describe('LoginView', () => {
  let authStore;
  let router;

  beforeEach(() => {
    // 重置 mock
    vi.clearAllMocks();
    authStore = useAuthStore();
    router = useRouter();
  });

  test('renders login form', () => {
    const wrapper = mount(LoginView, {
      global: {
        plugins: [createPinia()],
      },
    });
    expect(wrapper.find('h1').text()).toBe('登入');
    expect(wrapper.find('input[type="email"]').exists()).toBe(true);
    expect(wrapper.find('input[type="password"]').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toBe('登入');
  });

  test('handles successful login', async () => {
    authStore.login.mockResolvedValueOnce({}); // 模擬登入成功

    const wrapper = mount(LoginView, {
      global: {
        plugins: [createPinia()],
      },
    });

    await wrapper.find('input[type="email"]').setValue('test@example.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    expect(authStore.login).toHaveBeenCalledWith('test@example.com', 'password123');
    expect(router.push).toHaveBeenCalledWith('/bookings');
    expect(wrapper.find('.error-message').exists()).toBe(false);
  });

  test('handles failed login', async () => {
    authStore.login.mockRejectedValueOnce(new Error('Invalid credentials')); // 模擬登入失敗

    const wrapper = mount(LoginView, {
      global: {
        plugins: [createPinia()],
      },
    });

    await wrapper.find('input[type="email"]').setValue('wrong@example.com');
    await wrapper.find('input[type="password"]').setValue('wrongpass');
    await wrapper.find('form').trigger('submit.prevent');

    expect(authStore.login).toHaveBeenCalledWith('wrong@example.com', 'wrongpass');
    expect(router.push).not.toHaveBeenCalled(); // 登入失敗不應跳轉
    expect(wrapper.find('.error-message').text()).toContain('Invalid credentials');
  });

  test('disables button during loading', async () => {
    authStore.login.mockImplementationOnce(() => new Promise(resolve => setTimeout(resolve, 100))); // 模擬異步登入

    const wrapper = mount(LoginView, {
      global: {
        plugins: [createPinia()],
      },
    });

    await wrapper.find('input[type="email"]').setValue('test@example.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined();
    expect(wrapper.find('button[type="submit"]').text()).toBe('登入中...');
  });
});
