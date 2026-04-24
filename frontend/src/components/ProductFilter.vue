<script setup>
import { reactive } from 'vue'

const emit = defineEmits(['filter', 'clear'])

const filters = reactive({
  category: '',
  size: '',
  color: '',
  minPrice: '',
  maxPrice: '',
  search: ''
})

const categories = [
  { value: '', label: 'ทั้งหมด' },
  { value: 'evening', label: 'ชุดราตรี' },
  { value: 'thai', label: 'ชุดไทย' },
  { value: 'suit', label: 'สูท' },
  { value: 'casual', label: 'ชุดลำลอง' }
]

const sizes = ['', 'S', 'M', 'L', 'XL', 'XXL']

const applyFilters = () => {
  emit('filter', { ...filters })
}

const clearFilters = () => {
  Object.keys(filters).forEach(key => filters[key] = '')
  emit('clear')
}
</script>

<template>
  <div class="card admin-card mb-4">
    <div class="card-body">
      <h5 class="card-title mb-3">ค้นหาและกรอง</h5>
      <div class="row g-3">
        <div class="col-md-4">
          <input
            v-model="filters.search"
            type="text"
            class="form-control"
            placeholder="ค้นหาชื่อชุด..."
            @keyup.enter="applyFilters"
          >
        </div>
        <div class="col-md-2">
          <select v-model="filters.category" class="form-select">
            <option v-for="cat in categories" :key="cat.value" :value="cat.value">
              {{ cat.label }}
            </option>
          </select>
        </div>
        <div class="col-md-2">
          <select v-model="filters.size" class="form-select">
            <option value="">ไซส์ทั้งหมด</option>
            <option v-for="size in sizes.slice(1)" :key="size" :value="size">
              {{ size }}
            </option>
          </select>
        </div>
        <div class="col-md-2">
          <input
            v-model="filters.minPrice"
            type="number"
            class="form-control"
            placeholder="ราคาต่ำสุด"
          >
        </div>
        <div class="col-md-2">
          <input
            v-model="filters.maxPrice"
            type="number"
            class="form-control"
            placeholder="ราคาสูงสุด"
          >
        </div>
      </div>
      <div class="mt-3">
        <button @click="applyFilters" class="btn btn-gold me-2">
          <i class="bi bi-search me-1"></i>ค้นหา
        </button>
        <button @click="clearFilters" class="btn btn-outline-secondary">
          <i class="bi bi-x-circle me-1"></i>ล้างตัวกรอง
        </button>
      </div>
    </div>
  </div>
</template>
