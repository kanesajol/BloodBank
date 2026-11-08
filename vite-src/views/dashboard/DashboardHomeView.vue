<template>
  <div class="overview">
    <!-- Top Stats Grid -->
    <div class="overview__stats">
      <article
        v-for="stat in dynamicStats"
        :key="stat.label"
        class="stat-card"
        :class="`stat-card--${stat.variant}`"
      >
        <div class="stat-card__top">
          <span class="stat-card__label">{{ stat.label }}</span>
          <span class="stat-card__icon" aria-hidden="true" v-html="stat.icon" />
        </div>
        <strong class="stat-card__value">{{ stat.value }}</strong>
        <span class="stat-card__meta" :class="`stat-card__meta--${stat.variant}`">{{ stat.meta }}</span>
      </article>
    </div>

    <!-- Main Panel -->
    <section class="overview__panel">
      <header class="panel-header">
        <div class="panel-header__title">
          <span class="panel-header__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" />
              <path d="M16 2v4M8 2v4M3 10h18" />
            </svg>
          </span>
          <div>
            <h2>Registered Donors Directory</h2>
            <p>Search, review, and register blood donors in the system.</p>
          </div>
        </div>

        <div class="panel-header__actions">
          <input
            v-model="searchQuery"
            type="search"
            class="panel-header__search"
            placeholder="Search donors by name, blood, or location..."
            aria-label="Search donors"
          />
          <button type="button" class="panel-header__add" @click="openRegisterModal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <circle cx="12" cy="8" r="4" />
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <path d="M19 8v6M22 11h-6" />
            </svg>
            Register Donor
          </button>
        </div>
      </header>

      <div class="donor-table-wrap">
        <table class="donor-table">
          <thead>
            <tr>
              <th>Donor</th>
              <th>Blood Type</th>
              <th>Age / Gender</th>
              <th>Location</th>
              <th>Status</th>
              <th>Last Donation</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="donor in formattedDonors" :key="donor.id">
              <td>
                <div class="donor-cell">
                  <span class="donor-cell__avatar" :style="{ background: donor.avatarColor }">
                    {{ donor.initials }}
                  </span>
                  <div>
                    <strong>{{ donor.fullName }}</strong>
                    <span>{{ donor.contact || 'No contact' }}</span>
                  </div>
                </div>
              </td>
              <td><span class="blood-badge">{{ donor.bloodType }}</span></td>
              <td>{{ donor.age }} yrs · {{ donor.gender }}</td>
              <td>{{ donor.barangay }}, {{ donor.municipality }}</td>
              <td>
                <span class="status-badge" :class="`status-badge--${donor.status.toLowerCase()}`">
                  {{ donor.status }}
                </span>
              </td>
              <td>{{ donor.lastDonationLabel }}</td>
              <td>
                <RouterLink
                  to="/dashboard/blood-collection"
                  class="donor-table__view"
                >
                  View Details
                </RouterLink>
              </td>
            </tr>
            <tr v-if="formattedDonors.length === 0">
              <td colspan="7" class="donor-table__empty">No donors match your search query.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ===================== REGISTER DONOR MODAL ===================== -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="registerDonorModalTitle">
          <div class="modal-header">
            <div class="modal-header-title">
              <span class="modal-header-icon"><i class="ti ti-user-plus"></i></span>
              <div>
                <h3 id="registerDonorModalTitle" class="modal-title">Register Blood Donor</h3>
                <p class="modal-subtitle">Add a new donor profile to the central blood bank registry.</p>
              </div>
            </div>
            <button type="button" class="modal-close" @click="closeModal" aria-label="Close">
              <i class="ti ti-x"></i>
            </button>
          </div>

          <form class="modal-form" @submit.prevent="submitDonor">
            <div class="form-grid">
              <div class="form-field full-width">
                <label for="fullName">Full Name</label>
                <input
                  id="fullName"
                  v-model="form.fullName"
                  type="text"
                  placeholder="e.g. Maria Clara Santos"
                  required
                />
              </div>

              <div class="form-field">
                <label for="bloodType">Blood Type</label>
                <select id="bloodType" v-model="form.bloodType" required>
                  <option value="" disabled>Select Blood Type</option>
                  <option v-for="bt in bloodTypes" :key="bt" :value="bt">{{ bt }}</option>
                </select>
              </div>

              <div class="form-field">
                <label for="age">Age</label>
                <input
                  id="age"
                  v-model.number="form.age"
                  type="number"
                  min="16"
                  max="70"
                  placeholder="e.g. 28"
                  required
                />
              </div>

              <div class="form-field">
                <label for="gender">Gender</label>
                <select id="gender" v-model="form.gender" required>
                  <option value="" disabled>Select Gender</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>

              <div class="form-field">
                <label for="contact">Contact Number</label>
                <input
                  id="contact"
                  v-model="form.contact"
                  type="tel"
                  placeholder="e.g. 0918-123-4567"
                  required
                />
              </div>

              <div class="form-field">
                <label for="barangay">Barangay</label>
                <input
                  id="barangay"
                  v-model="form.barangay"
                  type="text"
                  placeholder="e.g. Tubajon"
                  required
                />
              </div>

              <div class="form-field">
                <label for="municipality">Municipality</label>
                <input
                  id="municipality"
                  v-model="form.municipality"
                  type="text"
                  placeholder="e.g. Dinagat Islands"
                  required
                />
              </div>

              <div class="form-field">
                <label for="initialDonationDate">Initial Donation Date</label>
                <input
                  id="initialDonationDate"
                  v-model="form.initialDonationDate"
                  type="date"
                  required
                />
              </div>

              <div class="form-field">
                <label for="donationLocation">Donation Facility / Drive</label>
                <input
                  id="donationLocation"
                  v-model="form.donationLocation"
                  type="text"
                  placeholder="e.g. Tubajon Rural Health Center"
                  required
                />
              </div>
            </div>

            <div v-if="successMessage" class="alert-success">
              <i class="ti ti-check"></i> {{ successMessage }}
            </div>

            <div class="form-actions">
              <button type="button" class="btn-cancel" @click="closeModal">Cancel</button>
              <button type="submit" class="btn-submit" :disabled="submitting">
                {{ submitting ? 'Registering...' : 'Complete Registration' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, ref, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import {
  useBloodBankStore,
  bloodTypes,
  addDonor,
} from '../../stores/bloodBankStore'

const { donors } = useBloodBankStore()
const searchQuery = ref('')
const showModal = ref(false)
const submitting = ref(false)
const successMessage = ref('')

const colors = ['#dbeafe', '#dcfce7', '#ffedd5', '#f3e8ff', '#fce7f3', '#e0e7ff']

function todayISO() {
  return new Date().toISOString().slice(0, 10)
}

const form = reactive({
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

function resetForm() {
  form.fullName = ''
  form.bloodType = ''
  form.age = null
  form.gender = ''
  form.contact = ''
  form.barangay = 'Tubajon'
  form.municipality = 'Dinagat Islands'
  form.initialDonationDate = todayISO()
  form.donationLocation = 'Tubajon Health Center'
  successMessage.value = ''
}

function openRegisterModal() {
  resetForm()
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  resetForm()
}

function submitDonor() {
  submitting.value = true

  const newRecord = {
    fullName: form.fullName.trim(),
    bloodType: form.bloodType,
    age: form.age,
    gender: form.gender,
    contact: form.contact.trim(),
    barangay: form.barangay.trim(),
    municipality: form.municipality.trim(),
    registeredDate: form.initialDonationDate,
    donationHistory: [
      {
        date: form.initialDonationDate,
        bloodType: form.bloodType,
        location: form.donationLocation,
        bags: 1,
      },
    ],
  }

  addDonor(newRecord)

  successMessage.value = `Successfully registered donor: ${newRecord.fullName} (${newRecord.bloodType})!`

  setTimeout(() => {
    submitting.value = false
    closeModal()
  }, 1200)
}

const formattedDonors = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()

  const list = donors.value.map((d, idx) => {
    const initials = d.fullName
      .split(' ')
      .map((w) => w[0])
      .slice(0, 2)
      .join('')
      .toUpperCase()

    const lastDonation =
      d.donationHistory && d.donationHistory.length
        ? d.donationHistory[d.donationHistory.length - 1]
        : null

    let lastDonationLabel = 'New Registrant'
    if (lastDonation) {
      lastDonationLabel = new Date(lastDonation.date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    }

    return {
      ...d,
      initials,
      avatarColor: colors[idx % colors.length],
      status: 'Active',
      lastDonationLabel,
    }
  })

  if (!q) return list

  return list.filter(
    (donor) =>
      donor.fullName.toLowerCase().includes(q) ||
      donor.bloodType.toLowerCase().includes(q) ||
      donor.barangay.toLowerCase().includes(q) ||
      donor.municipality.toLowerCase().includes(q) ||
      donor.gender.toLowerCase().includes(q)
  )
})

const dynamicStats = computed(() => {
  const total = donors.value.length
  let totalBags = 0
  donors.value.forEach((d) => {
    totalBags += d.donationHistory ? d.donationHistory.length : 0
  })

  return [
    {
      label: 'Registered Donors',
      value: total.toLocaleString(),
      meta: 'Active donor registry',
      variant: 'blue',
      icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    },
    {
      label: 'Total Donations',
      value: totalBags.toLocaleString(),
      meta: 'Bags collected',
      variant: 'green',
      icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>`,
    },
    {
      label: 'Active Locations',
      value: '7 Municipalities',
      meta: 'Dinagat Islands coverage',
      variant: 'orange',
      icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h12v4l-4 4 4 4v4H6v-4l4-4-4-4V2z"/></svg>`,
    },
    {
      label: 'Eligible Today',
      value: '100%',
      meta: 'Verified donor profiles',
      variant: 'red',
      icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M4.93 4.93l14.14 14.14"/></svg>`,
    },
  ]
})
</script>

<style scoped>
.overview {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.overview__stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.stat-card {
  position: relative;
  background: var(--color-white);
  border-radius: 12px;
  padding: 1.1rem 1.25rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
}

.stat-card--blue::before { background: #3b82f6; }
.stat-card--green::before { background: #22c55e; }
.stat-card--orange::before { background: #f59e0b; }
.stat-card--red::before { background: #ef4444; }

.stat-card__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 0.65rem;
}

.stat-card__label {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #8899a8;
}

.stat-card__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 8px;
}

.stat-card__icon :deep(svg) {
  width: 18px;
  height: 18px;
}

.stat-card--blue .stat-card__icon {
  background: #eff6ff;
  color: #2563eb;
}

.stat-card--green .stat-card__icon {
  background: #f0fdf4;
  color: #16a34a;
}

.stat-card--orange .stat-card__icon {
  background: #fffbeb;
  color: #d97706;
}

.stat-card--red .stat-card__icon {
  background: #fef2f2;
  color: #dc2626;
}

.stat-card__value {
  display: block;
  font-size: 1.65rem;
  font-weight: 700;
  color: #1a3a6b;
  line-height: 1.2;
  margin-bottom: 0.35rem;
}

.stat-card__meta {
  font-size: 0.78rem;
  font-weight: 500;
}

.stat-card__meta--blue { color: #2563eb; }
.stat-card__meta--green { color: #16a34a; }
.stat-card__meta--orange { color: #d97706; }
.stat-card__meta--red { color: #dc2626; }

.overview__panel {
  background: var(--color-white);
  border-radius: 14px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.35rem 1.5rem;
  border-bottom: 1px solid #eef1f5;
}

.panel-header__title {
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
}

.panel-header__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: #eff6ff;
  color: #2563eb;
  flex-shrink: 0;
}

.panel-header__icon svg {
  width: 20px;
  height: 20px;
}

.panel-header__title h2 {
  font-size: 1.05rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 0.15rem;
}

.panel-header__title p {
  font-size: 0.82rem;
  color: #8899a8;
}

.panel-header__actions {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  flex-shrink: 0;
}

.panel-header__search {
  width: 240px;
  height: 38px;
  padding: 0 0.85rem;
  border: 1px solid #e0e4ea;
  border-radius: 8px;
  font-size: 0.85rem;
  outline: none;
  transition: border-color 0.2s;
}

.panel-header__search:focus {
  border-color: #93c5fd;
}

.panel-header__add {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  height: 38px;
  padding: 0 1rem;
  background: #c0392b;
  border: none;
  border-radius: 8px;
  color: var(--color-white);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.2s;
}

.panel-header__add svg {
  width: 16px;
  height: 16px;
}

.panel-header__add:hover {
  background: #a5301f;
}

.donor-table-wrap {
  overflow-x: auto;
}

.donor-table {
  width: 100%;
  border-collapse: collapse;
}

.donor-table th {
  padding: 0.85rem 1.5rem;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  text-align: left;
  color: #8899a8;
  background: #fafbfc;
  border-bottom: 1px solid #eef1f5;
  white-space: nowrap;
}

.donor-table td {
  padding: 0.9rem 1.5rem;
  font-size: 0.88rem;
  color: #37474f;
  border-bottom: 1px solid #f0f2f5;
  vertical-align: middle;
}

.donor-table tbody tr:last-child td {
  border-bottom: none;
}

.donor-table tbody tr:hover {
  background: #fafbfc;
}

.donor-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.donor-cell__avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  font-size: 0.72rem;
  font-weight: 700;
  color: #37474f;
  flex-shrink: 0;
}

.donor-cell strong {
  display: block;
  font-size: 0.88rem;
  font-weight: 600;
  color: #1a1a2e;
  line-height: 1.3;
}

.donor-cell span {
  display: block;
  font-size: 0.78rem;
  color: #8899a8;
}

.blood-badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 4px;
  background: #fdecec;
  color: #c0392b;
  font-weight: 700;
  font-size: 0.8rem;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-white);
}

.status-badge--active {
  background: #22c55e;
}

.donor-table__view {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.35rem 0.85rem;
  border: 1px solid #e0e4ea;
  border-radius: 7px;
  background: var(--color-white);
  font-size: 0.82rem;
  font-weight: 600;
  color: #1a1a2e;
  text-decoration: none;
  transition: background 0.15s, border-color 0.15s;
}

.donor-table__view:hover {
  background: #f5f7fa;
  border-color: #cdd5df;
}

.donor-table__empty {
  text-align: center;
  color: #8899a8;
  font-style: italic;
  padding: 2rem 1.5rem !important;
}

/* ===================== Modal Styles ===================== */
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
  border-radius: 12px;
  padding: 1.5rem;
  width: 100%;
  max-width: 580px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  border-bottom: 1px solid #eee;
  padding-bottom: 0.85rem;
}

.modal-header-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.modal-header-icon {
  width: 38px;
  height: 38px;
  border-radius: 8px;
  background: #fdecec;
  color: #c0392b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.modal-title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: #1a1a2e;
}

.modal-subtitle {
  margin: 2px 0 0;
  font-size: 0.8rem;
  color: #718096;
}

.modal-close {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  color: #888;
  padding: 4px;
}
.modal-close:hover {
  color: #1a1a2e;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem 1rem;
}

.full-width {
  grid-column: 1 / -1;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-field label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #4a5568;
}

.form-field input,
.form-field select {
  padding: 8px 10px;
  border: 1.5px solid #cbd5e0;
  border-radius: 6px;
  font-size: 0.85rem;
  outline: none;
  transition: border-color 0.2s;
}

.form-field input:focus,
.form-field select:focus {
  border-color: #c0392b;
}

.alert-success {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #dcfce7;
  color: #16a34a;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 0.82rem;
  font-weight: 600;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 0.5rem;
  border-top: 1px solid #eee;
  padding-top: 10px;
}

.btn-cancel {
  padding: 8px 14px;
  border-radius: 6px;
  border: 1px solid #cbd5e0;
  background: #fff;
  font-size: 0.85rem;
  font-weight: 600;
  color: #4a5568;
  cursor: pointer;
}
.btn-cancel:hover { background: #f1f5f9; }

.btn-submit {
  padding: 8px 16px;
  border-radius: 6px;
  border: none;
  background: #c0392b;
  color: #fff;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-submit:hover { background: #a5301f; }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

@media (max-width: 1100px) {
  .overview__stats {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .panel-header {
    flex-direction: column;
    align-items: stretch;
  }

  .panel-header__actions {
    flex-direction: column;
    align-items: stretch;
  }

  .panel-header__search {
    width: 100%;
  }

  .panel-header__add {
    justify-content: center;
  }

  .overview__stats {
    grid-template-columns: 1fr;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
