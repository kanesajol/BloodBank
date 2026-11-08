<template>
  <ModulePage
    title="Blood Collection Management"
    description="Record, search, and manage blood donor profiles and donation histories. Register first-time donors or record new donations for returning donors."
  >
    <div class="search-donor">
      <!-- ===================== TOP HEADER ACTION BAR ===================== -->
      <div class="collection-header-bar">
        <!-- Mode Switcher Tabs -->
        <div class="mode-tabs">
          <button
            v-for="mode in modes"
            :key="mode.value"
            type="button"
            :class="['mode-tab', { active: searchMode === mode.value }]"
            @click="switchMode(mode.value)"
          >
            <i :class="mode.icon"></i>
            {{ mode.label }}
          </button>
        </div>

        <button type="button" class="btn-register-donor" @click="openRegisterModal('new')">
          <i class="ti ti-user-plus"></i> Register / Returning Donor
        </button>
      </div>

      <!-- ==================================================================== -->
      <!-- 1. MODE: BY NAME (SEARCH DONOR)                                      -->
      <!-- ==================================================================== -->
      <div v-if="searchMode === 'name'" class="mode-panel">
        <div class="search-bar-wrap">
          <div class="search-input-wrap">
            <i class="ti ti-search search-icon"></i>
            <input
              v-model="nameQuery"
              type="text"
              placeholder="Search donor by full name (e.g. Juan Dela Cruz)..."
              class="search-input"
            />
          </div>
          <button v-if="nameQuery" type="button" class="btn-clear" @click="nameQuery = ''">Clear</button>
        </div>

        <p class="results-count">
          {{ nameResults.length }} donor{{ nameResults.length !== 1 ? 's' : '' }} found
          <span v-if="nameQuery"> for "{{ nameQuery }}"</span>
        </p>

        <div class="table-wrap">
          <table class="donor-table">
            <thead>
              <tr>
                <th>Full Name</th>
                <th>Blood Type</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Barangay</th>
                <th>Municipality / Location</th>
                <th>Contact Information</th>
                <th>Total Donations</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="nameResults.length === 0">
                <td colspan="9" class="empty-state">No donors match your search name.</td>
              </tr>
              <tr
                v-for="donor in nameResults"
                :key="donor.id"
                class="donor-row"
                @click="openDonor(donor.id)"
              >
                <td class="donor-name-link">
                  <strong>{{ donor.fullName }}</strong>
                </td>
                <td><span class="blood-type-badge">{{ donor.bloodType }}</span></td>
                <td>{{ donor.age }}</td>
                <td><span :class="['gender-tag', donor.gender.toLowerCase()]">{{ donor.gender }}</span></td>
                <td>{{ donor.barangay }}</td>
                <td>{{ donor.municipality }}</td>
                <td>{{ donor.contact }}</td>
                <td><strong class="text-red">{{ donor.totalDonations }}</strong></td>
                <td>
                  <button type="button" class="btn-view-profile" @click.stop="openDonor(donor.id)">
                    <i class="ti ti-eye"></i> View Profile
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ==================================================================== -->
      <!-- 2. MODE: BY LOCATION                                                 -->
      <!-- ==================================================================== -->
      <div v-else-if="searchMode === 'location'" class="mode-panel">
        <div class="search-bar-wrap">
          <div class="search-input-wrap">
            <i class="ti ti-map-pin search-icon"></i>
            <input
              v-model="locationQuery"
              type="text"
              placeholder="Search by barangay, municipality, or location (e.g. Tubajon)..."
              class="search-input"
            />
          </div>
          <button v-if="locationQuery" type="button" class="btn-clear" @click="locationQuery = ''">Clear</button>
        </div>

        <div class="quick-location-chips">
          <span class="chip-label">Quick Locations:</span>
          <button
            v-for="loc in popularLocations"
            :key="loc"
            type="button"
            :class="['location-chip', { active: locationQuery.toLowerCase() === loc.toLowerCase() }]"
            @click="locationQuery = loc"
          >
            {{ loc }}
          </button>
        </div>

        <div v-if="locationQuery">
          <p class="results-count">
            {{ locationResults.length }} donor{{ locationResults.length !== 1 ? 's' : '' }} associated with "<strong>{{ locationQuery }}</strong>":
          </p>

          <div class="location-cards-grid">
            <div
              v-for="donor in locationResults"
              :key="donor.id"
              class="location-donor-card"
              @click="openDonor(donor.id)"
            >
              <div class="card-top">
                <strong class="donor-name-link">{{ donor.fullName }}</strong>
                <span class="blood-type-badge">{{ donor.bloodType }}</span>
              </div>
              <div class="card-meta">
                <span>{{ donor.age }} yrs · {{ donor.gender }}</span>
                <span>{{ donor.barangay }}, {{ donor.municipality }}</span>
              </div>
              <div class="card-bottom">
                <span class="donation-count-text">
                  <i class="ti ti-droplet"></i> {{ donor.totalDonations }} total donation{{ donor.totalDonations !== 1 ? 's' : '' }}
                </span>
                <span class="click-hint">View history <i class="ti ti-chevron-right"></i></span>
              </div>
            </div>
            <div v-if="locationResults.length === 0" class="empty-state card-empty">
              No donors found for location "{{ locationQuery }}".
            </div>
          </div>
        </div>
        <p v-else class="hint-text">
          Enter a barangay or municipality name (or click a quick location chip above) to view all associated donors.
        </p>
      </div>

      <!-- ==================================================================== -->
      <!-- 3. MODE: BY LOCATION & YEAR                                          -->
      <!-- ==================================================================== -->
      <div v-else-if="searchMode === 'location-year'" class="mode-panel">
        <div class="filters-bar">
          <div class="search-input-wrap">
            <i class="ti ti-map-pin search-icon"></i>
            <input
              v-model="locYearLocation"
              type="text"
              placeholder="Filter location (e.g. Tubajon)..."
              class="search-input"
            />
          </div>
          <div class="filter-year-wrap">
            <label>Year:</label>
            <select v-model="locYearYear" class="filter-select">
              <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>
        </div>

        <div v-if="locYearLocation" class="month-groups">
          <div v-if="monthGroups.length === 0" class="empty-state">
            No donation records found for <strong>{{ locYearLocation }}</strong> in <strong>{{ locYearYear }}</strong>.
          </div>

          <div v-for="group in monthGroups" :key="group.month" class="month-group">
            <div class="month-title">
              <span><i class="ti ti-calendar"></i> {{ group.month }} {{ locYearYear }}</span>
              <span class="badge">{{ group.records.length }} Donation{{ group.records.length !== 1 ? 's' : '' }}</span>
            </div>

            <ul class="record-list">
              <li
                v-for="(record, idx) in group.records"
                :key="idx"
                class="record-item"
                @click="openDonor(record.donorId)"
              >
                <div class="record-donor-info">
                  <span class="donor-name-link">{{ record.donorName }}</span>
                  <span class="blood-type-badge sm">{{ record.bloodType }}</span>
                  <span class="record-sub">{{ record.location }}</span>
                </div>
                <div class="record-date-badge">
                  <i class="ti ti-calendar-event"></i>
                  <strong>{{ record.formattedExactDate }}</strong>
                </div>
              </li>
            </ul>
          </div>
        </div>
        <p v-else class="hint-text">Enter a location to view donation records organized month-by-month (Jan–Dec) for {{ locYearYear }}.</p>
      </div>



      <!-- ==================================================================== -->
      <!-- DONOR DETAILS MODAL (WITH ADD NEW DONATION FEATURE)                  -->
      <!-- ==================================================================== -->
      <div v-if="selectedDonor" class="modal-backdrop" @click.self="closeDonor">
        <div class="modal-card">
          <button class="modal-close" @click="closeDonor" aria-label="Close">
            <i class="ti ti-x"></i>
          </button>

          <div class="modal-header">
            <div>
              <h3 class="modal-name">{{ selectedDonor.fullName }}</h3>
              <span class="modal-sub">{{ selectedDonor.barangay }}, {{ selectedDonor.municipality }}</span>
            </div>
            <span class="blood-type-badge lg">{{ selectedDonor.bloodType }}</span>
          </div>

          <div class="modal-grid">
            <div class="modal-field"><span>Age:</span><strong>{{ selectedDonor.age }} yrs old</strong></div>
            <div class="modal-field"><span>Gender:</span><strong>{{ selectedDonor.gender }}</strong></div>
            <div class="modal-field"><span>Contact:</span><strong>{{ selectedDonor.contact }}</strong></div>
            <div class="modal-field"><span>Registered Date:</span><strong>{{ formatDate(selectedDonor.registeredDate) }}</strong></div>
            <div class="modal-field"><span>Barangay:</span><strong>{{ selectedDonor.barangay }}</strong></div>
            <div class="modal-field"><span>Municipality / Location:</span><strong>{{ selectedDonor.municipality }}</strong></div>
          </div>

          <div class="modal-total-banner">
            <div>
              <span>Total Number of Donations:</span>
              <strong class="total-count-val">{{ selectedDonor.donationHistory.length }}</strong>
            </div>
            <button
              type="button"
              class="btn-add-donation-toggle"
              @click="showAddDonationForm = !showAddDonationForm"
            >
              <i class="ti ti-plus"></i> {{ showAddDonationForm ? 'Close Form' : 'Add New Donation' }}
            </button>
          </div>

          <!-- Add New Donation Form (Returning Donor) -->
          <div v-if="showAddDonationForm" class="add-donation-panel">
            <h4 class="form-subheading"><i class="ti ti-droplet"></i> Add New Donation for {{ selectedDonor.fullName }}</h4>
            <form class="mini-donation-form" @submit.prevent="submitNewDonation">
              <div class="form-row">
                <div class="form-field">
                  <label for="newDonationDate">Donation Date</label>
                  <input id="newDonationDate" v-model="newDonation.date" type="date" required />
                </div>
                <div class="form-field">
                  <label for="newDonationLocation">Location / Facility</label>
                  <input id="newDonationLocation" v-model="newDonation.location" type="text" placeholder="e.g. Tubajon" required />
                </div>
              </div>
              <div class="form-actions-inline">
                <button type="button" class="btn-cancel-sm" @click="showAddDonationForm = false">Cancel</button>
                <button type="submit" class="btn-submit-sm">Save Donation Record</button>
              </div>
            </form>
          </div>

          <!-- Donation History Table/List -->
          <h4 class="modal-history-title">Complete Donation History</h4>
          <div class="history-table-wrap">
            <table class="history-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Blood Type</th>
                  <th>Location</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(rec, idx) in sortedHistory(selectedDonor)"
                  :key="idx"
                  :class="{ 'row-new-update': isLatestDonation(selectedDonor, rec) }"
                >
                  <td>
                    <strong>{{ formatDate(rec.date) }}</strong>
                    <span v-if="isLatestDonation(selectedDonor, rec)" class="new-badge">new update</span>
                  </td>
                  <td><span class="blood-type-badge sm">{{ rec.bloodType || selectedDonor.bloodType }}</span></td>
                  <td>{{ rec.location || selectedDonor.barangay }}</td>
                  <td><span class="status-pill verified">Completed</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================================================================== -->
      <!-- MODAL: REGISTER / RETURNING DONOR                                    -->
      <!-- ==================================================================== -->
      <Teleport to="body">
        <div v-if="showRegisterModal" class="modal-backdrop" @click.self="closeRegisterModal">
          <div class="modal-card register-modal-card">
            <button type="button" class="modal-close" @click="closeRegisterModal" aria-label="Close">
              <i class="ti ti-x"></i>
            </button>

            <div class="modal-header">
              <div class="modal-header-icon-wrap">
                <i :class="registerModalTab === 'new' ? 'ti ti-user-plus' : 'ti ti-refresh'"></i>
              </div>
              <div>
                <h3 class="modal-name">
                  {{ registerModalTab === 'new' ? 'Register First-Time Donor' : 'Record Returning Donor Donation' }}
                </h3>
                <p class="modal-sub">
                  {{ registerModalTab === 'new' ? 'Record donor profile and initial donation details.' : 'Search an existing donor to add a new donation to their profile.' }}
                </p>
              </div>
            </div>

            <!-- Modal Tab Switcher -->
            <div class="modal-tab-switch">
              <button
                type="button"
                :class="['modal-tab-btn', { active: registerModalTab === 'new' }]"
                @click="registerModalTab = 'new'"
              >
                <i class="ti ti-user-plus"></i> New / First-Time Donor
              </button>
              <button
                type="button"
                :class="['modal-tab-btn', { active: registerModalTab === 'returning' }]"
                @click="registerModalTab = 'returning'"
              >
                <i class="ti ti-refresh"></i> Returning Donor
              </button>
            </div>

            <!-- 1. New Donor Form -->
            <form v-if="registerModalTab === 'new'" class="register-form" @submit.prevent="handleRegisterDonor">
              <div class="form-grid">
                <div class="form-field full-span">
                  <label for="regFullName">Full Name</label>
                  <input
                    id="regFullName"
                    v-model="regForm.fullName"
                    type="text"
                    placeholder="e.g. Juan Dela Cruz"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="regBloodType">Blood Type</label>
                  <select id="regBloodType" v-model="regForm.bloodType" required>
                    <option value="" disabled>Select Blood Type</option>
                    <option v-for="bt in bloodTypes" :key="bt" :value="bt">{{ bt }}</option>
                  </select>
                </div>

                <div class="form-field">
                  <label for="regAge">Age</label>
                  <input
                    id="regAge"
                    v-model.number="regForm.age"
                    type="number"
                    min="16"
                    max="70"
                    placeholder="e.g. 34"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="regGender">Gender</label>
                  <select id="regGender" v-model="regForm.gender" required>
                    <option value="" disabled>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>

                <div class="form-field">
                  <label for="regContact">Contact Information</label>
                  <input
                    id="regContact"
                    v-model="regForm.contact"
                    type="tel"
                    placeholder="e.g. 0918-765-4321"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="regBarangay">Barangay</label>
                  <input
                    id="regBarangay"
                    v-model="regForm.barangay"
                    type="text"
                    placeholder="e.g. Tubajon"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="regMunicipality">Municipality / Location</label>
                  <input
                    id="regMunicipality"
                    v-model="regForm.municipality"
                    type="text"
                    placeholder="e.g. Dinagat Islands"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="regInitialDate">Initial Donation Date</label>
                  <input
                    id="regInitialDate"
                    v-model="regForm.initialDonationDate"
                    type="date"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="regDonationLocation">Donation Facility</label>
                  <input
                    id="regDonationLocation"
                    v-model="regForm.donationLocation"
                    type="text"
                    placeholder="e.g. Tubajon Health Center"
                    required
                  />
                </div>
              </div>

              <div v-if="registerSuccessMsg" class="success-alert">
                <i class="ti ti-check"></i> {{ registerSuccessMsg }}
              </div>

              <div class="modal-actions">
                <button type="button" class="btn-cancel" @click="closeRegisterModal">Cancel</button>
                <button type="submit" class="btn-primary">Save Donor Record</button>
              </div>
            </form>

            <!-- 2. Returning Donor Quick Search & Record -->
            <div v-else class="returning-modal-body">
              <div class="search-input-wrap">
                <i class="ti ti-search search-icon"></i>
                <input
                  v-model="returningSearchQuery"
                  type="text"
                  placeholder="Search existing donor by name (e.g. Juan)..."
                  class="search-input"
                  autofocus
                />
              </div>

              <div v-if="returningSearchQuery">
                <div v-if="returningSearchResults.length" class="returning-results-wrap">
                  <div
                    v-for="d in returningSearchResults"
                    :key="d.id"
                    class="returning-donor-item"
                  >
                    <div class="returning-donor-info">
                      <div class="returning-donor-header">
                        <strong class="donor-name-link">{{ d.fullName }}</strong>
                        <span class="blood-type-badge sm">{{ d.bloodType }}</span>
                      </div>
                      <span class="returning-donor-meta">
                        {{ d.age }} yrs · {{ d.gender }} · {{ d.barangay }}, {{ d.municipality }} ·
                        <strong class="text-red">{{ d.donationHistory.length }}</strong> previous donation{{ d.donationHistory.length !== 1 ? 's' : '' }}
                      </span>
                    </div>
                    <button
                      type="button"
                      class="btn-add-donation-sm"
                      @click="selectReturningDonor(d.id)"
                    >
                      <i class="ti ti-plus"></i> Add Donation
                    </button>
                  </div>
                </div>

                <div v-else class="no-donor-prompt">
                  <p>No existing donor found matching "<strong>{{ returningSearchQuery }}</strong>".</p>
                  <button
                    type="button"
                    class="btn-primary"
                    @click="registerModalTab = 'new'; regForm.fullName = returningSearchQuery"
                  >
                    <i class="ti ti-user-plus"></i> Register as New Donor
                  </button>
                </div>
              </div>

              <div v-else class="hint-box">
                <i class="ti ti-info-circle"></i>
                <span>Type the donor's name above to search their profile and add a new donation record.</span>
              </div>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </ModulePage>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import ModulePage from '../../components/dashboard/ModulePage.vue'
