<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const stats = ref({
  todayBookings: 0,
  pendingPickups: 0,
  pendingReturns: 0,
  totalProducts: 0
})

const recentBookings = ref([])
const loading = ref(true)

const statusLabels = {
  pending: 'รอยืนยัน',
  confirmed: 'ยืนยันแล้ว',
  ready: 'พร้อมรับชุด',
  rented: 'กำลังเช่า',
  returned: 'คืนชุดแล้ว',
  completed: 'เสร็จสิ้น',
  cancelled: 'ยกเลิก'
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('th-TH', {
    month: 'short',
    day: 'numeric'
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('th-TH').format(price)
}

onMounted(async () => {
  try {
    // Fetch all bookings
    const bookingsRes = await api.getBookings({ all: true })
    const allBookings = bookingsRes.data.data || []

    // Fetch products
    const productsRes = await api.getProducts()
    const products = productsRes.data.data || []

    // Calculate stats
    const today = new Date().toISOString().split('T')[0]

    stats.value.totalProducts = products.length
    stats.value.todayBookings = allBookings.filter(b =>
      b.pickup_date === today || b.return_date === today
    ).length
    stats.value.pendingPickups = allBookings.filter(b =>
      ['confirmed', 'ready'].includes(b.status)
    ).length
    stats.value.pendingReturns = allBookings.filter(b =>
      b.status === 'rented'
    ).length

    // Recent bookings
    recentBookings.value = allBookings.slice(0, 5)
  } catch (error) {
    console.error('Error loading dashboard:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="py-4">
    <div class="container-fluid">
      <h1 class="mb-4">Dashboard</h1>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border spinner-gold"></div>
      </div>

      <div v-else>
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
          <div class="col-md-3">
            <div class="card admin-card stat-card">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="text-muted">การจองวันนี้</h6>
                    <h2 class="stat-number">{{ stats.todayBookings }}</h2>
                  </div>
                  <div class="text-warning">
                    <i class="bi bi-calendar-event display-5"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card admin-card stat-card">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="text-muted">รอส่งมอบชุด</h6>
                    <h2 class="stat-number">{{ stats.pendingPickups }}</h2>
                  </div>
                  <div class="text-info">
                    <i class="bi bi-box-seam display-5"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card admin-card stat-card">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="text-muted">รอรับคืน</h6>
                    <h2 class="stat-number">{{ stats.pendingReturns }}</h2>
                  </div>
                  <div class="text-success">
                    <i class="bi bi-arrow-return-left display-5"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card admin-card stat-card">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="text-muted">ชุดทั้งหมด</h6>
                    <h2 class="stat-number">{{ stats.totalProducts }}</h2>
                  </div>
                  <div class="text-primary">
                    <i class="bi bi-handbag display-5"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="row g-4 mb-4">
          <div class="col-md-4">
            <router-link to="/admin/products" class="text-decoration-none">
              <div class="card admin-card h-100">
                <div class="card-body text-center py-4">
                  <i class="bi bi-plus-circle display-4 text-warning mb-2"></i>
                  <h5>จัดการชุด</h5>
                  <p class="text-muted mb-0">เพิ่ม แก้ไข ลบชุด</p>
                </div>
              </div>
            </router-link>
          </div>
          <div class="col-md-4">
            <router-link to="/admin/bookings" class="text-decoration-none">
              <div class="card admin-card h-100">
                <div class="card-body text-center py-4">
                  <i class="bi bi-list-check display-4 text-warning mb-2"></i>
                  <h5>จัดการการจอง</h5>
                  <p class="text-muted mb-0">ดูและอัปเดตสถานะการจอง</p>
                </div>
              </div>
            </router-link>
          </div>
          <div class="col-md-4">
            <router-link to="/admin/calendar" class="text-decoration-none">
              <div class="card admin-card h-100">
                <div class="card-body text-center py-4">
                  <i class="bi bi-calendar3 display-4 text-warning mb-2"></i>
                  <h5>ปฏิทินการจอง</h5>
                  <p class="text-muted mb-0">ดูภาพรวมการจอง</p>
                </div>
              </div>
            </router-link>
          </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card admin-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="card-title mb-0">การจองล่าสุด</h5>
              <router-link to="/admin/bookings" class="btn btn-sm btn-outline-warning">
                ดูทั้งหมด
              </router-link>
            </div>

            <div v-if="recentBookings.length === 0" class="text-center py-4 text-muted">
              ยังไม่มีการจอง
            </div>

            <div v-else class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>ลูกค้า</th>
                    <th>ชุด</th>
                    <th>วันที่</th>
                    <th>ราคา</th>
                    <th>สถานะ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="booking in recentBookings" :key="booking.id">
                    <td>{{ booking.id }}</td>
                    <td>{{ booking.user_name }}</td>
                    <td>{{ booking.product_name }}</td>
                    <td>{{ formatDate(booking.pickup_date) }} - {{ formatDate(booking.return_date) }}</td>
                    <td>฿{{ formatPrice(booking.total_price) }}</td>
                    <td>
                      <span :class="'badge status-' + booking.status">
                        {{ statusLabels[booking.status] || booking.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
