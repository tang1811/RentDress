<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../store'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
  error.value = ''
  loading.value = true

  const result = await authStore.login(email.value, password.value)

  loading.value = false

  if (result.success) {
    const redirect = route.query.redirect || '/'
    router.push(redirect)
  } else {
    error.value = result.message
  }
}
</script>

<template>
  <div class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-5">
          <div class="card admin-card">
            <div class="card-body p-5">
              <div class="text-center mb-4">
                <i class="bi bi-gem display-4 text-warning"></i>
                <h2 class="mt-2">เข้าสู่ระบบ</h2>
                <p class="text-muted">ยินดีต้อนรับกลับสู่ RentDress</p>
              </div>

              <div v-if="error" class="alert alert-danger">
                {{ error }}
              </div>

              <form @submit.prevent="handleSubmit">
                <div class="mb-3">
                  <label class="form-label">อีเมล</label>
                  <input
                    v-model="email"
                    type="email"
                    class="form-control form-control-lg"
                    placeholder="your@email.com"
                    required
                  >
                </div>

                <div class="mb-4">
                  <label class="form-label">รหัสผ่าน</label>
                  <input
                    v-model="password"
                    type="password"
                    class="form-control form-control-lg"
                    placeholder="********"
                    required
                  >
                </div>

                <button
                  type="submit"
                  class="btn btn-gold btn-lg w-100"
                  :disabled="loading"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  เข้าสู่ระบบ
                </button>
              </form>

              <hr class="my-4">

              <p class="text-center mb-0">
                ยังไม่มีบัญชี?
                <router-link to="/register" class="text-warning">สมัครสมาชิก</router-link>
              </p>
            </div>
          </div>

          <div class="text-center mt-4 text-muted small">
            <p class="mb-1">ทดสอบระบบ:</p>
            <p class="mb-0">Admin: admin@rentdress.com / password</p>
            <p>Customer: somchai@email.com / password</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