import {
  useBloodBankStore,
  bloodTypes,
  monthNames,
  addDonor,
  addDonationToDonor,
} from '../../stores/bloodBankStore'

const { donors } = useBloodBankStore()

const donorsWithTotals = computed(() =>
  donors.value.map((d) => ({ ...d, totalDonations: d.donationHistory.length }))
)

const modes = [
  { value: 'name', label: 'Search by Name', icon: 'ti ti-search' },
  { value: 'location', label: 'Search by Location', icon: 'ti ti-map-pin' },
  { value: 'location-year', label: 'Search by Location & Year', icon: 'ti ti-calendar' },
]

const searchMode = ref('name')
const selectedDonorId = ref(null)
const popularLocations = ['Tubajon', 'San Jose', 'Basilisa', 'Cagdianao', 'Dinagat', 'Loreto', 'Libjo']

function switchMode(mode) {
  searchMode.value = mode
}

function openDonor(id) {
  selectedDonorId.value = id
  showAddDonationForm.value = false
}

function closeDonor() {
  selectedDonorId.value = null
  showAddDonationForm.value = false
}

const selectedDonor = computed(() =>
  donorsWithTotals.value.find((d) => d.id === selectedDonorId.value) || null
)

function sortedHistory(donor) {
  return donor.donationHistory
    .slice()
    .sort((a, b) => new Date(b.date) - new Date(a.date))
}

