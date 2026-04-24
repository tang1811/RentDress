<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore, useBookingStore } from '../store'
import { useRouter } from 'vue-router'
import api from '../services/api'

const authStore = useAuthStore()
const bookingStore = useBookingStore()
const router = useRouter()

const activeTab = ref('bookings')
const editMode = ref(false)
const loading = ref(false)
const successMsg = ref('')

const user = computed(() => authStore.currentUser)
const bookings = computed(() => bookingStore.bookings)

const form = ref({ name: '', phone: '', address: '' })

const statusLabel = {
  pending:   { text: 'รอยืนยัน',   class: 'warning' },
  confirmed: { text: 'ยืนยันแล้ว', class: 'success' },
  cancelled: { text: 'ยกเลิก',     class: 'danger' },
  completed: { text: 'เสร็จสิ้น',  class: 'secondary' }
}

const formatPrice = (price) => new Intl.NumberFormat('th-TH').format(price)
const formatDate  = (dateStr) => new Date(dateStr).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' })

const startEdit = () => {
  form.value = { name: user.value.name, phone: user.value.phone || '', address: user.value.address || '' }
  editMode.value = true
}

const saveProfile = async () => {
  loading.value = true
  const result = await authStore.updateProfile(form.value)
  loading.value = false
  if (result.success) {
    successMsg.value = 'อัปเดตข้อมูลสำเร็จ'
    editMode.value = false
    setTimeout(() => { successMsg.value = '' }, 3000)
  } else {
    alert(result.message)
  }
}

const cancelBooking = async (id) => {
  if (!confirm('ยืนยันยกเลิกการจอง?')) return
  const result = await bookingStore.updateBooking({ id, status: 'cancelled' })
  if (result.success) {
    await bookingStore.fetchBookings(user.value.id)
  } else {
    alert(result.message)
  }
}

// ✅ Review
const showReviewModal = ref(false)
const selectedBooking  = ref(null)
const reviewForm       = ref({ rating: 5, comment: '' })
const reviewLoading    = ref(false)

const openReview = (booking) => {
  console.log('booking data:', booking)
  selectedBooking.value = booking
  reviewForm.value = { rating: 5, comment: '' }
  showReviewModal.value = true
}

const submitReview = async () => {
  reviewLoading.value = true
  try {
    const response = await api.createReview({
      user_id:    user.value.id,
      product_id: selectedBooking.value.product_id,
      booking_id: selectedBooking.value.id,
      rating:     reviewForm.value.rating,
      comment:    reviewForm.value.comment
    })
    if (response.data.success) {
      alert('ขอบคุณสำหรับรีวิว!')
      showReviewModal.value = false
    } else {
      alert(response.data.message)
    }
  } catch (e) {
    console.error('Review error:', e)
    alert('เกิดข้อผิดพลาด: ' + (e.response?.data?.message || e.message))
  }
  reviewLoading.value = false
}

const logout = () => {
  authStore.logout()
  router.push('/')
}

onMounted(async () => {
  await bookingStore.fetchBookings(user.value?.id)
})
</script>

