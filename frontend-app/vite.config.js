import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    port: 3000, // 前端開發伺服器端口
  },
  test: { # Vitest 配置 (根據優化建議)
    environment: 'jsdom',
    coverage: {
      provider: 'v8'
    }
  }
})