function isLatestDonation(donor, record) {
  const sorted = donor.donationHistory.slice().sort((a, b) => new Date(b.date) - new Date(a.date))
  return sorted[0] === record
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

// --- Mode: by name ---
const nameQuery = ref('')
const nameResults = computed(() => {
  if (!nameQuery.value) return donorsWithTotals.value
  const q = nameQuery.value.toLowerCase().trim()
  return donorsWithTotals.value.filter((d) =>
    d.fullName.toLowerCase().includes(q)
  )
})

// --- Mode: by location ---
const locationQuery = ref('')
const locationResults = computed(() => {
  const q = locationQuery.value.toLowerCase().trim()
  if (!q) return []
  return donorsWithTotals.value.filter(
    (d) =>
      d.barangay.toLowerCase().includes(q) ||
      d.municipality.toLowerCase().includes(q) ||
      d.donationHistory.some((item) => item.location && item.location.toLowerCase().includes(q))
  )
})

// --- Mode: by location & year ---
const locYearLocation = ref('')
const currentYear = new Date().getFullYear()
const yearOptions = [currentYear - 2, currentYear - 1, currentYear, currentYear + 1]
const locYearYear = ref(currentYear)

const monthGroups = computed(() => {
  const q = locYearLocation.value.toLowerCase().trim()
  const year = locYearYear.value
  const buckets = {}

  donorsWithTotals.value.forEach((donor) => {
    donor.donationHistory.forEach((record) => {
      const d = new Date(record.date)
      const matchesLocation =
        (record.location && record.location.toLowerCase().includes(q)) ||
        donor.barangay.toLowerCase().includes(q) ||
        donor.municipality.toLowerCase().includes(q)

      if (matchesLocation && d.getFullYear() === year) {
        const m = d.getMonth()
        if (!buckets[m]) buckets[m] = []
        buckets[m].push({
          donorId: donor.id,
          donorName: donor.fullName,
          bloodType: record.bloodType || donor.bloodType,
          location: record.location || donor.barangay,
          dayLabel: monthNames[m] + ' ' + d.getDate(),
          formattedExactDate: formatDate(record.date),
          rawDate: d,
        })
      }
    })
  })

  return Object.keys(buckets)
    .map(Number)
    .sort((a, b) => a - b)
    .map((m) => ({
      month: monthNames[m],
      records: buckets[m].sort((a, b) => a.rawDate - b.rawDate),
    }))
})

// --- Mode: Returning Donor Search ---
const returningSearchQuery = ref('')
const returningSearchResults = computed(() => {
  const q = returningSearchQuery.value.toLowerCase().trim()
  if (!q) return []
  return donorsWithTotals.value.filter((d) => d.fullName.toLowerCase().includes(q))
})

// --- Add New Donation to Existing Donor ---
const showAddDonationForm = ref(false)
const newDonation = reactive({
  date: new Date().toISOString().slice(0, 10),
  location: 'Tubajon',
})

function submitNewDonation() {
  if (!selectedDonor.value) return

  addDonationToDonor(selectedDonor.value.id, {
    date: newDonation.date,
    bloodType: selectedDonor.value.bloodType,
    location: newDonation.location,
    bags: 1,
  })

  showAddDonationForm.value = false
  newDonation.date = new Date().toISOString().slice(0, 10)
  newDonation.location = 'Tubajon'
}

// --- Register / Returning Donor Modal ---
const showRegisterModal = ref(false)
const registerModalTab = ref('new')
const registerSuccessMsg = ref('')

function todayISO() {
  return new Date().toISOString().slice(0, 10)
}

const regForm = reactive({
  fullName: '',
  bloodType: '',
  age: null,
  gender: '',
  contact: '',
  barangay: 'Tubajon',
  municipality: 'Dinagat Islands',
  initialDonationDate: todayISO(),
  donationLocation: 'Tubajon Health Center',
})

function openRegisterModal(tab = 'new') {
  registerModalTab.value = tab
  regForm.fullName = ''
  regForm.bloodType = ''
  regForm.age = null
  regForm.gender = ''
  regForm.contact = ''
  regForm.barangay = 'Tubajon'
  regForm.municipality = 'Dinagat Islands'
  regForm.initialDonationDate = todayISO()
  regForm.donationLocation = 'Tubajon Health Center'
  registerSuccessMsg.value = ''
  returningSearchQuery.value = ''
  showRegisterModal.value = true
}

function closeRegisterModal() {
  showRegisterModal.value = false
  registerSuccessMsg.value = ''
  returningSearchQuery.value = ''
}

function selectReturningDonor(donorId) {
  closeRegisterModal()
  openDonor(donorId)
  showAddDonationForm.value = true
}

function handleRegisterDonor() {
  const newDonorRecord = {
    fullName: regForm.fullName.trim(),
    bloodType: regForm.bloodType,
    age: regForm.age,
    gender: regForm.gender,
    contact: regForm.contact.trim(),
    barangay: regForm.barangay.trim(),
    municipality: regForm.municipality.trim(),
    registeredDate: regForm.initialDonationDate,
    donationHistory: [
      {
        date: regForm.initialDonationDate,
        bloodType: regForm.bloodType,
        location: regForm.donationLocation,
        bags: 1,
      },
    ],
  }

  const created = addDonor(newDonorRecord)
  registerSuccessMsg.value = `Registered donor: ${created.fullName} (${created.bloodType}) successfully!`

  setTimeout(() => {
    closeRegisterModal()
    openDonor(created.id)
  }, 1000)
}
</script>

<style scoped>
.search-donor {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

/* ===================== Header Actions ===================== */
.collection-header-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  border-bottom: 2px solid #edf2f7;
  padding-bottom: 1rem;
}

.mode-tabs {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.mode-tab {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  font-size: 0.85rem;
  color: #4a5568;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.mode-tab:hover {
  background: #e2e8f0;
  color: #1a202c;
}

.mode-tab.active {
  color: #fff;
  background: #c0392b;
  border-color: #c0392b;
  font-weight: 600;
  box-shadow: 0 2px 6px rgba(192, 57, 43, 0.25);
}

.btn-register-donor {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #c0392b;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-register-donor:hover {
  background: #a5301f;
}

.mode-panel {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* ===================== Search Bars ===================== */
.search-bar-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.search-input-wrap {
  position: relative;
  flex: 1;
  min-width: 260px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #888;
  font-size: 16px;
}

.search-input {
  width: 100%;
  padding: 9px 12px 9px 36px;
  border: 1.5px solid #cbd5e0;
  border-radius: 8px;
  font-size: 0.88rem;
  outline: none;
  background: #fff;
  box-sizing: border-box;
}
.search-input:focus {
  border-color: #c0392b;
}

.btn-clear {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #cbd5e0;
  background: #fff;
  font-size: 0.8rem;
  cursor: pointer;
}

.quick-location-chips {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.chip-label {
  font-size: 0.78rem;
  color: #718096;
  font-weight: 600;
}

.location-chip {
  padding: 4px 10px;
  border-radius: 999px;
  border: 1px solid #cbd5e0;
  background: #f8fafc;
  font-size: 0.78rem;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.15s;
}
.location-chip:hover {
  background: #e2e8f0;
}
.location-chip.active {
  background: #c0392b;
  color: #fff;
  border-color: #c0392b;
  font-weight: 600;
}

.filters-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  align-items: center;
}

.filter-year-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #4a5568;
}

.filter-select {
  padding: 8px 12px;
  border: 1.5px solid #cbd5e0;
  border-radius: 8px;
  font-size: 0.88rem;
  background: #fff;
  outline: none;
}

.results-count,
.hint-text {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0;
}

/* ===================== Tables ===================== */
.table-wrap {
  overflow-x: auto;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #fff;
}

.donor-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.donor-table th {
  text-align: left;
  padding: 10px 14px;
  background: #f8fafc;
  font-weight: 600;
  color: #4a5568;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

.donor-table td {
  padding: 10px 14px;
  border-bottom: 1px solid #f1f5f9;
  color: #4a5568;
  vertical-align: middle;
}

.donor-row {
  cursor: pointer;
  transition: background 0.15s;
}
.donor-row:hover {
  background: #f8fafc;
}

.donor-name-link {
  color: #c0392b;
}

.btn-view-profile {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid #cbd5e0;
  background: #fff;
  font-size: 0.75rem;
  font-weight: 600;
  color: #2d3748;
  cursor: pointer;
}
.btn-view-profile:hover {
  background: #f1f5f9;
}

.blood-type-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  background: #fdecec;
  color: #c0392b;
  font-weight: 700;
  font-size: 0.75rem;
}
.blood-type-badge.sm { font-size: 0.7rem; padding: 1px 6px; }
.blood-type-badge.lg { font-size: 0.95rem; padding: 4px 12px; }

.gender-tag {
  display: inline-block;
  padding: 2px 7px;
  border-radius: 4px;
  font-size: 0.72rem;
  font-weight: 600;
}
.gender-tag.male { background: #ebf8ff; color: #2b6cb0; }
.gender-tag.female { background: #fdf2f8; color: #db2777; }

.text-red { color: #c0392b; }
.empty-state {
  text-align: center;
  color: #94a3b8;
  padding: 2rem !important;
  font-style: italic;
}

/* ===================== Location Cards Grid ===================== */
.location-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 12px;
}

.location-donor-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.location-donor-card:hover {
  border-color: #c0392b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.78rem;
  color: #64748b;
}

.card-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid #f1f5f9;
  padding-top: 8px;
  font-size: 0.75rem;
}

.donation-count-text {
  font-weight: 600;
  color: #c0392b;
}

.click-hint {
  color: #94a3b8;
}

/* ===================== Month Groups ===================== */
.month-groups {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.month-group {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #fff;
  overflow: hidden;
}

.month-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 14px;
  background: #f8fafc;
  font-weight: 700;
  font-size: 0.85rem;
  color: #2d3748;
  border-bottom: 1px solid #e2e8f0;
}

.month-title .badge {
  font-size: 0.72rem;
  background: #edf2f7;
  color: #4a5568;
  padding: 2px 8px;
  border-radius: 6px;
}

.record-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.record-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 14px;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  font-size: 0.85rem;
  transition: background 0.15s;
}
.record-item:last-child {
  border-bottom: none;
}
.record-item:hover {
  background: #f8fafc;
}

.record-donor-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.record-sub {
  font-size: 0.75rem;
  color: #718096;
}

.record-date-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.78rem;
  color: #4a5568;
  background: #f1f5f9;
  padding: 3px 8px;
  border-radius: 6px;
}

/* ===================== Modal Tab Switcher & Returning Donor ===================== */
.modal-tab-switch {
  display: flex;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 10px;
  gap: 4px;
  margin-bottom: 1.25rem;
}

.modal-tab-btn {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.modal-tab-btn:hover {
  color: #1a202c;
}

.modal-tab-btn.active {
  background: #fff;
  color: #c0392b;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.returning-modal-body {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.returning-results-wrap {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 280px;
  overflow-y: auto;
  border: 1.5px solid #edf2f7;
  border-radius: 10px;
  padding: 8px;
  background: #f8fafc;
}

.returning-donor-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 14px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  transition: all 0.15s;
}

.returning-donor-item:hover {
  border-color: #cbd5e0;
  background: #fdfefe;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.returning-donor-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.returning-donor-header {
  display: flex;
  align-items: center;
  gap: 8px;
}

.returning-donor-meta {
  font-size: 0.78rem;
  color: #64748b;
}

.btn-add-donation-sm {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border-radius: 6px;
  background: #c0392b;
  color: #fff;
  border: none;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s;
}

.btn-add-donation-sm:hover {
  background: #a5301f;
}

.no-donor-prompt {
  text-align: center;
  padding: 1.5rem;
  color: #64748b;
  font-size: 0.85rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  background: #f8fafc;
  border-radius: 10px;
  border: 1px dashed #cbd5e0;
}

.hint-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px 14px;
  color: #64748b;
  font-size: 0.83rem;
}