<template>
  <div class="py-5">
    <div class="container">
      <h1 class="section-title text-center mb-5">บัญชีของฉัน</h1>
      <div class="row">

        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
          <div class="card admin-card text-center p-4 mb-3">
            <div class="avatar-circle mx-auto mb-3">
              <i class="bi bi-person-fill fs-1 text-warning"></i>
            </div>
            <h5 class="mb-1">{{ user?.name }}</h5>
            <small class="text-muted">{{ user?.email }}</small>
            <hr>
            <div class="d-grid gap-2">
              <button @click="activeTab='bookings'" class="btn" :class="activeTab==='bookings' ? 'btn-gold' : 'btn-outline-secondary'">
                <i class="bi bi-calendar-check me-2"></i>การจองของฉัน
              </button>
              <button @click="activeTab='profile'" class="btn" :class="activeTab==='profile' ? 'btn-gold' : 'btn-outline-secondary'">
                <i class="bi bi-person me-2"></i>ข้อมูลส่วนตัว
              </button>
              <button @click="logout" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
              </button>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="col-md-9">

          <!-- Bookings tab -->
          <div v-if="activeTab === 'bookings'">
            <div class="card admin-card">
              <div class="card-body">
                <h5 class="card-title mb-4">ประวัติการจอง</h5>

                <div v-if="bookingStore.loading" class="text-center py-4">
                  <div class="spinner-border spinner-gold"></div>
                </div>

                <div v-else-if="bookings.length === 0" class="text-center py-5 text-muted">
                  <i class="bi bi-calendar-x display-4 d-block mb-3"></i>
                  ยังไม่มีการจอง
                  <div class="mt-3">
                    <router-link to="/products" class="btn btn-gold">เลือกชุด</router-link>
                  </div>
                </div>

                <div v-else>
                  <div v-for="booking in bookings" :key="booking.id" class="booking-card card mb-3">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col-md-2">
                          <img v-if="booking.product_image" :src="booking.product_image" class="img-fluid rounded" style="height:80px;object-fit:cover;">
                          <div v-else class="bg-light rounded d-flex align-items-center justify-content-center" style="height:80px;">
                            <i class="bi bi-image text-muted"></i>
                          </div>
                        </div>
                        <div class="col-md-5">
                          <h6 class="mb-1">{{ booking.product_name }}</h6>
                          <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>{{ formatDate(booking.pickup_date) }} — {{ formatDate(booking.return_date) }}
                          </small>
                        </div>
                        <div class="col-md-2 text-center">
                          <span class="badge" :class="`bg-${statusLabel[booking.status]?.class || 'secondary'}`">
                            {{ statusLabel[booking.status]?.text || booking.status }}
                          </span>
                        </div>
                        <div class="col-md-3 text-end">
                          <div class="fw-bold text-warning mb-1">฿{{ formatPrice(booking.total_price) }}</div>
                          <button v-if="booking.status === 'pending'" @click="cancelBooking(booking.id)"
                            class="btn btn-sm btn-outline-danger">ยกเลิก</button>
                          <button v-if="['completed','returned'].includes(booking.status)"
                            @click="openReview(booking)"
                            class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-star me-1"></i>รีวิว
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Profile tab -->
          <div v-if="activeTab === 'profile'">
            <div class="card admin-card">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <h5 class="card-title mb-0">ข้อมูลส่วนตัว</h5>
                  <button v-if="!editMode" @click="startEdit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-pencil me-1"></i>แก้ไข
                  </button>
                </div>
                <div v-if="successMsg" class="alert alert-success">{{ successMsg }}</div>
                <div v-if="!editMode">
                  <div class="row mb-3"><div class="col-4 text-muted">ชื่อ</div><div class="col-8">{{ user?.name }}</div></div>
                  <div class="row mb-3"><div class="col-4 text-muted">อีเมล</div><div class="col-8">{{ user?.email }}</div></div>
                  <div class="row mb-3"><div class="col-4 text-muted">โทรศัพท์</div><div class="col-8">{{ user?.phone || '-' }}</div></div>
                  <div class="row mb-3"><div class="col-4 text-muted">ที่อยู่</div><div class="col-8">{{ user?.address || '-' }}</div></div>
                </div>
                <div v-else>
                  <div class="mb-3">
                    <label class="form-label">ชื่อ</label>
                    <input v-model="form.name" class="form-control" type="text" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">อีเมล</label>
                    <input :value="user?.email" class="form-control" type="email" disabled>
                    <small class="text-muted">ไม่สามารถเปลี่ยนอีเมลได้</small>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">โทรศัพท์</label>
                    <input v-model="form.phone" class="form-control" type="tel">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">ที่อยู่</label>
                    <textarea v-model="form.address" class="form-control" rows="3"></textarea>
                  </div>
                  <div class="d-flex gap-2">
                    <button @click="saveProfile" class="btn btn-gold" :disabled="loading">
                      <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                      บันทึก
                    </button>
                    <button @click="editMode=false" class="btn btn-outline-secondary">ยกเลิก</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <div v-if="showReviewModal" class="modal d-block" style="background:rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">เขียนรีวิว — {{ selectedBooking?.product_name }}</h5>
            <button @click="showReviewModal=false" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">คะแนน</label>
              <div>
                <i v-for="i in 5" :key="i"
                  class="bi fs-3 me-1"
                  :class="i <= reviewForm.rating ? 'bi-star-fill text-warning' : 'bi-star text-muted'"
                  style="cursor:pointer"
                  @click="reviewForm.rating = i">
                </i>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">ความคิดเห็น</label>
              <textarea v-model="reviewForm.comment" class="form-control" rows="4" placeholder="เขียนรีวิวของคุณ..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showReviewModal=false" class="btn btn-secondary">ยกเลิก</button>
            <button @click="submitReview" class="btn btn-gold" :disabled="reviewLoading">
              <span v-if="reviewLoading" class="spinner-border spinner-border-sm me-1"></span>
              ส่งรีวิว
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.avatar-circle { width:80px; height:80px; background:#f5f5f5; border-radius:50%; display:flex; align-items:center; justify-content:center; }
.btn-gold { background-color:#c9a227; color:white; border:none; font-weight:bold; }
.btn-gold:hover { background-color:#a3821d; }
</style>