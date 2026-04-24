import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || '/api'

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: { 'Content-Type': 'application/json' }
})

// ✅ FIX: interceptor แนบ token ทุก request อัตโนมัติ
apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }
  return config
})

// ✅ FIX: interceptor handle 401 → logout อัตโนมัติ
apiClient.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default {
  // Auth
  login: (email, password) => apiClient.post('/users/login.php', { email, password }),
  register: (data) => apiClient.post('/users/register.php', data),
  getProfile: () => apiClient.get('/users/profile.php'),
  updateProfile: (data) => apiClient.put('/users/profile.php', data),

  // Products
  getProducts: (params = {}) => apiClient.get('/products/read.php', { params }),
 getProduct: (id) => apiClient.get('/products/read_single.php', { params: { id } }),
  createProduct: (data) => apiClient.post('/products/create.php', data),
  updateProduct: (data) => apiClient.put('/products/update.php', data),
  deleteProduct: (id) => apiClient.delete('/products/delete.php', { params: { id } }),

  // Bookings
  getBookings: (params = {}) => apiClient.get('/bookings/read.php', { params }),
  createBooking: (data) => apiClient.post('/bookings/create.php', data),
  updateBooking: (data) => apiClient.put('/bookings/update.php', data),
  cancelBooking: (id) => apiClient.put('/bookings/update.php', { id, status: 'cancelled' }),

  // Users (Admin)
  getUsers: () => apiClient.get('/users/read.php'),

  // Calendar
  checkAvailability: (productId, month, year) =>
    apiClient.get('/bookings/check_availability.php', {
      params: { product_id: productId, month, year }
    }),

  // Reviews
  getReviews: (productId) => apiClient.get('/reviews/read.php', { params: { product_id: productId } }),
  createReview: (data) => apiClient.post('/reviews/create.php', data)
}
