import { ref, computed } from 'vue'

// ==========================================
// Centralized Blood Bank Reactive Store
// Records stored here automatically calculate & power:
// - Blood Collection Module
// - Blood Consumption Module
// - Automatic Reports (Monthly, Quarterly, Yearly, Age, Gender, Consumption)
// - Staff Accounts & Shift Schedule / Login Records
// ==========================================

export const bloodTypes = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']
export const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]

// ==========================================
// Shift Definitions
// ==========================================
export const shiftDefinitions = [
  {
    id: 'morning',
    name: 'Morning Shift',
    timeRange: '6:00 AM – 2:00 PM',
    startHour: 6,
    endHour: 14,
    color: '#2563eb',
    badgeClass: 'shift-morning',
  },
  {
    id: 'afternoon',
    name: 'Afternoon Shift',
    timeRange: '2:00 PM – 10:00 PM',
    startHour: 14,
    endHour: 22,
    color: '#d97706',
    badgeClass: 'shift-afternoon',
  },
  {
    id: 'midnight',
    name: 'Midnight Shift',
    timeRange: '10:00 PM – 6:00 AM',
    startHour: 22,
    endHour: 6,
    color: '#7c3aed',
    badgeClass: 'shift-midnight',
  },
]

export function getShiftByTime(dateObj = new Date()) {
  const hour = dateObj.getHours()
  if (hour >= 6 && hour < 14) {
    return shiftDefinitions[0] // Morning
  } else if (hour >= 14 && hour < 22) {
    return shiftDefinitions[1] // Afternoon
  } else {
    return shiftDefinitions[2] // Midnight
  }
}

