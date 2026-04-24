import { defineStore } from 'pinia'
import api from '../services/api'

// Auth Store
export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    currentUser: (state) => state.user
  },

  actions: {
    async login(email, password) {
      try {
        const response = await api.login(email, password)
        if (response.data.success) {
          this.user = response.data.data.user
          this.token = response.data.data.token
          localStorage.setItem('user', JSON.stringify(this.user))
          localStorage.setItem('token', this.token)
          return { success: true }
        }
        return { success: false, message: response.data.message }
      } catch (error) {
        return { success: false, message: error.response?.data?.message || 'เข้าสู่ระบบไม่สำเร็จ' }
      }
    },

    async register(userData) {
      try {
        const response = await api.register(userData)
        if (response.data.success) {
          this.user = response.data.data.user
          this.token = response.data.data.token
          localStorage.setItem('user', JSON.stringify(this.user))
          localStorage.setItem('token', this.token)
          return { success: true }
        }
        return { success: false, message: response.data.message }
      } catch (error) {
        return { success: false, message: error.response?.data?.message || 'สมัครสมาชิกไม่สำเร็จ' }
      }
    },

    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('user')
      localStorage.removeItem('token')
    },

    async updateProfile(userData) {
      try {
        const response = await api.updateProfile({ ...userData, user_id: this.user.id })
        if (response.data.success) {
          this.user = { ...this.user, ...userData }
          localStorage.setItem('user', JSON.stringify(this.user))
          return { success: true }
        }
        return { success: false, message: response.data.message }
      } catch (error) {
        return { success: false, message: error.response?.data?.message || 'อัปเดตข้อมูลไม่สำเร็จ' }
      }
    }
  }
})

// Products Store
export const useProductStore = defineStore('products', {
  state: () => ({
    products: [],
    currentProduct: null,
    loading: false,
    filters: {
      category: '',
      size: '',
      color: '',
      minPrice: '',
      maxPrice: '',
      search: ''
    }
  }),

  actions: {
    async fetchProducts(filters = {}) {
      this.loading = true
      try {
        const response = await api.getProducts(filters)
        if (response.data.success) {
          this.products = response.data.data
        }
      } catch (error) {
        console.error('Error fetching products:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchProduct(id) {
      this.loading = true
      this.currentProduct = null
      try {
        const response = await api.getProduct(id)
        if (response.data.success) {
          this.currentProduct = response.data.data
        }
      } catch (error) {
        console.error('Error fetching product:', error)
      } finally {
        this.loading = false
      }
    },

    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
    },

    clearFilters() {
      this.filters = { category: '', size: '', color: '', minPrice: '', maxPrice: '', search: '' }
    }
  }
})

// Bookings Store
export const useBookingStore = defineStore('bookings', {
  state: () => ({
    bookings: [],
    currentBooking: null,
    loading: false
  }),

  actions: {
    async fetchBookings(userId = null, all = false) {
      this.loading = true
      try {
        const params = {}
        if (userId) params.user_id = userId
        if (all) params.all = true
        const response = await api.getBookings(params)
        if (response.data.success) {
          this.bookings = response.data.data
        }
      } catch (error) {
        console.error('Error fetching bookings:', error)
      } finally {
        this.loading = false
      }
    },

    async createBooking(bookingData) {
      try {
        const response = await api.createBooking(bookingData)
        return response.data
      } catch (error) {
        return { success: false, message: error.response?.data?.message || 'จองไม่สำเร็จ' }
      }
    },

    async updateBooking(bookingData) {
      try {
        const response = await api.updateBooking(bookingData)
        return response.data
      } catch (error) {
        return { success: false, message: error.response?.data?.message || 'อัปเดตไม่สำเร็จ' }
      }
    }
  }
})
