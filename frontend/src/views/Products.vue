<script setup>
import { onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useProductStore } from '../store'
import ProductCard from '../components/ProductCard.vue'
import ProductFilter from '../components/ProductFilter.vue'

const route = useRoute()
const productStore = useProductStore()

const handleFilter = async (filters) => {
  await productStore.fetchProducts(filters)
}

const handleClear = async () => {
  productStore.clearFilters()
  await productStore.fetchProducts()
}

onMounted(async () => {
  const filters = {}
  if (route.query.category) {
    filters.category = route.query.category
  }
  await productStore.fetchProducts(filters)
})

watch(() => route.query, async (newQuery) => {
  const filters = {}
  if (newQuery.category) {
    filters.category = newQuery.category
  }
  await productStore.fetchProducts(filters)
})
</script>

<template>
  <div class="py-5">
    <div class="container">
      <h1 class="text-center section-title mb-5">ชุดทั้งหมด</h1>
      <ProductFilter @filter="handleFilter" @clear="handleClear" />

      <div v-if="productStore.loading" class="text-center py-5">
        <div class="spinner-border text-warning" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <div v-else-if="productStore.products.length === 0" class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <p class="mt-3 text-muted">ไม่พบชุดที่ตรงกับเงื่อนไข</p>
      </div>

      <div v-else class="row g-4">
        <div v-for="product in productStore.products" :key="product.id" class="col-md-4 col-lg-3">
          <ProductCard :product="product" />
        </div>
      </div>
    </div>
  </div>
</template>