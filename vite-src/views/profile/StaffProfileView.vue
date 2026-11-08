<template>
  <ModulePage
    title="Staff Profile & Account Details"
    description="Manage your individual staff account details, review your assigned shift schedule, and view your automatic login audit records."
  >
    <div class="profile-layout">
      <!-- Left: Profile Summary Card -->
      <div class="profile-card">
        <div class="profile-avatar" :style="{ backgroundColor: currentStaff.avatarColor }">
          {{ currentStaff.avatarInitials }}
        </div>
        <h3 class="profile-name">{{ currentStaff.fullName }}</h3>
        <span class="profile-username">@{{ currentStaff.username }}</span>
        <span class="profile-role-tag">{{ currentStaff.role }}</span>

        <div class="shift-highlight-box" :class="currentStaff.shiftType">
          <div class="shift-hl-icon">
            <i :class="getShiftIcon(currentStaff.shiftType)"></i>
          </div>
          <div class="shift-hl-text">
            <span class="shift-hl-label">Assigned Shift Schedule</span>
            <strong class="shift-hl-name">{{ currentStaff.assignedShift }}</strong>
          </div>
        </div>

        <div class="profile-details-list">
          <div class="detail-item">
            <span class="detail-label"><i class="ti ti-mail"></i> Work Email:</span>
            <strong class="detail-val">{{ currentStaff.email }}</strong>
          </div>
          <div class="detail-item">
            <span class="detail-label"><i class="ti ti-phone"></i> Contact No:</span>
            <strong class="detail-val">{{ currentStaff.contact }}</strong>
          </div>
          <div class="detail-item">
            <span class="detail-label"><i class="ti ti-building-hospital"></i> Station:</span>
            <strong class="detail-val">{{ currentStaff.station }}</strong>
          </div>
          <div class="detail-item">
            <span class="detail-label"><i class="ti ti-clock"></i> Last Login:</span>
            <strong class="detail-val text-red">{{ currentStaff.lastLogin }}</strong>
          </div>
        </div>
      </div>

      <!-- Right: Shift Logs & Account Preferences -->
      <div class="profile-right-content">
        <!-- Shift Logs History for this staff -->
        <div class="card-box">
          <div class="box-header">
            <h4 class="box-title"><i class="ti ti-history"></i> My Automatic Shift & Login Records</h4>
            <span class="badge">{{ myShiftRecords.length }} Recorded</span>
          </div>

          <div class="table-wrap">
            <table class="profile-table">
              <thead>
                <tr>
                  <th>Shift Schedule</th>
                  <th>Logged In</th>
                  <th>Date</th>
                  <th>Station Terminal</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="myShiftRecords.length === 0">
                  <td colspan="5" class="empty-state">No shift logs found for this account.</td>
                </tr>
                <tr v-for="rec in myShiftRecords" :key="rec.id">
                  <td>
                    <span :class="['shift-badge', getShiftBadgeClass(rec.shiftType)]">
                      <i :class="getShiftIcon(rec.shiftType)"></i>
                      {{ rec.shiftName }} ({{ rec.shiftTimeRange }})
                    </span>
                  </td>
                  <td><strong class="text-red">{{ rec.loginTime }}</strong></td>
                  <td>{{ formatDate(rec.loginDate) }}</td>
                  <td>{{ rec.station }}</td>
                  <td>
                    <span :class="['duty-badge', rec.status === 'On Duty' ? 'active' : 'completed']">
                      {{ rec.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Shift Policy Reminder -->
        <div class="card-box policy-box">
          <h4 class="box-title"><i class="ti ti-info-circle"></i> Standard Blood Bank Shift Policy</h4>
          <ul class="policy-list">
            <li><strong>Morning Shift (6:00 AM – 2:00 PM):</strong> Responsible for morning donor intake, scheduled collection drives, and opening laboratory calibrations.</li>
            <li><strong>Afternoon Shift (2:00 PM – 10:00 PM):</strong> Manages peak walk-in donors, component separation, and routine cross-matching.</li>
            <li><strong>Midnight Shift (10:00 PM – 6:00 AM):</strong> Dedicated emergency screenings, urgent blood release for OR/ER cases, and night cold-chain inventory monitoring.</li>
          </ul>
        </div>
      </div>
    </div>
  </ModulePage>
</template>

<script setup>
import { computed } from 'vue'
import ModulePage from '../../components/dashboard/ModulePage.vue'
import { useBloodBankStore } from '../../stores/bloodBankStore'

const { staffAccounts, staffShiftRecords } = useBloodBankStore()

// Default logged-in staff member (e.g. Kane Sajol)
const currentStaff = computed(() => staffAccounts.value[0] || {})

const myShiftRecords = computed(() => {
  return staffShiftRecords.value.filter((r) => r.staffId === currentStaff.value.id)
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

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<style scoped>
.profile-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 1.5rem;
  align-items: start;
}

.profile-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.profile-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  color: #fff;
  font-size: 1.5rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.75rem;
}

.profile-name {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.profile-username {
  font-size: 0.8rem;
  color: #718096;
  margin-bottom: 0.5rem;
}

.profile-role-tag {
  background: #f1f5f9;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 999px;
  margin-bottom: 1rem;
}

.shift-highlight-box {
  width: 100%;
  border-radius: 10px;
  padding: 0.85rem;
  margin-bottom: 1.25rem;
  display: flex;
  align-items: center;
  gap: 10px;
  text-align: left;
}
.shift-highlight-box.morning {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
}
.shift-highlight-box.afternoon {
  background: #fffbeb;
  border: 1px solid #fde68a;
}
.shift-highlight-box.midnight {
  background: #f5f3ff;
  border: 1px solid #ddd6fe;
}

.shift-hl-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}
.morning .shift-hl-icon { color: #2563eb; }
.afternoon .shift-hl-icon { color: #d97706; }
.midnight .shift-hl-icon { color: #7c3aed; }

.shift-hl-text {
  display: flex;
  flex-direction: column;
}
.shift-hl-label {
  font-size: 0.7rem;
  color: #718096;
  font-weight: 600;
}
.shift-hl-name {
  font-size: 0.85rem;
  color: #1a202c;
}

.profile-details-list {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  text-align: left;
  border-top: 1px solid #edf2f7;
  padding-top: 1rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  gap: 6px;
}
.detail-label {
  color: #718096;
  display: flex;
  align-items: center;
  gap: 4px;
}
.detail-val {
  color: #2d3748;
}
.text-red { color: #c0392b; }

.profile-right-content {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-box {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.box-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.box-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.badge {
  font-size: 0.75rem;
  background: #edf2f7;
  color: #4a5568;
  padding: 3px 8px;
  border-radius: 6px;
  font-weight: 600;
}

.table-wrap {
  overflow-x: auto;
}

.profile-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.profile-table th {
  text-align: left;
  padding: 8px 10px;
  background: #f8fafc;
  color: #4a5568;
  font-weight: 600;
  border-bottom: 1px solid #e2e8f0;
}

.profile-table td {
  padding: 8px 10px;
  border-bottom: 1px solid #f1f5f9;
  color: #4a5568;
}

.shift-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.72rem;
  font-weight: 600;
}
.shift-morning { background: #eff6ff; color: #2563eb; }
.shift-afternoon { background: #fffbeb; color: #d97706; }
.shift-midnight { background: #f5f3ff; color: #7c3aed; }

.duty-badge {
  display: inline-block;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 700;
}
.duty-badge.active { background: #dcfce7; color: #16a34a; }
.duty-badge.completed { background: #f1f5f9; color: #64748b; }

.policy-box {
  background: #f8fafc;
}
.policy-list {
  margin: 0.75rem 0 0 1.25rem;
  padding: 0;
  font-size: 0.82rem;
  color: #4a5568;
  line-height: 1.6;
}

@media (max-width: 900px) {
  .profile-layout {
    grid-template-columns: 1fr;
  }
}
</style>
