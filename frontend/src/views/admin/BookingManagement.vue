<script setup>
import { ref, computed, onMounted } from 'vue'
import { useBookingStore, useAuthStore } from '../../store'

const bookingStore = useBookingStore()
const authStore = useAuthStore()

const filterStatus = ref('all')
const searchQuery = ref('')

const bookings = computed(() => bookingStore.bookings)

const filteredBookings = computed(() => {
  return bookings.value.filter(b => {
    const matchStatus = filterStatus.value === 'all' || b.status === filterStatus.value
    const matchSearch = !searchQuery.value || 
      b.user_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      b.product_name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchStatus && matchSearch
  })
})

const summary = computed(() => ({
  total:     bookings.value.length,
  pending:   bookings.value.filter(b => b.status === 'pending').length,
  confirmed: bookings.value.filter(b => b.status === 'confirmed').length,
  completed: bookings.value.filter(b => b.status === 'completed').length,
  revenue:   bookings.value.filter(b => b.status === 'completed').reduce((s, b) => s + Number(b.total_price), 0)
}))

const statusLabel = {
  pending:   { text: 'รอยืนยัน',   class: 'warning' },
  confirmed: { text: 'ยืนยันแล้ว', class: 'success' },
  cancelled: { text: 'ยกเลิก',     class: 'danger' },
  completed: { text: 'เสร็จสิ้น',  class: 'secondary' }
}

const formatPrice = (price) => new Intl.NumberFormat('th-TH').format(price)
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' })

const updateStatus = async (booking, newStatus) => {
  if (!confirm(`เปลี่ยนสถานะเป็น "${statusLabel[newStatus]?.text}" ใช่ไหม?`)) return
  const result = await bookingStore.updateBooking({ id: booking.id, status: newStatus })
  if (result.success) {
    await bookingStore.fetchBookings(null, true)
  } else {
    alert(result.message)
  }
}

onMounted(async () => {
  await bookingStore.fetchBookings(null, true)
})
</script>

<template>
  <div>
    <h4 class="mb-4">จัดการการจอง</h4>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card text-center p-3 border-warning">
          <div class="text-warning fw-bold fs-3">{{ summary.pending }}</div>
          <small class="text-muted">รอยืนยัน</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center p-3 border-success">
          <div class="text-success fw-bold fs-3">{{ summary.confirmed }}</div>
          <small class="text-muted">ยืนยันแล้ว</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center p-3 border-secondary">
          <div class="text-secondary fw-bold fs-3">{{ summary.completed }}</div>
          <small class="text-muted">เสร็จสิ้น</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center p-3 border-warning">
          <div class="text-warning fw-bold fs-3">฿{{ formatPrice(summary.revenue) }}</div>
          <small class="text-muted">รายได้รวม</small>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card admin-card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <input v-model="searchQuery" class="form-control" placeholder="ค้นหาชื่อลูกค้า หรือชุด...">
          </div>
          <div class="col-md-3">
            <select v-model="filterStatus" class="form-select">
              <option value="all">ทุกสถานะ</option>
              <option value="pending">รอยืนยัน</option>
              <option value="confirmed">ยืนยันแล้ว</option>
              <option value="completed">เสร็จสิ้น</option>
              <option value="cancelled">ยกเลิก</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card admin-card">
      <div class="card-body p-0">
        <div v-if="bookingStore.loading" class="text-center py-5">
          <div class="spinner-border spinner-gold"></div>
        </div>

        <div v-else-if="filteredBookings.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-calendar-x display-4 d-block mb-2"></i>ไม่พบข้อมูลการจอง
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>ลูกค้า</th>
                <th>ชุด</th>
                <th>วันรับ</th>
                <th>วันคืน</th>
                <th>ราคา</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="booking in filteredBookings" :key="booking.id">
                <td><small class="text-muted">#{{ booking.id }}</small></td>
                <td>
                  <div class="fw-semibold">{{ booking.user_name }}</div>
                  <small class="text-muted">{{ booking.user_email }}</small>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <img v-if="booking.product_image" :src="booking.product_image" style="width:40px;height:40px;object-fit:cover;" class="rounded">
                    <span>{{ booking.product_name }}</span>
                  </div>
                </td>
                <td>{{ formatDate(booking.pickup_date) }}</td>
                <td>{{ formatDate(booking.return_date) }}</td>
                <td class="text-warning fw-bold">฿{{ formatPrice(booking.total_price) }}</td>
                <td>
                  <span class="badge" :class="`bg-${statusLabel[booking.status]?.class}`">
                    {{ statusLabel[booking.status]?.text }}
                  </span>
                </td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                      เปลี่ยนสถานะ
                    </button>
                    <ul class="dropdown-menu">
                      <li v-if="booking.status === 'pending'">
                        <button class="dropdown-item text-success" @click="updateStatus(booking, 'confirmed')">
                          <i class="bi bi-check-circle me-2"></i>ยืนยัน
                        </button>
                      </li>
                      <li v-if="booking.status === 'confirmed'">
                        <button class="dropdown-item text-secondary" @click="updateStatus(booking, 'completed')">
                          <i class="bi bi-flag me-2"></i>เสร็จสิ้น
                        </button>
                      </li>
                      <li v-if="['pending','confirmed'].includes(booking.status)">
                        <button class="dropdown-item text-danger" @click="updateStatus(booking, 'cancelled')">
                          <i class="bi bi-x-circle me-2"></i>ยกเลิก
                        </button>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
