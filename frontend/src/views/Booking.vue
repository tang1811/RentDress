<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore, useAuthStore, useBookingStore } from '../store'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()
const authStore = useAuthStore()
const bookingStore = useBookingStore()

const loading = ref(false)
const notes = ref('')

const product = computed(() => productStore.currentProduct)
const user = computed(() => authStore.currentUser)
const startDate = computed(() => route.query.start)
const endDate = computed(() => route.query.end)

const rentalDays = computed(() => {
  if (!startDate.value || !endDate.value) return 0
  const start = new Date(startDate.value)
  const end = new Date(endDate.value)
  return Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1
})

const totalPrice = computed(() => {
  if (!product.value) return 0
  return product.value.rental_price * rentalDays.value
})

const formatPrice = (price) => new Intl.NumberFormat('th-TH').format(price)

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' })
}

const submitBooking = async () => {
  if (!startDate.value || !endDate.value) {
    alert('กรุณาเลือกวันที่ก่อน')
    return
  }
  loading.value = true
  const result = await bookingStore.createBooking({
    product_id: product.value.id,
    pickup_date: startDate.value,
    return_date: endDate.value,
    notes: notes.value
  })
  loading.value = false

  if (result.success) {
    alert('จองสำเร็จ! กรุณาติดต่อชำระมัดจำภายใน 24 ชั่วโมง')
    router.push('/profile')
  } else {
    alert(result.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่')
  }
}

onMounted(async () => {
  if (!startDate.value || !endDate.value) {
    router.push(`/products/${route.params.id}`)
    return
  }
  await productStore.fetchProduct(route.params.id)
})
</script>

<template>
  <div class="py-5">
    <div class="container">
      <div v-if="productStore.loading" class="text-center py-5">
        <div class="spinner-border spinner-gold" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <div v-else-if="!product" class="text-center py-5">
        <p>ไม่พบข้อมูลชุด</p>
      </div>

      <div v-else>
        <h1 class="text-center section-title mb-5">ยืนยันการจอง</h1>

        <div class="row">
          <div class="col-md-8">
            <div class="card admin-card mb-4">
              <div class="card-body">
                <h5 class="card-title mb-4">รายละเอียดการจอง</h5>
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="img-fluid rounded">
                    <div v-else class="placeholder-image rounded" style="height: 200px;">
                      <i class="bi bi-image"></i>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <h4>{{ product.name }}</h4>
                    <p class="text-muted">{{ product.description }}</p>
                    <div class="row">
                      <div class="col-6">
                        <p class="mb-1"><strong>ไซส์:</strong> {{ product.size }}</p>
                        <p class="mb-1"><strong>สี:</strong> {{ product.color }}</p>
                      </div>
                      <div class="col-6">
                        <p class="mb-1"><strong>ค่าเช่า/วัน:</strong> ฿{{ formatPrice(product.rental_price) }}</p>
                        <p class="mb-1"><strong>มัดจำ:</strong> ฿{{ formatPrice(product.deposit_price) }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-md-6">
                    <div class="date-box p-3 bg-light rounded mb-3">
                      <i class="bi bi-calendar-check text-success me-2"></i>
                      <strong>วันรับชุด:</strong> {{ formatDate(startDate) }}
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="date-box p-3 bg-light rounded mb-3">
                      <i class="bi bi-calendar-x text-danger me-2"></i>
                      <strong>วันคืนชุด:</strong> {{ formatDate(endDate) }}
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">หมายเหตุ (ถ้ามี)</label>
                  <textarea v-model="notes" class="form-control" rows="3"
                    placeholder="ระบุข้อความถึงร้าน เช่น ต้องการแก้ไขชุด หรือนัดเวลารับชุด"></textarea>
                </div>
              </div>
            </div>

            <div class="card admin-card mb-4">
              <div class="card-body">
                <h5 class="card-title mb-3">ข้อมูลผู้จอง</h5>
                <div class="row">
                  <div class="col-md-6">
                    <p class="mb-1"><strong>ชื่อ:</strong> {{ user?.name }}</p>
                    <p class="mb-1"><strong>อีเมล:</strong> {{ user?.email }}</p>
                  </div>
                  <div class="col-md-6">
                    <p class="mb-1"><strong>โทรศัพท์:</strong> {{ user?.phone || '-' }}</p>
                    <p class="mb-1"><strong>ที่อยู่:</strong> {{ user?.address || '-' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card admin-card sticky-top" style="top: 100px;">
              <div class="card-body">
                <h5 class="card-title mb-4">สรุปค่าใช้จ่าย</h5>
                <div class="d-flex justify-content-between mb-2">
                  <span>ค่าเช่า ({{ rentalDays }} วัน)</span>
                  <span>฿{{ formatPrice(totalPrice) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span>มัดจำ</span>
                  <span>฿{{ formatPrice(product.deposit_price) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                  <strong>รวมทั้งสิ้น</strong>
                  <strong class="text-warning fs-4">฿{{ formatPrice(totalPrice + Number(product.deposit_price)) }}</strong>
                </div>
                <div class="alert alert-warning small mt-3">
                  <i class="bi bi-info-circle me-1"></i>
                  มัดจำจะได้รับคืนเมื่อคืนชุดในสภาพปกติ
                </div>
                <button @click="submitBooking" class="btn btn-gold btn-lg w-100" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-check-circle me-2"></i>
                  ยืนยันการจอง
                </button>
                <router-link :to="`/products/${product.id}`" class="btn btn-outline-secondary w-100 mt-2">
                  ยกเลิก
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
