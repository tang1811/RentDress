<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  phone: '',
  address: ''
})
const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
  error.value = ''

  if (form.value.password !== form.value.confirmPassword) {
    error.value = 'รหัสผ่านไม่ตรงกัน'
    return
  }

  if (form.value.password.length < 6) {
    error.value = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร'
    return
  }

  loading.value = true

  const result = await authStore.register({
    name: form.value.name,
    email: form.value.email,
    password: form.value.password,
    phone: form.value.phone,
    address: form.value.address
  })

  loading.value = false

  if (result.success) {
    router.push('/')
  } else {
    error.value = result.message
  }
}
</script>

<template>
  <div class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card admin-card">
            <div class="card-body p-5">
              <div class="text-center mb-4">
                <i class="bi bi-gem display-4 text-warning"></i>
                <h2 class="mt-2">สมัครสมาชิก</h2>
                <p class="text-muted">เริ่มต้นเช่าชุดสวยกับ RentDress</p>
              </div>

              <div v-if="error" class="alert alert-danger">
                {{ error }}
              </div>

              <form @submit.prevent="handleSubmit">
                <div class="mb-3">
                  <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="form-control"
                    placeholder="ชื่อ นามสกุล"
                    required
                  >
                </div>

                <div class="mb-3">
                  <label class="form-label">อีเมล <span class="text-danger">*</span></label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="form-control"
                    placeholder="your@email.com"
                    required
                  >
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                    <input
                      v-model="form.password"
                      type="password"
                      class="form-control"
                      placeholder="อย่างน้อย 6 ตัวอักษร"
                      required
                    >
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                    <input
                      v-model="form.confirmPassword"
                      type="password"
                      class="form-control"
                      placeholder="ยืนยันรหัสผ่าน"
                      required
                    >
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">เบอร์โทรศัพท์</label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    class="form-control"
                    placeholder="08X-XXX-XXXX"
                  >
                </div>

                <div class="mb-4">
                  <label class="form-label">ที่อยู่</label>
                  <textarea
                    v-model="form.address"
                    class="form-control"
                    rows="2"
                    placeholder="ที่อยู่สำหรับจัดส่ง"
                  ></textarea>
                </div>

                <button
                  type="submit"
                  class="btn btn-gold btn-lg w-100"
                  :disabled="loading"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  สมัครสมาชิก
                </button>
              </form>

              <hr class="my-4">

              <p class="text-center mb-0">
                มีบัญชีอยู่แล้ว?
                <router-link to="/login" class="text-warning">เข้าสู่ระบบ</router-link>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
