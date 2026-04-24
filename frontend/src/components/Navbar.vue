<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store'

const router = useRouter()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isAdmin = computed(() => authStore.isAdmin)
const user = computed(() => authStore.currentUser)

const logout = () => {
  authStore.logout()
  router.push('/')
}
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
      <router-link class="navbar-brand" to="/">
        <i class="bi bi-gem me-2"></i>RentDress
      </router-link>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <router-link class="nav-link" to="/">หน้าแรก</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/products">ชุดทั้งหมด</router-link>
          </li>
        </ul>

        <ul class="navbar-nav">
          <template v-if="isAuthenticated">
            <li class="nav-item dropdown" v-if="isAdmin">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                Admin
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><router-link class="dropdown-item" to="/admin">Dashboard</router-link></li>
                <li><router-link class="dropdown-item" to="/admin/products">จัดการชุด</router-link></li>
                <li><router-link class="dropdown-item" to="/admin/bookings">จัดการการจอง</router-link></li>
                <li><router-link class="dropdown-item" to="/admin/calendar">ปฏิทินการจอง</router-link></li>
              </ul>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                {{ user?.name }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><router-link class="dropdown-item" to="/profile">โปรไฟล์</router-link></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" @click.prevent="logout">ออกจากระบบ</a></li>
              </ul>
            </li>
          </template>
          <template v-else>
            <li class="nav-item">
              <router-link class="nav-link" to="/login">เข้าสู่ระบบ</router-link>
            </li>
            <li class="nav-item">
              <router-link class="btn btn-gold btn-sm ms-2" to="/register">สมัครสมาชิก</router-link>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.navbar-toggler {
  border-color: var(--gold-primary);
}

.navbar-toggler-icon {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23D4AF37' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

.dropdown-menu {
  background-color: var(--black-secondary);
  border: 1px solid var(--gold-primary);
}

.dropdown-item {
  color: white;
}

.dropdown-item:hover {
  background-color: var(--gold-primary);
  color: var(--black-primary);
}

.dropdown-divider {
  border-color: var(--gold-primary);
}
</style>
