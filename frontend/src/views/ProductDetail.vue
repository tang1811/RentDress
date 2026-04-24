<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore, useAuthStore } from '../store'
import BookingCalendar from '../components/BookingCalendar.vue'
import ReviewList from '../components/ReviewList.vue'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()
const authStore = useAuthStore()

const currentImageIndex = ref(0)
const product = computed(() => productStore.currentProduct)
const isAuthenticated = computed(() => authStore.isAuthenticated)

// ✅ FIX #1: เพิ่ม state สำหรับเก็บวันที่ที่เลือกจาก BookingCalendar
const selectedDates = ref({ startDate: null, endDate: null })

const productImages = computed(() => {
  if (!product.value) return []
  let imgs = []
  if (product.value.image_url) {
    imgs.push(product.value.image_url)
  }
  if (product.value.images && product.value.images.length > 0) {
    product.value.images.forEach(img => {
      if (img.image_url && img.image_url !== product.value.image_url) {
        imgs.push(img.image_url)
      }
    })
  }
  return imgs
})

const currentImage = computed(() => {
  return productImages.value.length > 0 ? productImages.value[currentImageIndex.value] : null
})

const formatPrice = (price) => new Intl.NumberFormat('th-TH').format(price)
const selectImage = (index) => { currentImageIndex.value = index }

// ✅ FIX #1: รับ event จาก BookingCalendar
const onDateSelect = (dates) => {
  selectedDates.value = dates
}

// ✅ FIX #1: ปุ่มจองชุดนำทางไปหน้า Booking พร้อม query params
const goToBooking = () => {
  if (!isAuthenticated.value) {
    router.push({ name: 'Login', query: { redirect: route.fullPath } })
    return
  }
  router.push({
    name: 'Booking',
    params: { id: product.value.id },
    query: {
      start: selectedDates.value.startDate,
      end: selectedDates.value.endDate
    }
  })
}

onMounted(async () => {
  currentImageIndex.value = 0
  await productStore.fetchProduct(route.params.id)
})
</script>

<template>
  <!-- ✅ FIX #5: เพิ่ม Loading state -->
  <div v-if="productStore.loading" class="text-center py-5">
    <div class="spinner-border spinner-gold" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <div v-else-if="!product" class="text-center py-5">
    <p class="text-muted">ไม่พบข้อมูลชุด</p>
    <router-link to="/products" class="btn btn-outline-secondary mt-2">กลับไปหน้าชุดทั้งหมด</router-link>
  </div>

  <div class="py-5" v-else>
    <div class="container">
      <div class="row">
        <div class="col-md-6 mb-4">
          <div class="main-image-container mb-3 position-relative">
            <img
              v-if="currentImage"
              :src="currentImage"
              class="img-fluid rounded shadow w-100"
              style="height: 550px; object-fit: cover;"
            >
            <div v-else class="placeholder-image rounded d-flex align-items-center justify-content-center bg-light" style="height: 550px;">
              <i class="bi bi-image display-1 text-muted"></i>
            </div>
          </div>

          <div class="d-flex gap-2 overflow-auto" v-if="productImages.length > 1">
            <img
              v-for="(img, idx) in productImages" :key="idx"
              :src="img" @click="selectImage(idx)"
              class="rounded cursor-pointer border"
              :class="{'border-warning': currentImageIndex === idx}"
              style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
            >
          </div>
        </div>

        <div class="col-md-6">
          <h1 class="mb-3">{{ product.name }}</h1>
          <p class="text-muted lead">{{ product.description }}</p>
          <div class="p-4 bg-dark text-white rounded mb-4">
            <div class="row">
              <div class="col-6">
                <small class="text-secondary">ค่าเช่า/วัน</small>
                <h2 class="text-warning">฿{{ formatPrice(product.rental_price) }}</h2>
              </div>
              <div class="col-6">
                <small class="text-secondary">มัดจำ</small>
                <h2 class="text-warning">฿{{ formatPrice(product.deposit_price) }}</h2>
              </div>
            </div>
          </div>

          <!-- ✅ FIX #1: ผูก @select event เพื่อรับวันที่ที่เลือก -->
          <BookingCalendar :productId="product.id" @select="onDateSelect" />

          <!-- ✅ FIX #1: ปุ่มจองมี @click handler และ disabled เมื่อยังไม่เลือกวันครบ -->
          <button
            @click="goToBooking"
            class="btn btn-gold btn-lg w-100 mt-4 py-3"
            :disabled="!selectedDates.startDate || !selectedDates.endDate"
          >
            <i class="bi bi-check-circle me-2"></i>
            {{ !selectedDates.startDate ? 'กรุณาเลือกวันรับชุด' : (!selectedDates.endDate ? 'กรุณาเลือกวันคืนชุด' : 'จองชุดนี้') }}
          </button>
        </div>
      </div>
      <ReviewList class="mt-5" :productId="product.id" />
    </div>
  </div>
</template>

<style scoped>
.btn-gold { background-color: #c9a227; color: white; border: none; font-weight: bold; }
.btn-gold:hover { background-color: #a3821d; }
.btn-gold:disabled { background-color: #9a8040; opacity: 0.65; cursor: not-allowed; }
</style>