// 1. Initial Donors and Donation Records
export const donors = ref([
  {
    id: 1,
    fullName: 'Juan Dela Cruz',
    bloodType: 'O+',
    age: 34,
    gender: 'Male',
    barangay: 'Tubajon',
    municipality: 'Dinagat Islands',
    contact: '0918-765-4321',
    registeredDate: '2026-01-15',
    donationHistory: [
      { date: '2026-01-15', bloodType: 'O+', location: 'Tubajon', bags: 1 },
      { date: '2026-04-20', bloodType: 'O+', location: 'Tubajon', bags: 1 },
      { date: '2026-08-14', bloodType: 'O+', location: 'Tubajon', bags: 1 },
    ],
  },
  {
    id: 2,
    fullName: 'Maria Santos',
    bloodType: 'A+',
    age: 23,
    gender: 'Female',
    barangay: 'San Jose',
    municipality: 'Dinagat Islands',
    contact: '0917-123-4567',
    registeredDate: '2026-01-20',
    donationHistory: [
      { date: '2026-01-20', bloodType: 'A+', location: 'San Jose', bags: 1 },
      { date: '2026-05-18', bloodType: 'A+', location: 'San Jose', bags: 1 },
    ],
  },
  {
    id: 3,
    fullName: 'Pedro Garcia',
    bloodType: 'B+',
    age: 42,
    gender: 'Male',
    barangay: 'Basilisa',
    municipality: 'Dinagat Islands',
    contact: '0920-555-0192',
    registeredDate: '2026-02-10',
    donationHistory: [
      { date: '2026-02-10', bloodType: 'B+', location: 'Basilisa', bags: 1 },
      { date: '2026-06-15', bloodType: 'B+', location: 'Basilisa', bags: 1 },
    ],
  },
  {
    id: 4,
    fullName: 'Ana Cruz',
    bloodType: 'O-',
    age: 21,
    gender: 'Female',
    barangay: 'Tubajon',
    municipality: 'Dinagat Islands',
    contact: '0921-888-2233',
    registeredDate: '2025-11-05',
    donationHistory: [
      { date: '2025-11-05', bloodType: 'O-', location: 'Tubajon', bags: 1 },
      { date: '2026-03-02', bloodType: 'O-', location: 'Tubajon', bags: 1 },
      { date: '2026-07-22', bloodType: 'O-', location: 'Tubajon', bags: 1 },
    ],
  },
  {
    id: 5,
    fullName: 'Carlos Reyes',
    bloodType: 'AB+',
    age: 51,
    gender: 'Male',
    barangay: 'Cagdianao',
    municipality: 'Dinagat Islands',
    contact: '0930-111-9988',
    registeredDate: '2026-02-25',
    donationHistory: [
      { date: '2026-02-25', bloodType: 'AB+', location: 'Cagdianao', bags: 1 },
    ],
  },
  {
    id: 6,
    fullName: 'Elena Bautista',
    bloodType: 'O+',
    age: 28,
    gender: 'Female',
    barangay: 'Loreto',
    municipality: 'Dinagat Islands',
    contact: '0919-444-5566',
    registeredDate: '2026-04-05',
    donationHistory: [
      { date: '2026-04-05', bloodType: 'O+', location: 'Loreto', bags: 1 },
      { date: '2026-08-10', bloodType: 'O+', location: 'Loreto', bags: 1 },
    ],
  },
  {
    id: 7,
    fullName: 'Mark Villanueva',
    bloodType: 'A-',
    age: 38,
    gender: 'Male',
    barangay: 'Libjo',
    municipality: 'Dinagat Islands',
    contact: '0922-333-7788',
    registeredDate: '2026-04-12',
    donationHistory: [
      { date: '2026-04-12', bloodType: 'A-', location: 'Libjo', bags: 1 },
    ],
  },
  {
    id: 8,
    fullName: 'Grace Lim',
    bloodType: 'B-',
    age: 58,
    gender: 'Female',
    barangay: 'San Jose',
    municipality: 'Dinagat Islands',
    contact: '0915-777-6655',
    registeredDate: '2026-05-02',
    donationHistory: [
      { date: '2026-05-02', bloodType: 'B-', location: 'San Jose', bags: 1 },
    ],
  },
  {
    id: 9,
    fullName: 'Joshua Tan',
    bloodType: 'O+',
    age: 19,
    gender: 'Male',
    barangay: 'Dinagat',
    municipality: 'Dinagat Islands',
    contact: '0918-222-1100',
    registeredDate: '2026-07-08',
    donationHistory: [
      { date: '2026-07-08', bloodType: 'O+', location: 'Dinagat', bags: 1 },
    ],
  },
  {
    id: 10,
    fullName: 'Patricia Ramos',
    bloodType: 'AB-',
    age: 47,
    gender: 'Female',
    barangay: 'Tubajon',
    municipality: 'Dinagat Islands',
    contact: '0916-999-4433',
    registeredDate: '2026-07-19',
    donationHistory: [
      { date: '2026-07-19', bloodType: 'AB-', location: 'Tubajon', bags: 1 },
    ],
  },
  {
    id: 11,
    fullName: 'Gabriel Soriano',
    bloodType: 'O+',
    age: 62,
    gender: 'Male',
    barangay: 'San Jose',
    municipality: 'Dinagat Islands',
    contact: '0928-333-1212',
    registeredDate: '2026-08-01',
    donationHistory: [
      { date: '2026-08-01', bloodType: 'O+', location: 'San Jose', bags: 1 },
    ],
  },
  {
    id: 12,
    fullName: 'Kristine Gomez',
    bloodType: 'A+',
    age: 26,
    gender: 'Female',
    barangay: 'Basilisa',
    municipality: 'Dinagat Islands',
    contact: '0917-888-9900',
    registeredDate: '2026-08-15',
    donationHistory: [
      { date: '2026-08-15', bloodType: 'A+', location: 'Basilisa', bags: 1 },
    ],
  },
])

