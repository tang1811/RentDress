<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import api from '../services/api'

const props = defineProps({
  productId: {
    type: [Number, String],
    required: true
  }
})

const emit = defineEmits(['select'])

const currentDate = ref(new Date())
const unavailableDates = ref([])
const selectedStartDate = ref(null)
const selectedEndDate = ref(null)
const loading = ref(false)

const currentMonth = computed(() => currentDate.value.getMonth())
const currentYear = computed(() => currentDate.value.getFullYear())

const monthNames = [
  'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
  'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
]

const dayNames = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส']

const daysInMonth = computed(() => {
  return new Date(currentYear.value, currentMonth.value + 1, 0).getDate()
})

const firstDayOfMonth = computed(() => {
  return new Date(currentYear.value, currentMonth.value, 1).getDay()
})

const calendarDays = computed(() => {
  const days = []
  for (let i = 0; i < firstDayOfMonth.value; i++) {
    days.push({ day: '', empty: true })
  }
  for (let day = 1; day <= daysInMonth.value; day++) {
    const dateStr = formatDateStr(currentYear.value, currentMonth.value + 1, day)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const thisDate = new Date(currentYear.value, currentMonth.value, day)
    days.push({
      day,
      date: dateStr,
      unavailable: unavailableDates.value.includes(dateStr),
      past: thisDate < today,
      selected: isSelected(dateStr),
      inRange: isInRange(dateStr)
    })
  }
  return days
})

const formatDateStr = (year, month, day) => {
  return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
}

const isSelected = (dateStr) => {
  return dateStr === selectedStartDate.value || dateStr === selectedEndDate.value
}

const isInRange = (dateStr) => {
  if (!selectedStartDate.value || !selectedEndDate.value) return false
  return dateStr > selectedStartDate.value && dateStr < selectedEndDate.value
}

const fetchAvailability = async () => {
  loading.value = true
  try {
    const response = await api.checkAvailability(
      props.productId,
      currentMonth.value + 1,
      currentYear.value
    )
    if (response.data.success) {
      unavailableDates.value = response.data.data.unavailable_dates
    }
  } catch (error) {
    console.error('Error fetching availability:', error)
  } finally {
    loading.value = false
  }
}

const prevMonth = () => {
  currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1)
}

const selectDate = (day) => {
  if (day.empty || day.unavailable || day.past) return

  if (!selectedStartDate.value || (selectedStartDate.value && selectedEndDate.value)) {
    selectedStartDate.value = day.date
    selectedEndDate.value = null
    // ✅ FIX: reset emit เมื่อเริ่มเลือกใหม่
    emit('select', { startDate: null, endDate: null })
  } else {
    if (day.date < selectedStartDate.value) {
      selectedEndDate.value = selectedStartDate.value
      selectedStartDate.value = day.date
    } else {
      selectedEndDate.value = day.date
    }

    const hasConflict = unavailableDates.value.some(date =>
      date >= selectedStartDate.value && date <= selectedEndDate.value
    )

    if (hasConflict) {
      alert('มีวันที่ไม่ว่างในช่วงที่เลือก กรุณาเลือกใหม่')
      selectedStartDate.value = null
      selectedEndDate.value = null
      emit('select', { startDate: null, endDate: null })
    } else {
      emit('select', {
        startDate: selectedStartDate.value,
        endDate: selectedEndDate.value
      })
    }
  }
}

// ✅ FIX #3: watch currentDate แทน computed values
watch(currentDate, () => {
  fetchAvailability()
})

onMounted(() => {
  fetchAvailability()
})
</script>

<template>
  <div class="booking-calendar">
    <div class="calendar-header d-flex justify-content-between align-items-center mb-3">
      <button @click="prevMonth" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-chevron-left"></i>
      </button>
      <h5 class="mb-0">{{ monthNames[currentMonth] }} {{ currentYear + 543 }}</h5>
      <button @click="nextMonth" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>

    <div v-if="loading" class="text-center py-4">
      <div class="spinner-border spinner-gold" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else>
      <div class="calendar-weekdays d-flex mb-2">
        <div v-for="day in dayNames" :key="day" class="calendar-weekday text-center">
          {{ day }}
        </div>
      </div>

      <div class="calendar-days d-flex flex-wrap">
        <div
          v-for="(day, index) in calendarDays"
          :key="index"
          class="calendar-day-wrapper"
        >
          <div
            v-if="!day.empty"
            class="calendar-day"
            :class="{
              'unavailable': day.unavailable || day.past,
              'selected': day.selected,
              'in-range': day.inRange
            }"
            @click="selectDate(day)"
          >
            {{ day.day }}
          </div>
        </div>
      </div>
    </div>

    <div class="calendar-legend mt-3">
      <div class="d-flex gap-3 small">
        <div><span class="legend-box available"></span> ว่าง</div>
        <div><span class="legend-box unavailable"></span> ไม่ว่าง</div>
        <div><span class="legend-box selected"></span> เลือก</div>
      </div>
    </div>

    <div v-if="selectedStartDate" class="mt-3 p-3 bg-light rounded">
      <strong>วันที่เลือก:</strong>
      <div><i class="bi bi-calendar-check text-success me-1"></i>รับชุด: {{ selectedStartDate }}</div>
      <div v-if="selectedEndDate"><i class="bi bi-calendar-x text-danger me-1"></i>คืนชุด: {{ selectedEndDate }}</div>
      <div v-else class="text-muted"><i class="bi bi-info-circle me-1"></i>กรุณาเลือกวันคืนชุด</div>
    </div>
  </div>
</template>

<style scoped>
.calendar-weekday,
.calendar-day-wrapper {
  width: calc(100% / 7);
  text-align: center;
}

.calendar-weekday {
  font-weight: 600;
  color: var(--gold-dark);
  padding: 8px 0;
}

.calendar-day-wrapper {
  padding: 4px;
}

.calendar-day {
  width: 36px;
  height: 36px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.2s;
}

.calendar-day:not(.unavailable):hover {
  background-color: var(--gold-light, #f5e6a3);
}

.calendar-day.unavailable {
  background-color: #f0f0f0;
  color: #aaa;
  cursor: not-allowed;
  border-radius: 50%;
}

.calendar-day.selected {
  background-color: var(--gold-primary, #c9a227);
  color: white;
  font-weight: bold;
}

.calendar-day.in-range {
  background-color: var(--gold-light, #f5e6a3);
  border-radius: 0;
}

.legend-box {
  display: inline-block;
  width: 16px;
  height: 16px;
  border-radius: 4px;
  margin-right: 4px;
  vertical-align: middle;
}

.legend-box.available {
  background-color: white;
  border: 1px solid #ccc;
}

.legend-box.unavailable {
  background-color: #f0f0f0;
}

.legend-box.selected {
  background-color: var(--gold-primary, #c9a227);
}
</style>
