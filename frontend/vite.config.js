import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '/RentDress/api')
      },
      '/uploads': {
        target: 'http://localhost',
        changeOrigin: true,
        rewrite: (path) => '/RentDress' + path
      }
    }
  },
  build: {
    outDir: '../dist',
    emptyOutDir: true
  }
})
