<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const props = defineProps({
  productId: {
    type: [Number, String],
    required: true
  }
})

const reviews = ref([])
const avgRating = ref(0)
const loading = ref(false)

const fetchReviews = async () => {
  loading.value = true
  try {
    const response = await api.getReviews(props.productId)
    if (response.data.success) {
      reviews.value = response.data.data
      avgRating.value = response.data.avg_rating
    }
  } catch (error) {
    console.error('Error fetching reviews:', error)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(() => {
  fetchReviews()
})
</script>

<template>
  <div class="review-list">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4>รีวิวจากลูกค้า</h4>
      <div v-if="reviews.length > 0" class="rating-summary">
        <span class="rating-stars">
          <i class="bi bi-star-fill" v-for="i in Math.floor(avgRating)" :key="i"></i>
          <i class="bi bi-star-half" v-if="avgRating % 1 >= 0.5"></i>
        </span>
        <span class="ms-2">{{ avgRating.toFixed(1) }} ({{ reviews.length }} รีวิว)</span>
      </div>
    </div>

    <div v-if="loading" class="text-center py-4">
      <div class="spinner-border spinner-gold" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="reviews.length === 0" class="text-center py-4 text-muted">
      <i class="bi bi-chat-square-text fs-1"></i>
      <p class="mt-2">ยังไม่มีรีวิว</p>
    </div>

    <div v-else>
      <div v-for="review in reviews" :key="review.id" class="review-item card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <strong>{{ review.user_name }}</strong>
              <div class="rating-stars small">
                <i class="bi bi-star-fill" v-for="i in review.rating" :key="i"></i>
                <i class="bi bi-star" v-for="i in (5 - review.rating)" :key="'e'+i"></i>
              </div>
            </div>
            <small class="text-muted">{{ formatDate(review.created_at) }}</small>
          </div>
          <p class="mb-0">{{ review.comment }}</p>
          <img
            v-if="review.image_url"
            :src="review.image_url"
            alt="Review image"
            class="review-image mt-2"
          >
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.review-image {
  max-width: 200px;
  border-radius: 8px;
}

.review-item {
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}
</style>