// 2. Initial Consumption Records (Strictly Aggregated - No Patient Identifiers)
export const consumptionRecords = ref([
  { id: 1, location: 'Tubajon', bloodType: 'O+', numberOfBags: 8, month: 1, year: 2026, reportingPeriod: 'January 2026', dateSubmitted: '2026-01-31' },
  { id: 2, location: 'Tubajon', bloodType: 'O+', numberOfBags: 12, month: 2, year: 2026, reportingPeriod: 'February 2026', dateSubmitted: '2026-02-28' },
  { id: 3, location: 'Tubajon', bloodType: 'A+', numberOfBags: 5, month: 3, year: 2026, reportingPeriod: 'March 2026', dateSubmitted: '2026-03-31' },
  { id: 4, location: 'San Jose', bloodType: 'B+', numberOfBags: 7, month: 4, year: 2026, reportingPeriod: 'April 2026', dateSubmitted: '2026-04-30' },
  { id: 5, location: 'Basilisa', bloodType: 'O+', numberOfBags: 10, month: 5, year: 2026, reportingPeriod: 'May 2026', dateSubmitted: '2026-05-31' },
  { id: 6, location: 'Cagdianao', bloodType: 'A+', numberOfBags: 6, month: 6, year: 2026, reportingPeriod: 'June 2026', dateSubmitted: '2026-06-30' },
  { id: 7, location: 'Dinagat', bloodType: 'AB+', numberOfBags: 4, month: 7, year: 2026, reportingPeriod: 'July 2026', dateSubmitted: '2026-07-31' },
  { id: 8, location: 'Tubajon', bloodType: 'O+', numberOfBags: 10, month: 8, year: 2026, reportingPeriod: 'August 2026', dateSubmitted: '2026-08-31' },
  { id: 9, location: 'Loreto', bloodType: 'B-', numberOfBags: 3, month: 8, year: 2026, reportingPeriod: 'August 2026', dateSubmitted: '2026-08-20' },
])

// ==========================================
// 3. Staff Accounts Store (Individual Staff with Assigned Shift)
// ==========================================
export const staffAccounts = ref([
  {
    id: 1,
    fullName: 'Kane Sajol',
    username: 'kane_sajol',
    email: 'kane.sajol@crh-bloodbank.gov.ph',
    role: 'Medical Technologist',
    assignedShift: 'Morning Shift (6AM – 2PM)',
    shiftType: 'morning',
    station: 'Central Screening Lab / Tubajon',
    contact: '0918-123-4567',
    status: 'Active',
    avatarInitials: 'KS',
    avatarColor: '#2563eb',
    lastLogin: '2026-08-25 07:00 AM',
  },
  {
    id: 2,
    fullName: 'John Doe',
    username: 'john_doe',
    email: 'john.doe@crh-bloodbank.gov.ph',
    role: 'Blood Collection Nurse',
    assignedShift: 'Afternoon Shift (2PM – 10PM)',
    shiftType: 'afternoon',
    station: 'Mobile Donation Center / San Jose',
    contact: '0917-555-8899',
    status: 'Active',
    avatarInitials: 'JD',
    avatarColor: '#d97706',
    lastLogin: '2026-08-25 03:00 PM',
  },
  {
    id: 3,
    fullName: 'Jumel Alipao',
    username: 'jumel_alipao',
    email: 'jumel.alipao@crh-bloodbank.gov.ph',
    role: 'Emergency Screening Officer',
    assignedShift: 'Midnight Shift (10PM – 6AM)',
    shiftType: 'midnight',
    station: 'Emergency Night Lab / Main Hospital',
    contact: '0922-777-3311',
    status: 'Active',
    avatarInitials: 'JA',
    avatarColor: '#7c3aed',
    lastLogin: '2026-08-24 10:00 PM',
  },
  {
    id: 4,
    fullName: 'Sarah Ahmed',
    username: 'sarah_ahmed',
    email: 'sarah.ahmed@crh-bloodbank.gov.ph',
    role: 'Blood Bank Administrator',
    assignedShift: 'Morning Shift (6AM – 2PM)',
    shiftType: 'morning',
    station: 'Administration & QC Office',
    contact: '0919-888-4422',
    status: 'Active',
    avatarInitials: 'SA',
    avatarColor: '#059669',
    lastLogin: '2026-08-25 06:50 AM',
  },
  {
    id: 5,
    fullName: 'Maria Elena Santos',
    username: 'maria_santos',
    email: 'maria.santos@crh-bloodbank.gov.ph',
    role: 'Phlebotomist / Screener',
    assignedShift: 'Afternoon Shift (2PM – 10PM)',
    shiftType: 'afternoon',
    station: 'Basilisa Collection Unit',
    contact: '0920-111-2244',
    status: 'Active',
    avatarInitials: 'MS',
    avatarColor: '#dc2626',
    lastLogin: '2026-08-24 02:15 PM',
  },
])

