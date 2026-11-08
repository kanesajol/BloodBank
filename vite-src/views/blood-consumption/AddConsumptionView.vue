<template>
  <ModulePage
    title="Consumption Records"
    description="Record and search blood consumption by location, blood type, number of bags, reporting period, and date submitted. No patient-identifying information is required."
  >
    <div class="consumption-module">
      <!-- ===================== SEARCH / LIST RECORDS ===================== -->
      <section class="panel">
        <div class="panel-header">
          <h3 class="panel-title">Search Consumption Records</h3>
          <button type="button" class="btn-primary btn-add" @click="openModal">
            <i class="ti ti-plus"></i> Add Consumption Record
          </button>
        </div>
        <div class="consumption-search">
          <div class="filters-bar">
            <div class="search-input-wrap">
              <i class="ti ti-map-pin search-icon"></i>
              <input v-model="filters.location" type="text" placeholder="Location (e.g. Tubajon)..." class="search-input" />
            </div>

            <select v-model="filters.bloodType" class="filter-select">
              <option value="">All Blood Types</option>
              <option v-for="bt in bloodTypes" :key="bt" :value="bt">{{ bt }}</option>
            </select>

            <select v-model="filters.month" class="filter-select">
              <option value="">All Months</option>
              <option v-for="(m, idx) in monthNames" :key="m" :value="idx + 1">{{ m }}</option>
            </select>

            <select v-model="filters.year" class="filter-select">
              <option value="">All Years</option>
              <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
            </select>

            <button class="reset-btn" @click="resetFilters">
              <i class="ti ti-refresh"></i> Reset
            </button>
          </div>

          <!-- Summary totals -->
          <div class="summary-grid">
            <div class="summary-card">
              <span>Total Bags</span>
              <strong>{{ totalBags }}</strong>
            </div>
            <div class="summary-card" v-for="bt in totalsByBloodType" :key="bt.type">
              <span>{{ bt.type }}</span>
              <strong>{{ bt.total }}</strong>
            </div>
          </div>

          <p class="results-count">
            {{ filteredRecords.length }} record{{ filteredRecords.length !== 1 ? 's' : '' }}
            <span v-if="filters.location"> for "{{ filters.location }}"</span>
            <span v-if="filters.year"> in {{ filters.year }}</span>
          </p>

          <div class="table-wrap">
            <table class="records-table">
              <thead>
                <tr>
                  <th>Month</th>
                  <th>Blood Type</th>
                  <th>Location</th>
                  <th>Number of Bags</th>
                  <th>Date Submitted</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="filteredRecords.length === 0">
                  <td colspan="5" class="empty-state">No consumption records match your filters.</td>
                </tr>
                <tr v-for="rec in filteredRecords" :key="rec.id">
                  <td>{{ monthNames[rec.month - 1] }}</td>
                  <td><span class="blood-type-badge">{{ rec.bloodType }}</span></td>
                  <td>{{ rec.location }}</td>
                  <td>{{ rec.numberOfBags }}</td>
                  <td>{{ formatDate(rec.dateSubmitted) }}</td>
                </tr>
              </tbody>
              <tfoot v-if="filteredRecords.length">
                <tr>
                  <td colspan="3" class="total-label">Total</td>
                  <td class="total-value">{{ totalBags }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </section>

      <!-- ===================== ADD RECORD MODAL ===================== -->
      <Teleport to="body">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="addRecordModalTitle">
            <div class="modal-header">
              <h3 id="addRecordModalTitle" class="panel-title">Add Consumption Record</h3>
              <button type="button" class="modal-close" @click="closeModal" aria-label="Close">
                <i class="ti ti-x"></i>
              </button>
            </div>

            <form class="consumption-form" @submit.prevent="submitRecord">
              <div class="form-grid">
                <div class="form-field">
                  <label for="location">Location</label>
                  <input
                    id="location"
                    v-model="form.location"
                    type="text"
                    placeholder="e.g. Tubajon"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="bloodType">Blood Type</label>
                  <select id="bloodType" v-model="form.bloodType" required>
                    <option value="" disabled>Select blood type</option>
                    <option v-for="bt in bloodTypes" :key="bt" :value="bt">{{ bt }}</option>
                  </select>
                </div>

                <div class="form-field">
                  <label for="bags">Number of Bags</label>
                  <input
                    id="bags"
                    v-model.number="form.numberOfBags"
                    type="number"
                    min="1"
                    step="1"
                    placeholder="e.g. 10"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="reportMonth">Reporting Period</label>
                  <div class="period-inputs">
                    <select id="reportMonth" v-model="form.reportingMonth" required>
                      <option value="" disabled>Month</option>
                      <option v-for="(m, idx) in monthNames" :key="m" :value="idx + 1">{{ m }}</option>
                    </select>
                    <select v-model="form.reportingYear" required>
                      <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                    </select>
                  </div>
                </div>

                <div class="form-field">
                  <label for="dateSubmitted">Date Submitted</label>
                  <input
                    id="dateSubmitted"
                    v-model="form.dateSubmitted"
                    type="date"
                    required
                  />
                </div>
              </div>

              <div class="form-actions">
                <button type="button" class="btn-secondary" @click="resetForm">Clear</button>
                <button type="submit" class="btn-primary" :disabled="submitting">
                  {{ submitting ? 'Saving...' : 'Save Record' }}
                </button>
              </div>

              <p v-if="successMessage" class="success-message">
                <i class="ti ti-check"></i> {{ successMessage }}
              </p>
              <p v-if="errorMessage" class="error-message">
                <i class="ti ti-x"></i> {{ errorMessage }}
              </p>
            </form>
          </div>
        </div>
      </Teleport>
    </div>
  </ModulePage>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import ModulePage from '../../components/dashboard/ModulePage.vue'
import {
  consumptionRecords as records,
  bloodTypes,
  monthNames,
  addConsumptionRecord,
} from '../../stores/bloodBankStore'

const currentYear = new Date().getFullYear()
const yearOptions = [currentYear - 1, currentYear, currentYear + 1]

function todayISO() {
  return new Date().toISOString().slice(0, 10)
}

// ===================== Add record form =====================
const form = reactive({
  location: '',
  bloodType: '',
  numberOfBags: null,
  reportingMonth: new Date().getMonth() + 1,
  reportingYear: currentYear,
  dateSubmitted: todayISO(),
})

const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// ===================== Modal control =====================
const showModal = ref(false)

function openModal() {
  resetForm()
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  resetForm()
}

function resetForm() {
  form.location = ''
  form.bloodType = ''
  form.numberOfBags = null
  form.reportingMonth = new Date().getMonth() + 1
  form.reportingYear = currentYear
  form.dateSubmitted = todayISO()
  successMessage.value = ''
  errorMessage.value = ''
}

async function submitRecord() {
  errorMessage.value = ''
  successMessage.value = ''
  submitting.value = true

  const payload = {
    location: form.location,
    blood_type: form.bloodType,
    number_of_bags: form.numberOfBags,
    reporting_period: `${monthNames[form.reportingMonth - 1]} ${form.reportingYear}`,
    reporting_month: form.reportingMonth,
    reporting_year: form.reportingYear,
    date_submitted: form.dateSubmitted,
  }

  try {
    // TODO: replace with your Laravel endpoint
    // const { data: saved } = await axios.post('/api/consumption-records', payload)
    console.log('Submitting consumption record:', payload)

    addConsumptionRecord(payload)

    successMessage.value = `Record saved: ${payload.number_of_bags} bags of ${payload.blood_type} for ${payload.location} (${payload.reporting_period}).`
    resetFormKeepMessage()

    // Give the user a moment to see the confirmation, then close the modal.
    setTimeout(() => {
      showModal.value = false
      successMessage.value = ''
    }, 1200)
  } catch (err) {
    errorMessage.value = 'Could not save the record. Please try again.'
  } finally {
    submitting.value = false
  }
}

function resetFormKeepMessage() {
  form.location = ''
  form.bloodType = ''
  form.numberOfBags = null
  form.reportingMonth = new Date().getMonth() + 1
  form.reportingYear = currentYear
  form.dateSubmitted = todayISO()
}

// ===================== Search / filter records =====================
const filters = reactive({
  location: '',
  bloodType: '',
  month: '',
  year: '',
})

function resetFilters() {
  filters.location = ''
  filters.bloodType = ''
  filters.month = ''
  filters.year = ''
}

const filteredRecords = computed(() => {
  return records.value
    .filter((r) => !filters.location || r.location.toLowerCase().includes(filters.location.toLowerCase()))
    .filter((r) => !filters.bloodType || r.bloodType === filters.bloodType)
    .filter((r) => !filters.month || r.month === Number(filters.month))
    .filter((r) => !filters.year || r.year === Number(filters.year))
    .sort((a, b) => a.month - b.month)
})

const totalBags = computed(() =>
  filteredRecords.value.reduce((sum, r) => sum + r.numberOfBags, 0)
)

const totalsByBloodType = computed(() => {
  const map = {}
  filteredRecords.value.forEach((r) => {
    map[r.bloodType] = (map[r.bloodType] || 0) + r.numberOfBags
  })
  return Object.entries(map).map(([type, total]) => ({ type, total }))
})

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}
</script>

