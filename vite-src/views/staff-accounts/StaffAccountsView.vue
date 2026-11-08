<template>
  <ModulePage
    title="Staff Accounts Management"
    description="Every staff member has a dedicated account. Manage staff profiles, roles, assigned shift schedules, and contact details."
  >
    <div class="staff-module">
      <!-- ===================== HEADER ACTIONS BAR ===================== -->
      <div class="module-controls-bar">
        <div class="staff-header-info">
          <span class="staff-count-badge">
            <i class="ti ti-users"></i> Staff Accounts ({{ staffAccounts.length }})
          </span>
        </div>

        <div class="controls-actions">
          <button type="button" class="btn-primary" @click="openAddStaffModal">
            <i class="ti ti-user-plus"></i> Add Staff Account
          </button>
        </div>
      </div>

      <!-- ===================== STAFF ACCOUNTS DIRECTORY ===================== -->
      <section class="tab-content-panel">
        <div class="filters-bar">
          <div class="search-input-wrap">
            <i class="ti ti-search search-icon"></i>
            <input
              v-model="searchAccountQuery"
              type="text"
              placeholder="Search staff by name, username, or station..."
              class="search-input"
            />
          </div>

          <select v-model="filterShiftSchedule" class="filter-select">
            <option value="">All Shift Schedules</option>
            <option value="morning">Morning Shift (6AM – 2PM)</option>
            <option value="afternoon">Afternoon Shift (2PM – 10PM)</option>
            <option value="midnight">Midnight Shift (10PM – 6AM)</option>
          </select>
        </div>

        <div class="table-wrap">
          <table class="staff-table">
            <thead>
              <tr>
                <th>Staff Member</th>
                <th>Role / Position</th>
                <th>Assigned Shift Schedule</th>
                <th>Station / Branch</th>
                <th>Contact</th>
                <th>Last Login Timestamp</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredStaffAccounts.length === 0">
                <td colspan="6" class="empty-state">No staff accounts match your filter.</td>
              </tr>
              <tr v-for="staff in filteredStaffAccounts" :key="staff.id">
                <td>
                  <div class="staff-profile-cell">
                    <span class="staff-avatar" :style="{ backgroundColor: staff.avatarColor }">
                      {{ staff.avatarInitials }}
                    </span>
                    <div>
                      <strong class="staff-name">{{ staff.fullName }}</strong>
                      <span class="staff-subtext">@{{ staff.username }}</span>
                    </div>
                  </div>
                </td>
                <td>{{ staff.role }}</td>
                <td>
                  <span :class="['shift-badge', getShiftBadgeClass(staff.shiftType)]">
                    <i :class="getShiftIcon(staff.shiftType)"></i>
                    {{ staff.assignedShift }}
                  </span>
                </td>
                <td>{{ staff.station }}</td>
                <td>{{ staff.contact }}</td>
                <td>
                  <span class="last-login-text">
                    <i class="ti ti-clock"></i> {{ staff.lastLogin }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ===================== MODAL: ADD STAFF ACCOUNT ===================== -->
      <Teleport to="body">
        <div v-if="showAddStaffModal" class="modal-overlay" @click.self="showAddStaffModal = false">
          <div class="modal-card">
            <div class="modal-header">
              <h3 class="modal-title">Create Staff Account</h3>
              <button type="button" class="modal-close" @click="showAddStaffModal = false">
                <i class="ti ti-x"></i>
              </button>
            </div>

            <form class="staff-form" @submit.prevent="handleCreateStaff">
              <div class="form-grid">
                <div class="form-field">
                  <label for="fullName">Full Name</label>
                  <input
                    id="fullName"
                    v-model="newStaffForm.fullName"
                    type="text"
                    placeholder="e.g. Kane Sajol"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="username">Username</label>
                  <input
                    id="username"
                    v-model="newStaffForm.username"
                    type="text"
                    placeholder="e.g. kane_sajol"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="email">Work Email</label>
                  <input
                    id="email"
                    v-model="newStaffForm.email"
                    type="email"
                    placeholder="e.g. kane@crh-bloodbank.gov.ph"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="role">Role / Position</label>
                  <select id="role" v-model="newStaffForm.role" required>
                    <option value="" disabled>Select Role</option>
                    <option value="Medical Technologist">Medical Technologist</option>
                    <option value="Blood Collection Nurse">Blood Collection Nurse</option>
                    <option value="Emergency Screening Officer">Emergency Screening Officer</option>
                    <option value="Phlebotomist / Screener">Phlebotomist / Screener</option>
                    <option value="Blood Bank Administrator">Blood Bank Administrator</option>
                  </select>
                </div>

                <div class="form-field">
                  <label for="shiftType">Assigned Shift Schedule</label>
                  <select id="shiftType" v-model="newStaffForm.shiftType" required>
                    <option value="morning">Morning Shift (6:00 AM – 2:00 PM)</option>
                    <option value="afternoon">Afternoon Shift (2:00 PM – 10:00 PM)</option>
                    <option value="midnight">Midnight Shift (10:00 PM – 6:00 AM)</option>
                  </select>
                </div>

                <div class="form-field">
                  <label for="station">Assigned Station / Location</label>
                  <input
                    id="station"
                    v-model="newStaffForm.station"
                    type="text"
                    placeholder="e.g. Central Screening Lab / Tubajon"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="contact">Contact Number</label>
                  <input
                    id="contact"
                    v-model="newStaffForm.contact"
                    type="text"
                    placeholder="e.g. 0918-123-4567"
                    required
                  />
                </div>
              </div>

              <div class="form-actions">
                <button type="button" class="btn-secondary" @click="showAddStaffModal = false">Cancel</button>
                <button type="submit" class="btn-primary">Save Staff Account</button>
              </div>
            </form>
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
  addStaffAccount,
} from '../../stores/bloodBankStore'

