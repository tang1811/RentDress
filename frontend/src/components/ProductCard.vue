<script setup>
import { computed } from 'vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

// ✅ FIX: แก้ path รูปให้ถูกต้อง
// ฐานข้อมูลเก็บ path เช่น /uploads/products/xxx.jpg หรือ /images/dress1.jpg
// vite proxy จะ forward /uploads → http://localhost/RentDress/uploads
const displayImage = computed(() => {
  const imgPath = props.product.primary_image 
               || props.product.image_url 
               || props.product.image 
               || null

  if (!imgPath) return null

  // ถ้าเป็น path เต็ม http:// ใช้ได้เลย
  if (imgPath.startsWith('http')) return imgPath

  // ถ้า path ขึ้นต้นด้วย / ใช้ได้เลย (vite proxy จัดการให้)
  if (imgPath.startsWith('/')) return imgPath

  // ถ้าไม่มี / นำหน้า ให้เติม /
  return '/' + imgPath
})

const categoryLabel = computed(() => {
  const labels = { evening: 'ชุดราตรี', thai: 'ชุดไทย', suit: 'สูท', casual: 'ชุดลำลอง' }
  return labels[props.product.category] || props.product.category
})

const formatPrice = (price) => new Intl.NumberFormat('th-TH').format(price)
</script>

<template>
  <div class="card card-product h-100">
    <div class="position-relative">
      <img
        v-if="displayImage"
        :src="displayImage"
        :alt="product.name"
        class="card-img-top"
        @error="(e) => e.target.style.display='none'"
      >
      <div
        v-else
        class="card-img-top placeholder-image d-flex align-items-center justify-content-center bg-light"
        style="height: 320px;"
      >
        <div class="text-center">
          <i class="bi bi-image fs-1 text-muted"></i>
          <p class="small text-muted mt-2">ไม่มีรูปภาพ</p>
        </div>
      </div>

      <span class="badge badge-category position-absolute top-0 end-0 m-2">
        {{ categoryLabel }}
      </span>
    </div>

    <div class="card-body d-flex flex-column">
      <h5 class="card-title">{{ product.name }}</h5>
      <p class="card-text text-muted small flex-grow-1">
        {{ product.description?.substring(0, 80) }}{{ product.description?.length > 80 ? '...' : '' }}
      </p>
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="badge bg-secondary">Size: {{ product.size }}</span>
      </div>
      <div class="d-flex justify-content-between align-items-center">
        <span class="price fw-bold text-primary">฿{{ formatPrice(product.rental_price) }}/วัน</span>
        <router-link :to="`/products/${product.id}`" class="btn btn-gold btn-sm">
          ดูรายละเอียด
        </router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card-img-top { height: 320px; object-fit: cover; }
.btn-gold { background-color: #c9a227; color: white; border: none; }
.btn-gold:hover { background-color: #a3821d; }
</style>