<style scoped>
.consumption-module {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.panel {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.panel-title {
  margin: 0;
  font-size: 15px;
  font-weight: 700;
  color: #333;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  border-bottom: 1px solid #eee;
  padding-bottom: 8px;
}

.btn-add {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}

/* ===================== Modal ===================== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 1000;
}

.modal-card {
  background: #fff;
  border-radius: 10px;
  padding: 1.5rem;
  width: 100%;
  max-width: 560px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 1rem;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
}

.modal-close {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  color: #888;
  line-height: 1;
  padding: 4px;
  border-radius: 4px;
}
.modal-close:hover {
  background: #f5f5f5;
  color: #444;
}

.modal-card .consumption-form {
  max-width: none;
}

.consumption-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  max-width: 520px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem 1.25rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-field label {
  font-size: 13px;
  font-weight: 600;
  color: #444;
}

.form-field input,
.form-field select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

.period-inputs {
  display: flex;
  gap: 8px;
}
.period-inputs select {
  flex: 1;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.btn-secondary,
.btn-primary {
  padding: 8px 18px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  border: 1px solid #ddd;
}

.btn-secondary {
  background: #fff;
  color: #444;
}
.btn-secondary:hover {
  background: #f5f5f5;
}

.btn-primary {
  background: #c0392b;
  border-color: #c0392b;
  color: #fff;
}
.btn-primary:hover {
  background: #a5301f;
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.success-message {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #1e7e34;
  font-size: 13px;
  background: #eaf7ec;
  padding: 8px 12px;
  border-radius: 6px;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #c0392b;
  font-size: 13px;
  background: #fdecec;
  padding: 8px 12px;
  border-radius: 6px;
}

.consumption-search {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.filters-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  align-items: center;
}

.search-input-wrap {
  position: relative;
  flex: 1;
  min-width: 200px;
}
.search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #888;
  font-size: 16px;
}
.search-input {
  width: 100%;
  padding: 8px 12px 8px 34px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  background: #fff;
}

.reset-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: #fff;
  cursor: pointer;
  font-size: 14px;
}
.reset-btn:hover {
  background: #f5f5f5;
}

.summary-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.summary-card {
  background: #fafafa;
  border-radius: 8px;
  padding: 10px 16px;
  min-width: 100px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.summary-card span {
  font-size: 12px;
  color: #888;
}
.summary-card strong {
  font-size: 20px;
  color: #c0392b;
}

.results-count {
  font-size: 13px;
  color: #666;
  margin: 0;
}

.table-wrap {
  overflow-x: auto;
  border: 1px solid #eee;
  border-radius: 8px;
}

.records-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.records-table th {
  text-align: left;
  padding: 10px 12px;
  background: #fafafa;
  font-weight: 600;
  color: #444;
  border-bottom: 1px solid #eee;
  white-space: nowrap;
}

.records-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #f0f0f0;
}

.records-table tfoot td {
  padding: 10px 12px;
  border-top: 2px solid #eee;
  background: #fafafa;
}
.total-label {
  text-align: right;
  font-weight: 600;
  color: #444;
}
.total-value {
  font-weight: 700;
  color: #c0392b;
}

.blood-type-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  background: #fdecec;
  color: #c0392b;
  font-weight: 600;
  font-size: 12px;
}

.empty-state {
  text-align: center;
  color: #999;
  padding: 24px;
}
</style>