import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from '../components/login/LoginPage.vue'
import DashboardLayout from '../layouts/DashboardLayout.vue'

const routes = [
  {
    path: '/',
    name: 'login',
    component: LoginPage,
  },
  {
    path: '/dashboard',
    component: DashboardLayout,
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('../views/dashboard/DashboardHomeView.vue'),
      },
      {
        path: 'profile',
        name: 'staff-profile',
        component: () => import('../views/profile/StaffProfileView.vue'),
      },
      {
        path: 'blood-collection',
        name: 'blood-collection',
        component: () => import('../views/blood-collection/SearchDonorView.vue'),
      },
      {
        path: 'blood-consumption',
        name: 'blood-consumption',
        component: () => import('../views/blood-consumption/AddConsumptionView.vue'),
        alias: ['blood-consumption/add-record', 'blood-consumption/search-records'],
      },
      {
        path: 'reports',
        name: 'reports',
        component: () => import('../views/reports/ReportsView.vue'),
        alias: [
          'reports/monthly-donor',
          'reports/quarterly-donor',
          'reports/yearly-donor',
          'reports/donor-age',
          'reports/gender',
          'reports/blood-consumption',
        ],
      },
      {
        path: 'staff-accounts',
        name: 'staff-accounts',
        component: () => import('../views/staff-accounts/StaffAccountsView.vue'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