/* ===================== Modals ===================== */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-card {
  position: relative;
  background: #fff;
  border-radius: 14px;
  padding: 1.75rem;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2);
}

.register-modal-card {
  max-width: 620px;
}

.modal-close {
  position: absolute;
  top: 16px;
  right: 16px;
  border: none;
  background: none;
  font-size: 18px;
  color: #888;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
}
.modal-close:hover {
  color: #1a202c;
  background: #f1f5f9;
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 1.25rem;
  border-bottom: 1px solid #eef2f6;
  padding-bottom: 12px;
}

.modal-header-icon-wrap {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: #fdecec;
  color: #c0392b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  flex-shrink: 0;
}

.modal-name {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.modal-sub {
  font-size: 0.8rem;
  color: #718096;
  margin: 2px 0 0;
}

.modal-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px 18px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f0f0f0;
  margin-bottom: 12px;
}

.modal-field {
  display: flex;
  flex-direction: column;
  gap: 2px;
  font-size: 0.82rem;
}
.modal-field span { color: #718096; }
.modal-field strong { color: #1a202c; }

.modal-total-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px 14px;
  margin-bottom: 1rem;
}
.total-count-val {
  font-size: 1.15rem;
  font-weight: 800;
  color: #c0392b;
  margin-left: 6px;
}

.btn-add-donation-toggle {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border-radius: 6px;
  background: #c0392b;
  border: none;
  color: #fff;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
}

.add-donation-panel {
  background: #fffafa;
  border: 1.5px solid #fecaca;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 1rem;
}

.form-subheading {
  margin: 0 0 8px;
  font-size: 0.85rem;
  font-weight: 700;
  color: #991b1b;
  display: flex;
  align-items: center;
  gap: 5px;
}

.mini-donation-form {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.form-actions-inline {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 4px;
}

.btn-submit-sm {
  padding: 6px 12px;
  background: #c0392b;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
}
.btn-cancel-sm {
  padding: 6px 10px;
  background: #fff;
  border: 1px solid #cbd5e0;
  border-radius: 6px;
  font-size: 0.78rem;
  cursor: pointer;
}

.modal-history-title {
  font-size: 0.88rem;
  font-weight: 700;
  color: #2d3748;
  margin: 0 0 8px;
}

.history-table-wrap {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.82rem;
}

.history-table th {
  text-align: left;
  padding: 8px 10px;
  background: #f8fafc;
  color: #4a5568;
  font-weight: 600;
  border-bottom: 1px solid #e2e8f0;
}

.history-table td {
  padding: 8px 10px;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
}

.row-new-update {
  background: #fff5f5;
}

.new-badge {
  font-size: 0.68rem;
  background: #fdecec;
  color: #c0392b;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 700;
  margin-left: 6px;
}

.status-pill.verified {
  font-size: 0.7rem;
  padding: 2px 6px;
  border-radius: 4px;
  background: #dcfce7;
  color: #16a34a;
  font-weight: 600;
}

/* ===================== Register Form (FIXED) ===================== */
.register-form {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.9rem 1.25rem;
}

.full-span {
  grid-column: 1 / -1;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 100%;
}

.form-field label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #374151;
  text-align: left;
}

.form-field input,
.form-field select {
  width: 100%;
  padding: 9px 12px;
  border: 1.5px solid #cbd5e0;
  border-radius: 8px;
  font-size: 0.88rem;
  outline: none;
  background: #fff;
  color: #1e293b;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-field input:focus,
.form-field select:focus {
  border-color: #c0392b;
  box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1);
}

.success-alert {
  background: #dcfce7;
  color: #16a34a;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  border-top: 1px solid #eef2f6;
  padding-top: 14px;
  margin-top: 4px;
}

.btn-primary {
  padding: 9px 20px;
  border-radius: 8px;
  background: #c0392b;
  color: #fff;
  border: none;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-primary:hover {
  background: #a5301f;
}

.btn-cancel {
  padding: 9px 16px;
  border-radius: 8px;
  background: #fff;
  border: 1.5px solid #cbd5e0;
  color: #475569;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-cancel:hover {
  background: #f8fafc;
}

@media (max-width: 900px) {
  .register-decision-grid {
    grid-template-columns: 1fr;
  }
  .form-grid {
    grid-template-columns: 1fr;
  }
  .modal-grid {
    grid-template-columns: 1fr;
  }
}
</style>