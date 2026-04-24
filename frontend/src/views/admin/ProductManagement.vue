<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const products = ref([])
const loading = ref(true)
const showModal = ref(false)
const editMode = ref(false)
const saving = ref(false)
const uploading = ref(false)

const form = ref({
  id: null,
  name: '',
  description: '',
  category: 'evening',
  size: 'M',
  color: '',
  rental_price: '',
  deposit_price: '',
  image_url: '',
  status: 'available',
  buffer_days: 2
})

// สำหรับจัดการรูปภาพหลายรูป
const productImages = ref([])
const selectedFiles = ref([])
const previewUrls = ref([])

const categories = [
  { value: 'evening', label: 'ชุดราตรี' },
  { value: 'thai', label: 'ชุดไทย' },
  { value: 'suit', label: 'สูท' },
  { value: 'casual', label: 'ชุดลำลอง' }
]

const sizes = ['S', 'M', 'L', 'XL', 'XXL']
const statuses = [
  { value: 'available', label: 'พร้อมให้เช่า' },
  { value: 'rented', label: 'กำลังเช่า' },
  { value: 'maintenance', label: 'ซ่อมบำรุง' }
]

const fetchProducts = async () => {
  loading.value = true
  try {
    const response = await api.getProducts({ status: '' })
    if (response.data.success) {
      products.value = response.data.data
    }
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

const fetchProductImages = async (productId) => {
  try {
    const response = await api.getProductImages(productId)
    if (response.data.success) {
      productImages.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching images:', error)
  }
}

const openCreateModal = () => {
  editMode.value = false
  form.value = {
    id: null,
    name: '',
    description: '',
    category: 'evening',
    size: 'M',
    color: '',
    rental_price: '',
    deposit_price: '',
    image_url: '',
    status: 'available',
    buffer_days: 2
  }
  productImages.value = []
  selectedFiles.value = []
  previewUrls.value = []
  showModal.value = true
}

const openEditModal = async (product) => {
  editMode.value = true
  form.value = { ...product }
  selectedFiles.value = []
  previewUrls.value = []
  showModal.value = true
  await fetchProductImages(product.id)
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  selectedFiles.value = [...selectedFiles.value, ...files]

  // สร้าง preview URLs
  files.forEach(file => {
    const url = URL.createObjectURL(file)
    previewUrls.value.push({
      url,
      name: file.name
    })
  })
}

const removeSelectedFile = (index) => {
  URL.revokeObjectURL(previewUrls.value[index].url)
  selectedFiles.value.splice(index, 1)
  previewUrls.value.splice(index, 1)
}

const uploadSelectedFiles = async (productId) => {
  if (selectedFiles.value.length === 0) return true

  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('product_id', productId)
    selectedFiles.value.forEach(file => {
      formData.append('images[]', file)
    })

    const response = await api.uploadImages(formData)
    if (response.data.success) {
      selectedFiles.value = []
      previewUrls.value = []
      return true
    } else {
      alert('อัปโหลดรูปไม่สำเร็จ: ' + (response.data.message || ''))
      return false
    }
  } catch (error) {
    console.error('Upload error:', error)
    alert('เกิดข้อผิดพลาดในการอัปโหลดรูป')
    return false
  } finally {
    uploading.value = false
  }
}

const deleteImage = async (image) => {
  if (!confirm('ต้องการลบรูปนี้หรือไม่?')) return

  try {
    const response = await api.deleteProductImage(image.id)
    if (response.data.success) {
      productImages.value = productImages.value.filter(img => img.id !== image.id)
    } else {
      alert('ลบรูปไม่สำเร็จ')
    }
  } catch (error) {
    alert('เกิดข้อผิดพลาด')
  }
}

const setAsPrimary = async (image) => {
  try {
    const response = await api.setPrimaryImage(image.id)
    if (response.data.success) {
      productImages.value.forEach(img => {
        img.is_primary = img.id === image.id ? 1 : 0
      })
    }
  } catch (error) {
    alert('เกิดข้อผิดพลาด')
  }
}

const saveProduct = async () => {
  saving.value = true

  try {
    let response
    let productId = form.value.id

    if (editMode.value) {
      response = await api.updateProduct(form.value)
    } else {
      response = await api.createProduct(form.value)
      if (response.data.success) {
        productId = response.data.id
      }
    }

    if (response.data.success) {
      // อัปโหลดรูปภาพที่เลือก
      if (selectedFiles.value.length > 0) {
        await uploadSelectedFiles(productId)
      }

      showModal.value = false
      await fetchProducts()
      alert(editMode.value ? 'อัปเดตสำเร็จ' : 'เพิ่มชุดสำเร็จ')
    } else {
      alert(response.data.message || 'เกิดข้อผิดพลาด')
    }
  } catch (error) {
    alert('เกิดข้อผิดพลาด')
  } finally {
    saving.value = false
  }
}

const deleteProduct = async (product) => {
  if (!confirm(`ต้องการลบ "${product.name}" หรือไม่?`)) return

  try {
    const response = await api.deleteProduct(product.id)
    if (response.data.success) {
      await fetchProducts()
      alert('ลบสำเร็จ')
    } else {
      alert(response.data.message || 'เกิดข้อผิดพลาด')
    }
  } catch (error) {
    alert('เกิดข้อผิดพลาด')
  }
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('th-TH').format(price)
}

const getCategoryLabel = (value) => {
  return categories.find(c => c.value === value)?.label || value
}

const getImageUrl = (url) => {
  if (!url) return ''
  if (url.startsWith('http')) return url
  return url
}

onMounted(() => {
  fetchProducts()
})
</script>

<template>
  <div class="py-4">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>จัดการชุด</h1>
        <button @click="openCreateModal" class="btn btn-gold">
          <i class="bi bi-plus-lg me-2"></i>เพิ่มชุดใหม่
        </button>
      </div>

      <div class="card admin-card">
        <div class="card-body">
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border spinner-gold"></div>
          </div>

          <div v-else-if="products.length === 0" class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-1"></i>
            <p class="mt-2">ยังไม่มีชุด</p>
          </div>

          <div v-else class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>รูป</th>
                  <th>ชื่อชุด</th>
                  <th>หมวดหมู่</th>
                  <th>ไซส์</th>
                  <th>ค่าเช่า</th>
                  <th>มัดจำ</th>
                  <th>สถานะ</th>
                  <th>จัดการ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in products" :key="product.id">
                  <td>
                    <img
                      v-if="product.primary_image"
                      :src="getImageUrl(product.primary_image)"
                      class="rounded"
                      style="width: 60px; height: 60px; object-fit: cover;"
                    >
                    <div v-else class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                      <i class="bi bi-image text-muted"></i>
                    </div>
                  </td>
                  <td>
                    <strong>{{ product.name }}</strong>
                    <br><small class="text-muted">{{ product.color }}</small>
                  </td>
                  <td>{{ getCategoryLabel(product.category) }}</td>
                  <td>{{ product.size }}</td>
                  <td>฿{{ formatPrice(product.rental_price) }}</td>
                  <td>฿{{ formatPrice(product.deposit_price) }}</td>
                  <td>
                    <span :class="product.status === 'available' ? 'text-success fw-bold' : 'text-danger fw-bold'">
                      {{ product.status === 'available' ? 'พร้อม' : product.status === 'rented' ? 'เช่าอยู่' : 'ซ่อม' }}
                    </span>
                  </td>
                  <td>
                    <button @click="openEditModal(product)" class="btn btn-sm btn-outline-primary me-1">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button @click="deleteProduct(product)" class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal d-block" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editMode ? 'แก้ไขชุด' : 'เพิ่มชุดใหม่' }}</h5>
            <button @click="showModal = false" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveProduct">
              <div class="row">
                <div class="col-md-8 mb-3">
                  <label class="form-label">ชื่อชุด *</label>
                  <input v-model="form.name" type="text" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">หมวดหมู่</label>
                  <select v-model="form.category" class="form-select">
                    <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                      {{ cat.label }}
                    </option>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">รายละเอียด</label>
                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">ไซส์</label>
                  <select v-model="form.size" class="form-select">
                    <option v-for="size in sizes" :key="size" :value="size">{{ size }}</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">สี</label>
                  <input v-model="form.color" type="text" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">สถานะ</label>
                  <select v-model="form.status" class="form-select">
                    <option v-for="status in statuses" :key="status.value" :value="status.value">
                      {{ status.label }}
                    </option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">ค่าเช่า/วัน (บาท) *</label>
                  <input v-model.number="form.rental_price" type="number" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">มัดจำ (บาท) *</label>
                  <input v-model.number="form.deposit_price" type="number" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Buffer Days</label>
                  <input v-model.number="form.buffer_days" type="number" class="form-control" min="0">
                </div>
              </div>

              <!-- ส่วนอัปโหลดรูปภาพ -->
              <div class="mb-3">
                <label class="form-label">รูปภาพชุด</label>

                <!-- รูปที่อัปโหลดแล้ว (แสดงเฉพาะตอน edit) -->
                <div v-if="editMode && productImages.length > 0" class="mb-3">
                  <small class="text-muted d-block mb-2">รูปปัจจุบัน ({{ productImages.length }} รูป)</small>
                  <div class="d-flex flex-wrap gap-2">
                    <div v-for="image in productImages" :key="image.id" class="position-relative image-item">
                      <img :src="getImageUrl(image.image_url)" class="rounded" style="width: 100px; height: 100px; object-fit: cover;">
                      <span v-if="image.is_primary == 1" class="badge bg-success position-absolute" style="top: 2px; left: 2px; font-size: 10px;">
                        หลัก
                      </span>
                      <div class="image-actions position-absolute" style="top: 2px; right: 2px;">
                        <button v-if="image.is_primary != 1" @click.prevent="setAsPrimary(image)" class="btn btn-sm btn-light" title="ตั้งเป็นรูปหลัก">
                          <i class="bi bi-star"></i>
                        </button>
                        <button @click.prevent="deleteImage(image)" class="btn btn-sm btn-danger" title="ลบ">
                          <i class="bi bi-x"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- เลือกไฟล์ใหม่ -->
                <div class="upload-area p-3 border border-dashed rounded text-center" @click="$refs.fileInput.click()" style="cursor: pointer; border-style: dashed !important;">
                  <i class="bi bi-cloud-arrow-up display-6 text-muted"></i>
                  <p class="mb-0 text-muted">คลิกเพื่อเลือกรูปภาพ หรือลากไฟล์มาวาง</p>
                  <small class="text-muted">รองรับ JPG, PNG, GIF, WebP (สูงสุด 5MB ต่อรูป)</small>
                </div>
                <input
                  ref="fileInput"
                  type="file"
                  accept="image/jpeg,image/png,image/gif,image/webp"
                  multiple
                  class="d-none"
                  @change="handleFileSelect"
                >

                <!-- Preview รูปที่เลือก -->
                <div v-if="previewUrls.length > 0" class="mt-3">
                  <small class="text-muted d-block mb-2">รูปใหม่ที่จะอัปโหลด ({{ previewUrls.length }} รูป)</small>
                  <div class="d-flex flex-wrap gap-2">
                    <div v-for="(preview, index) in previewUrls" :key="index" class="position-relative">
                      <img :src="preview.url" class="rounded" style="width: 100px; height: 100px; object-fit: cover;">
                      <button @click.prevent="removeSelectedFile(index)" class="btn btn-sm btn-danger position-absolute" style="top: 2px; right: 2px;">
                        <i class="bi bi-x"></i>
                      </button>
                      <small class="d-block text-truncate" style="max-width: 100px;">{{ preview.name }}</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- URL รูปภาพเก่า (ซ่อนไว้แต่ยังเก็บค่า) -->
              <input type="hidden" v-model="form.image_url">
            </form>
          </div>
          <div class="modal-footer">
            <button @click="showModal = false" class="btn btn-secondary">ยกเลิก</button>
            <button @click="saveProduct" class="btn btn-gold" :disabled="saving || uploading">
              <span v-if="saving || uploading" class="spinner-border spinner-border-sm me-1"></span>
              {{ uploading ? 'กำลังอัปโหลด...' : (editMode ? 'บันทึก' : 'เพิ่มชุด') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.image-item:hover .image-actions {
  opacity: 1;
}

.image-actions {
  opacity: 0;
  transition: opacity 0.2s;
}

.image-item:hover .image-actions,
.image-actions:focus-within {
  opacity: 1;
}

.upload-area {
  transition: all 0.2s;
}

.upload-area:hover {
  background-color: #f8f9fa;
  border-color: #c9a227 !important;
}
</style>