// ==========================================
// 4. Automatic Staff Shift & Login Records History
// ==========================================
export const staffShiftRecords = ref([
  {
    id: 101,
    staffId: 1,
    staffName: 'Kane Sajol',
    username: 'kane_sajol',
    role: 'Medical Technologist',
    shiftName: 'Morning Shift',
    shiftTimeRange: '6:00 AM – 2:00 PM',
    shiftType: 'morning',
    loginDate: '2026-08-25',
    loginTime: '07:00 AM',
    logoutTime: null,
    status: 'On Duty',
    station: 'Central Screening Lab - Terminal 1',
    notes: 'Logged in for Morning Shift duties.',
  },
  {
    id: 102,
    staffId: 2,
    staffName: 'John Doe',
    username: 'john_doe',
    role: 'Blood Collection Nurse',
    shiftName: 'Afternoon Shift',
    shiftTimeRange: '2:00 PM – 10:00 PM',
    shiftType: 'afternoon',
    loginDate: '2026-08-25',
    loginTime: '03:00 PM',
    logoutTime: null,
    status: 'On Duty',
    station: 'San Jose Mobile Unit - Terminal 2',
    notes: 'Logged in for Afternoon Shift collection.',
  },
  {
    id: 103,
    staffId: 3,
    staffName: 'Jumel Alipao',
    username: 'jumel_alipao',
    role: 'Emergency Screening Officer',
    shiftName: 'Midnight Shift',
    shiftTimeRange: '10:00 PM – 6:00 AM',
    shiftType: 'midnight',
    loginDate: '2026-08-24',
    loginTime: '10:00 PM',
    logoutTime: '06:00 AM',
    status: 'Completed',
    station: 'Emergency Night Lab - Terminal 3',
    notes: 'Completed midnight emergency screening.',
  },
  {
    id: 104,
    staffId: 4,
    staffName: 'Sarah Ahmed',
    username: 'sarah_ahmed',
    role: 'Blood Bank Administrator',
    shiftName: 'Morning Shift',
    shiftTimeRange: '6:00 AM – 2:00 PM',
    shiftType: 'morning',
    loginDate: '2026-08-25',
    loginTime: '06:50 AM',
    logoutTime: null,
    status: 'On Duty',
    station: 'Admin QC Office - Terminal 1',
    notes: 'Admin morning quality inspection.',
  },
  {
    id: 105,
    staffId: 5,
    staffName: 'Maria Elena Santos',
    username: 'maria_santos',
    role: 'Phlebotomist / Screener',
    shiftName: 'Afternoon Shift',
    shiftTimeRange: '2:00 PM – 10:00 PM',
    shiftType: 'afternoon',
    loginDate: '2026-08-24',
    loginTime: '02:15 PM',
    logoutTime: '10:00 PM',
    status: 'Completed',
    station: 'Basilisa Collection Unit - Terminal 1',
    notes: 'Completed afternoon phlebotomy duty.',
  },
  {
    id: 106,
    staffId: 1,
    staffName: 'Kane Sajol',
    username: 'kane_sajol',
    role: 'Medical Technologist',
    shiftName: 'Morning Shift',
    shiftTimeRange: '6:00 AM – 2:00 PM',
    shiftType: 'morning',
    loginDate: '2026-08-24',
    loginTime: '06:00 AM',
    logoutTime: '02:00 PM',
    status: 'Completed',
    station: 'Central Screening Lab - Terminal 1',
    notes: 'Standard morning shift completed.',
  },
])

// ==========================================
// Actions to add records dynamically
// ==========================================
export function addDonor(newDonorData) {
  const newId = donors.value.length ? Math.max(...donors.value.map(d => d.id)) + 1 : 1
  const record = {
    id: newId,
    ...newDonorData,
    registeredDate: newDonorData.registeredDate || new Date().toISOString().slice(0, 10),
    donationHistory: newDonorData.donationHistory || [],
  }
  donors.value.push(record)
  return record
}

