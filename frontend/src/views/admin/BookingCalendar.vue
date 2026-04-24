<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'

const currentDate = ref(new Date())
const bookings = ref([])
const products = ref([])
const loading = ref(true)

const currentMonth = computed(() => currentDate.value.getMonth())
const currentYear = computed(() => currentDate.value.getFullYear())

const monthNames = [
  'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
  'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
]

const dayNames = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส']

const daysInMonth = computed(() => {
  return new Date(currentYear.value, currentMonth.value + 1, 0).getDate()
})

const firstDayOfMonth = computed(() => {
  return new Date(currentYear.value, currentMonth.value, 1).getDay()
})

const calendarDays = computed(() => {
  const days = []

  // Empty slots
  for (let i = 0; i < firstDayOfMonth.value; i++) {
    days.push({ day: '', empty: true, bookings: [] })
  }

  // Days of the month
  for (let day = 1; day <= daysInMonth.value; day++) {
    const dateStr = formatDateStr(currentYear.value, currentMonth.value + 1, day)
    const dayBookings = bookings.value.filter(b => {
      return dateStr >= b.pickup_date && dateStr <= b.return_date
    })

    days.push({
      day,
      date: dateStr,
      bookings: dayBookings,
      isToday: dateStr === formatDateStr(new Date().getFullYear(), new Date().getMonth() + 1, new Date().getDate())
    })
  }

  return days
})

const formatDateStr = (year, month, day) => {
  return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
}

const prevMonth = () => {
  currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1)
  fetchData()
}

const nextMonth = () => {
  currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1)
  fetchData()
}

const getStatusColor = (status) => {
  const colors = {
    pending: '#ffc107',
    confirmed: '#17a2b8',
    ready: '#6f42c1',
    rented: '#007bff',
    returned: '#28a745',
    completed: '#28a745',
    cancelled: '#dc3545'
  }
  return colors[status] || '#6c757d'
}

const fetchData = async () => {
  loading.value = true
  try {
    const [bookingsRes, productsRes] = await Promise.all([
      api.getBookings({ all: true }),
      api.getProducts()
    ])

    if (bookingsRes.data.success) {
      bookings.value = bookingsRes.data.data.filter(b =>
        b.status !== 'cancelled' && b.status !== 'completed'
      )
    }
    if (productsRes.data.success) {
      products.value = productsRes.data.data
    }
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div class="py-4">
    <div class="container-fluid">
      <h1 class="mb-4">ปฏิทินการจอง</h1>

      <div class="card admin-card">
        <div class="card-body">
          <!-- Calendar Header -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <button @click="prevMonth" class="btn btn-outline-secondary">
              <i class="bi bi-chevron-left"></i> เดือนก่อน
            </button>
            <h3 class="mb-0">{{ monthNames[currentMonth] }} {{ currentYear + 543 }}</h3>
            <button @click="nextMonth" class="btn btn-outline-secondary">
              เดือนถัดไป <i class="bi bi-chevron-right"></i>
            </button>
          </div>

          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border spinner-gold"></div>
          </div>

          <div v-else>
            <!-- Weekday Headers -->
            <div class="row g-1 mb-2">
              <div v-for="day in dayNames" :key="day" class="col text-center">
                <strong :class="{ 'text-danger': day === 'อา' }">{{ day }}</strong>
              </div>
            </div>

            <!-- Calendar Grid -->
            <div class="row g-1">
              <div
                v-for="(day, index) in calendarDays"
                :key="index"
                class="col"
                :class="{ 'opacity-25': day.empty }"
              >
                <div
                  class="calendar-cell p-2 border rounded"
                  :class="{ 'bg-warning bg-opacity-25': day.isToday }"
                  style="min-height: 100px;"
                >
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span :class="{ 'fw-bold text-warning': day.isToday }">{{ day.day }}</span>
                    <span v-if="day.bookings?.length" class="badge bg-secondary">
                      {{ day.bookings.length }}
                    </span>
                  </div>

                  <div v-for="booking in day.bookings?.slice(0, 3)" :key="booking.id" class="mb-1">
                    <div
                      class="small p-1 rounded text-white text-truncate"
                      :style="{ backgroundColor: getStatusColor(booking.status) }"
                      :title="booking.product_name + ' - ' + booking.user_name"
                    >
                      {{ booking.product_name }}
                    </div>
                  </div>

                  <div v-if="day.bookings?.length > 3" class="small text-muted">
                    +{{ day.bookings.length - 3 }} อื่นๆ
                  </div>
                </div>
              </div>
            </div>

            <!-- Legend -->
            <div class="mt-4">
              <h6>สัญลักษณ์สถานะ:</h6>
              <div class="d-flex flex-wrap gap-3">
                <div v-for="(label, status) in {
                  pending: 'รอยืนยัน',
                  confirmed: 'ยืนยันแล้ว',
                  ready: 'พร้อมรับ',
                  rented: 'กำลังเช่า'
                }" :key="status" class="d-flex align-items-center">
                  <span
                    class="d-inline-block rounded me-2"
                    :style="{ backgroundColor: getStatusColor(status), width: '20px', height: '20px' }"
                  ></span>
                  <span>{{ label }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.calendar-cell {
  transition: all 0.2s;
}

.calendar-cell:hover {
  background-color: #f8f9fa;
}

.row > .col {
  flex: 0 0 calc(100% / 7);
  max-width: calc(100% / 7);
}
</style>