const { staffAccounts } = useBloodBankStore()

const searchAccountQuery = ref('')
const filterShiftSchedule = ref('')

const showAddStaffModal = ref(false)

const newStaffForm = reactive({
  fullName: '',
  username: '',
  email: '',
  role: 'Medical Technologist',
  shiftType: 'morning',
  station: 'Central Screening Lab / Tubajon',
  contact: '',
})

function getShiftBadgeClass(shiftType) {
  if (shiftType === 'morning') return 'shift-morning'
  if (shiftType === 'afternoon') return 'shift-afternoon'
  return 'shift-midnight'
}

function getShiftIcon(shiftType) {
  if (shiftType === 'morning') return 'ti ti-sun'
  if (shiftType === 'afternoon') return 'ti ti-sunset'
  return 'ti ti-moon'
}

const filteredStaffAccounts = computed(() => {
  const q = searchAccountQuery.value.toLowerCase().trim()
  const shift = filterShiftSchedule.value
  return staffAccounts.value.filter((s) => {
    const matchesSearch =
      !q ||
      s.fullName.toLowerCase().includes(q) ||
      s.username.toLowerCase().includes(q) ||
      s.station.toLowerCase().includes(q) ||
      s.role.toLowerCase().includes(q)
    const matchesShift = !shift || s.shiftType === shift
    return matchesSearch && matchesShift
  })
})

function openAddStaffModal() {
  newStaffForm.fullName = ''
  newStaffForm.username = ''
  newStaffForm.email = ''
  newStaffForm.role = 'Medical Technologist'
  newStaffForm.shiftType = 'morning'
  newStaffForm.station = 'Central Screening Lab / Tubajon'
  newStaffForm.contact = ''
  showAddStaffModal.value = true
}

function handleCreateStaff() {
  const shiftLabels = {
    morning: 'Morning Shift (6AM – 2PM)',
    afternoon: 'Afternoon Shift (2PM – 10PM)',
    midnight: 'Midnight Shift (10PM – 6AM)',
  }

  addStaffAccount({
    fullName: newStaffForm.fullName,
    username: newStaffForm.username,
    email: newStaffForm.email,
    role: newStaffForm.role,
    assignedShift: shiftLabels[newStaffForm.shiftType] || 'Morning Shift',
    shiftType: newStaffForm.shiftType,
    station: newStaffForm.station,
    contact: newStaffForm.contact,
  })

  showAddStaffModal.value = false
}
</script>

<style scoped>
.staff-module {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ===================== Header Controls ===================== */
.module-controls-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  border-bottom: 2px solid #edf2f7;
  padding-bottom: 1rem;
}

.staff-count-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.88rem;
  font-weight: 700;
  color: #1a202c;
}
.staff-count-badge i {
  color: #c0392b;
  font-size: 1.05rem;
}

.controls-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary,
.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-primary {
  background: #c0392b;
  border: 1px solid #c0392b;
  color: #fff;
}
.btn-primary:hover {
  background: #a5301f;
}

.btn-secondary {
  background: #fff;
  border: 1px solid #cbd5e0;
  color: #2d3748;
}
.btn-secondary:hover {
  background: #f8fafc;
}

/* ===================== Filters Bar ===================== */
.filters-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.search-input-wrap {
  position: relative;
  flex: 1;
  min-width: 240px;
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
  border: 1px solid #cbd5e0;
  border-radius: 8px;
  font-size: 0.85rem;
  outline: none;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #cbd5e0;
  border-radius: 8px;
  font-size: 0.85rem;
  background: #fff;
  outline: none;
}

/* ===================== Tables ===================== */
.table-wrap {
  overflow-x: auto;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #fff;
}

.staff-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.staff-table th {
  text-align: left;
  padding: 10px 14px;
  background: #f8fafc;
  font-weight: 600;
  color: #4a5568;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

.staff-table td {
  padding: 10px 14px;
  border-bottom: 1px solid #f1f5f9;
  color: #4a5568;
  vertical-align: middle;
}

.staff-profile-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.staff-avatar {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.staff-name {
  color: #1a202c;
  display: block;
  font-size: 0.88rem;
}

.staff-subtext {
  font-size: 0.72rem;
  color: #718096;
}

.shift-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
}
.shift-morning {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
}
.shift-afternoon {
  background: #fffbeb;
  color: #d97706;
  border: 1px solid #fde68a;
}
.shift-midnight {
  background: #f5f3ff;
  color: #7c3aed;
  border: 1px solid #ddd6fe;
}

.last-login-text {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.78rem;
  color: #4a5568;
}

.empty-state {
  text-align: center;
  color: #a0aec0;
  padding: 2rem !important;
  font-style: italic;
}

/* ===================== Modals ===================== */
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
  max-width: 540px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.85rem;
  border-bottom: 1px solid #eee;
  padding-bottom: 8px;
}

.modal-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a202c;
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
  color: #1a202c;
}

.staff-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem 1rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-field label {
  font-size: 0.78rem;
  font-weight: 600;
  color: #4a5568;
}

.form-field input,
.form-field select {
  padding: 8px 10px;
  border: 1px solid #cbd5e0;
  border-radius: 6px;
  font-size: 0.85rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 0.5rem;
  border-top: 1px solid #eee;
  padding-top: 10px;
}

/* ===================== Responsive ===================== */
@media (max-width: 900px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
