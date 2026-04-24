<script setup>
import { ref, onMounted } from 'vue'
import { useProductStore } from '../store'
import ProductCard from '../components/ProductCard.vue'

const productStore = useProductStore()
const featuredProducts = ref([])

onMounted(async () => {
  await productStore.fetchProducts({ status: 'available' })
  featuredProducts.value = productStore.products.slice(0, 4)
})
</script>

<template>
  <div>
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="container position-relative">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <h1 class="hero-title">
              เช่าชุดพรีเมียม<br>
              สำหรับทุก<span>โอกาสพิเศษ</span>
            </h1>
            <p class="lead mb-4">
              บริการให้เช่าชุดราตรี ชุดไทย สูท และชุดพิเศษ
              คุณภาพระดับพรีเมียม พร้อมบริการจัดส่งทั่วประเทศ
            </p>
            <router-link to="/products" class="btn btn-gold btn-lg me-3">
              ดูชุดทั้งหมด
            </router-link>
            <router-link to="/register" class="btn btn-black btn-lg">
              สมัครสมาชิก
            </router-link>
          </div>
          <div class="col-lg-6 text-center d-none d-lg-block">
            <div class="hero-image-placeholder">
              <i class="bi bi-gem display-1 text-warning"></i>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5">
      <div class="container">
        <h2 class="text-center section-title">หมวดหมู่ชุด</h2>
        <div class="row g-4 mt-4">
          <div class="col-md-3">
            <router-link to="/products?category=evening" class="text-decoration-none">
              <div class="card admin-card text-center p-4">
                <i class="bi bi-stars display-4 text-warning mb-3"></i>
                <h5>ชุดราตรี</h5>
                <p class="text-muted small mb-0">สำหรับงานกาล่า ปาร์ตี้</p>
              </div>
            </router-link>
          </div>
          <div class="col-md-3">
            <router-link to="/products?category=thai" class="text-decoration-none">
              <div class="card admin-card text-center p-4">
                <i class="bi bi-flower1 display-4 text-warning mb-3"></i>
                <h5>ชุดไทย</h5>
                <p class="text-muted small mb-0">ชุดไทยจักรี ชุดไทยประยุกต์</p>
              </div>
            </router-link>
          </div>
          <div class="col-md-3">
            <router-link to="/products?category=suit" class="text-decoration-none">
              <div class="card admin-card text-center p-4">
                <i class="bi bi-briefcase display-4 text-warning mb-3"></i>
                <h5>สูท</h5>
                <p class="text-muted small mb-0">สูทผู้ชาย ตัดเย็บพิเศษ</p>
              </div>
            </router-link>
          </div>
          <div class="col-md-3">
            <router-link to="/products?category=casual" class="text-decoration-none">
              <div class="card admin-card text-center p-4">
                <i class="bi bi-handbag display-4 text-warning mb-3"></i>
                <h5>ชุดลำลอง</h5>
                <p class="text-muted small mb-0">ชุดค็อกเทล เดรส</p>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5 bg-white">
      <div class="container">
        <h2 class="text-center section-title">ชุดแนะนำ</h2>
        <div class="row g-4 mt-4">
          <div v-for="product in featuredProducts" :key="product.id" class="col-md-3">
            <ProductCard :product="product" />
          </div>
        </div>
        <div class="text-center mt-5">
          <router-link to="/products" class="btn btn-gold btn-lg">
            ดูชุดทั้งหมด <i class="bi bi-arrow-right ms-2"></i>
          </router-link>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section class="py-5">
      <div class="container">
        <h2 class="text-center section-title">ขั้นตอนการเช่า</h2>
        <div class="row g-4 mt-4">
          <div class="col-md-3 text-center">
            <div class="step-number">1</div>
            <h5 class="mt-3">เลือกชุด</h5>
            <p class="text-muted">เลือกชุดที่ถูกใจจากคอลเลคชันของเรา</p>
          </div>
          <div class="col-md-3 text-center">
            <div class="step-number">2</div>
            <h5 class="mt-3">เลือกวัน</h5>
            <p class="text-muted">เลือกวันรับและคืนชุดที่สะดวก</p>
          </div>
          <div class="col-md-3 text-center">
            <div class="step-number">3</div>
            <h5 class="mt-3">จองและชำระเงิน</h5>
            <p class="text-muted">วางมัดจำเพื่อยืนยันการจอง</p>
          </div>
          <div class="col-md-3 text-center">
            <div class="step-number">4</div>
            <h5 class="mt-3">รับชุดและสวยปัง</h5>
            <p class="text-muted">รับชุดและสวยในวันพิเศษของคุณ</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.hero-image-placeholder {
  background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-dark) 100%);
  width: 300px;
  height: 300px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

.step-number {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
  color: var(--black-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 auto;
}
</style>