export function addDonationToDonor(donorId, donation) {
  const donor = donors.value.find(d => d.id === donorId)
  if (donor) {
    donor.donationHistory.push({
      ...donation,
      bags: donation.bags || 1,
    })
  }
}

export function addConsumptionRecord(payload) {
  const newId = consumptionRecords.value.length ? Math.max(...consumptionRecords.value.map(r => r.id)) + 1 : 1
  const record = {
    id: newId,
    location: payload.location,
    bloodType: payload.blood_type || payload.bloodType,
    numberOfBags: Number(payload.number_of_bags || payload.numberOfBags),
    month: Number(payload.reporting_month || payload.month),
    year: Number(payload.reporting_year || payload.year),
    reportingPeriod: payload.reporting_period || payload.reportingPeriod || `${monthNames[(payload.reporting_month || payload.month) - 1]} ${payload.reporting_year || payload.year}`,
    dateSubmitted: payload.date_submitted || payload.dateSubmitted || new Date().toISOString().slice(0, 10),
  }
  consumptionRecords.value.push(record)
  return record
}

export function addStaffAccount(newStaff) {
  const newId = staffAccounts.value.length ? Math.max(...staffAccounts.value.map(s => s.id)) + 1 : 1
  const initials = newStaff.fullName
    .split(' ')
    .map(w => w[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()

  const colors = ['#2563eb', '#059669', '#d97706', '#7c3aed', '#dc2626', '#0891b2']
  const avatarColor = colors[newId % colors.length]

  const account = {
    id: newId,
    ...newStaff,
    avatarInitials: initials || 'ST',
    avatarColor,
    status: 'Active',
    lastLogin: 'Never',
  }
  staffAccounts.value.push(account)
  return account
}

export function recordStaffLogin(staffId, customTimeStr, stationName = 'Central Workstation') {
  const staff = staffAccounts.value.find(s => s.id === staffId)
  if (!staff) return null

  const now = new Date()
  const dateStr = now.toISOString().slice(0, 10)
  const timeStr = customTimeStr || now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })

  // Determine shift from staff or current time
  const shiftDef = shiftDefinitions.find(s => s.id === staff.shiftType) || getShiftByTime(now)

  const newId = staffShiftRecords.value.length ? Math.max(...staffShiftRecords.value.map(r => r.id)) + 1 : 1
  const record = {
    id: newId,
    staffId: staff.id,
    staffName: staff.fullName,
    username: staff.username,
    role: staff.role,
    shiftName: shiftDef.name,
    shiftTimeRange: shiftDef.timeRange,
    shiftType: shiftDef.id,
    loginDate: dateStr,
    loginTime: timeStr,
    logoutTime: null,
    status: 'On Duty',
    station: stationName,
    notes: `Logged in for ${shiftDef.name} (${shiftDef.timeRange}).`,
  }

  staffShiftRecords.value.unshift(record)
  staff.lastLogin = `${dateStr} ${timeStr}`
  return record
}

// ==========================================
// Central Composable for Report Calculations
// ==========================================
export function useBloodBankStore() {
  const allDonationsFlattened = computed(() => {
    const list = []
    donors.value.forEach(donor => {
      donor.donationHistory.forEach(item => {
        const d = new Date(item.date)
        list.push({
          donorId: donor.id,
          donorName: donor.fullName,
          bloodType: item.bloodType || donor.bloodType,
          location: item.location || donor.barangay,
          barangay: donor.barangay,
          municipality: donor.municipality,
          age: donor.age,
          gender: donor.gender,
          contact: donor.contact,
          date: item.date,
          year: d.getFullYear(),
          month: d.getMonth() + 1,
          bags: item.bags || 1,
        })
      })
    })
    return list
  })

  return {
    bloodTypes,
    monthNames,
    shiftDefinitions,
    donors,
    consumptionRecords,
    staffAccounts,
    staffShiftRecords,
    allDonationsFlattened,
    addDonor,
    addDonationToDonor,
    addConsumptionRecord,
    addStaffAccount,
    recordStaffLogin,
  }
}
