<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'

const users = ref([])
const loading = ref(false)
const searchQuery = ref('')
const filterRole = ref('all')

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const matchRole = filterRole.value === 'all' || u.role === filterRole.value
    const matchSearch = !searchQuery.value ||
      u.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      u.email?.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchRole && matchSearch
  })
})

const fetchUsers = async () => {
  loading.value = true
  try {
    const response = await api.getUsers()
    if (response.data.success) {
      users.value = response.data.data
    }
  } catch (e) {
    console.error('Error fetching users:', e)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(fetchUsers)
</script>

<template>
  <div>
    <h4 class="mb-4">จัดการผู้ใช้งาน</h4>

    <div class="card admin-card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <input v-model="searchQuery" class="form-control" placeholder="ค้นหาชื่อ หรืออีเมล...">
          </div>
          <div class="col-md-3">
            <select v-model="filterRole" class="form-select">
              <option value="all">ทุก Role</option>
              <option value="customer">Customer</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="card admin-card">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border spinner-gold"></div>
        </div>
        <div v-else-if="filteredUsers.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-people display-4 d-block mb-2"></i>ไม่พบข้อมูลผู้ใช้
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>ชื่อ</th>
                <th>อีเมล</th>
                <th>โทรศัพท์</th>
                <th>Role</th>
                <th>วันที่สมัคร</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in filteredUsers" :key="user.id">
                <td><small class="text-muted">{{ user.id }}</small></td>
                <td class="fw-semibold">{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.phone || '-' }}</td>
                <td>
                  <span class="badge" :class="user.role === 'admin' ? 'bg-warning text-dark' : 'bg-secondary'">
                    {{ user.role }}
                  </span>
                </td>
                <td>{{ formatDate(user.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
